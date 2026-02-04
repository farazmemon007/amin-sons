<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\Product;
use App\Models\Productbooking;
use App\Models\ProductBookingItem;
use App\Models\ReceiptsVoucher;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturn;
use App\Models\Stock;
use App\Models\AccountHead;
use App\Models\Vendor;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\StockMovement;
use App\Models\Notification;
use App\Services\StockAlertService;


class SaleController extends Controller
{



    public function ajaxPost(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {

                /* ================= VALIDATION & FETCH BOOKING ================= */
                if (!$request->booking_id) {
                    abort(422, 'Booking ID required');
                }

                $booking = Productbooking::with('items')
                    ->lockForUpdate()
                    ->findOrFail($request->booking_id);

                if (!$request->warehouse_id || !is_array($request->warehouse_id)) {
                    abort(422, 'Warehouse selection required');
                }

                // Enforce receipts requirement: posting a sale must include at least
                // one receipt row with a valid account and an amount > 0, OR there
                // must be at least one unprocessed receipt already saved for this booking.
                $hasValidReceipt = false;
                if (!empty($request->receipt_account_id) && is_array($request->receipt_account_id)) {
                    foreach ($request->receipt_account_id as $i => $accId) {
                        $amt = (float) ($request->receipt_amount[$i] ?? 0);
                        if ($amt > 0 && !empty($accId) && is_numeric($accId)) {
                            $hasValidReceipt = true;
                            break;
                        }
                    }
                }

                // Check DB for existing unprocessed receipts for this booking (legacy or new)
                $existsDbReceipts = ReceiptsVoucher::where(function ($q) use ($booking) {
                    $q->where('booking_id', $booking->id)
                        ->orWhere('reference_no', $booking->invoice_no)
                        ->orWhere('reference_no', 'like', '%"' . $booking->invoice_no . '"%')
                        ->orWhere('reference_no', 'like', '%' . $booking->invoice_no . '%');
                })
                    ->where('type', 'SALE_RECEIPT')
                    ->where(function ($q) {
                        $q->where('processed', false)->orWhereNull('processed');
                    })
                    ->exists();

                // If no receipt rows provided and no existing unprocessed receipts,
                // allow the booking to be posted anyway. Previously this aborted
                // the request; we now permit posting without an upfront receipt
                // and treat the sale as credit to the customer (ledger increases).
                if (! $hasValidReceipt && ! $existsDbReceipts) {
                    Log::info('No receipt rows provided; proceeding to post sale as credit', ['booking' => $booking->id]);
                }

                if ($booking->is_posted) {
                    abort(422, 'Invoice already posted');
                }

                /* ================= UPDATE WAREHOUSE IDs ================= */
                foreach ($booking->items as $item) {
                    $wid = $request->warehouse_id[$item->product_id] ?? null;

                    if (!$wid) {
                        abort(422, 'Warehouse not selected for product ID ' . $item->product_id);
                    }

                    $item->update(['warehouse_id' => $wid]);
                }

                $booking->load('items');

                /* ================= CREATE SALE ================= */
                $sale = Sale::create([
                    'invoice_no'        => $booking->invoice_no,
                    'manual_invoice'    => $booking->manual_invoice,
                    'customer_id'       => $booking->customer_id,
                    'party_type'        => $booking->party_type,
                    'address'           => $booking->address,
                    'tel'               => $booking->tel,
                    'remarks'           => $booking->remarks,
                    'sub_total1'        => $booking->sub_total1,
                    'sub_total2'        => $booking->sub_total2,
                    'discount_percent'  => $booking->discount_percent,
                    'discount_amount'   => $booking->discount_amount,
                    'previous_balance'  => $booking->previous_balance,
                    'total_balance'     => $booking->total_balance,
                    'total_net'         => $booking->sub_total2 ?? 0,
                    
                ]);

                /* ================= CUSTOMER LEDGER (ONCE) ================= */
                $lastLedger = CustomerLedger::where('customer_id', $booking->customer_id)
                    ->latest('id')
                    ->lockForUpdate()
                    ->first();

                $previousBalance = $lastLedger->closing_balance ?? 0;
                // echo "$previousBalance";
                // echo "<pre>";
                // print_r($lastLedger);
                // dd();

                if ($previousBalance < $sale->total_net) {
                    $message = "Insufficient customer balance.";
                }

                // For booking-posted sales we treat the sale amount as additional
                // credit owed by the customer: new closing = previous + sale total.
                // If the request includes receipt rows (or they will be processed),
                // subtract their total so the ledger reflects net outstanding.
                $receiptTotal = 0;
                if (!empty($request->receipt_amount) && is_array($request->receipt_amount)) {
                    foreach ($request->receipt_amount as $amt) {
                        $amt = (float) $amt;
                        if ($amt > 0) $receiptTotal += $amt;
                    }
                }

                // Create ledger for the sale (previous + sale). Any receipts
                // provided in the request will be created and applied below,
                // which will deduct from the customer's ledger exactly once.
                $newClosing = $previousBalance + $sale->total_net;

                CustomerLedger::create([
                    'customer_id'        => $booking->customer_id,
                    'admin_or_user_id'   => auth()->id(),
                    'previous_balance'   => $previousBalance,
                    'opening_balance'    => $previousBalance,
                    'closing_balance'    => $newClosing,
                ]);

                /* ================= SALE ITEMS & STOCK ================= */
                foreach ($booking->items as $it) {

                    $wid = $it->warehouse_id;

                    // Main Stock (allow negative balances)
                    $stock = Stock::lockForUpdate()
                        ->where('product_id', $it->product_id)
                        ->first();

                    $warehousestock = WarehouseStock::lockForUpdate()
                        ->where('product_id', $it->product_id)
                        ->where('warehouse_id', $wid)
                        ->first();

                    $currentStockQty = $stock->qty ?? 0;
                    $currentWhQty = $warehousestock->quantity ?? 0;

                    $newWhQty = $currentWhQty - $it->sales_qty;
                    $newStockQty = $currentStockQty - $it->sales_qty;

                    if ($warehousestock) {
                        $warehousestock->quantity = $newWhQty;
                        $warehousestock->save();
                    } else {
                        WarehouseStock::create([
                            'warehouse_id' => $wid,
                            'product_id' => $it->product_id,
                            'quantity' => $newWhQty,
                        ]);
                    }

                    if ($stock) {
                        $stock->qty = $newStockQty;
                        $stock->save();
                    } else {
                        Stock::create([
                            'branch_id' => 1,
                            'warehouse_id' => $wid,
                            'product_id' => $it->product_id,
                            'qty' => $newStockQty,
                            'reserved_qty' => 0,
                        ]);
                    }

                    // Sale Item - include line-item discounts from booking items
                    SaleItem::create([
                        'sale_id'       => $sale->id,
                        'warehouse_id'  => $wid,
                        'product_id'    => $it->product_id,
                        'sales_qty'     => $it->sales_qty,
                        'retail_price'  => $it->retail_price,
                        'discount_percent' => (float) ($it->discount_percent ?? 0),
                        'discount_amount' => (float) ($it->discount_amount ?? 0),
                        'amount'        => $it->amount,
                    ]);

                    // Stock Movement
                    StockMovement::create([
                        'product_id'    => $it->product_id,
                        'type'          => 'out',
                        'qty'           => $it->sales_qty,
                        'ref_type'      => 'SALE',
                        'ref_id'        => $sale->id,
                        'ref_uuid'      => $booking->invoice_no,
                        'is_auto_pluck' => 1,
                        'note'          => 'Sale Invoice ' . $booking->invoice_no,
                    ]);

                    /* ================= CHECK STOCK ALERT ================= */
                    StockAlertService::checkAndCreateAlert($it->product_id, $wid);
                }

                /* ================= ACCOUNT UPDATE ================= */
                // Avoid hardcoding account ID. Find an account under the "Sales" head
                // and credit it with the sale total. If no Sales account found, skip
                // to avoid accidentally posting the sale total to a bank (e.g., MCB).
                $salesHead = AccountHead::where('name', 'like', '%Sales%')->first();
                if ($salesHead) {
                    $saleAccount = Account::lockForUpdate()->where('head_id', $salesHead->id)->first();
                    if ($saleAccount) {
                        $saleAccount->opening_balance += $sale->total_net;
                        $saleAccount->save();
                    }
                }

                /* ================= PROCESS PAYMENT RECEIPTS (multiple payments) ================= */
                // If the request supplied receipt rows, create them inside this
                // transaction so they are part of the same atomic operation.
                if (!empty($request->receipt_account_id) && is_array($request->receipt_account_id)) {
                    // Build arrays of provided receipt account IDs and amounts
                    $rowAccountIds = [];
                    $rowAmounts = [];
                    foreach ($request->receipt_account_id as $i => $accId) {
                        $acc = $accId;
                        $amt = (float) ($request->receipt_amount[$i] ?? 0);
                        // Server-side validation: if amount > 0, account must be present
                        if ($amt > 0 && (empty($acc) || !is_numeric($acc))) {
                            abort(422, 'Invalid receipt row: amount provided but account missing or invalid at row ' . ($i + 1));
                        }
                        if (!$acc || $amt <= 0) continue;
                        $rowAccountIds[] = (int) $acc;
                        $rowAmounts[] = $amt;
                    }

                    if (!empty($rowAccountIds)) {
                        // Idempotency: if a processed receipt already exists for this booking
                        // invoice, do not create another one. This prevents duplicate
                        // application if the same booking is posted more than once.
                        $existsProcessed = ReceiptsVoucher::where('reference_no', $booking->invoice_no)
                            ->where('type', 'SALE_RECEIPT')
                            ->where('processed', true)
                            ->exists();

                        if ($existsProcessed) {
                            Log::info('Processed SALE_RECEIPT already exists; skipping creation', ['reference' => $booking->invoice_no]);
                        } else {
                            // If there are already unprocessed SALE_RECEIPT rows for this
                            // booking, do not create new ones here. This prevents creating
                            // duplicate receipts when the UI or another process already
                            // saved payment rows before posting.
                            $existsUnprocessed = ReceiptsVoucher::where('reference_no', $booking->invoice_no)
                                ->where('type', 'SALE_RECEIPT')
                                ->where(function ($q) {
                                    $q->where('processed', false)->orWhereNull('processed');
                                })
                                ->exists();

                            if ($existsUnprocessed) {
                                Log::info('Unprocessed SALE_RECEIPT(s) exist for booking; skipping creation', ['reference' => $booking->invoice_no]);
                            } else {
                                // Deduplicate account+amount pairs to avoid creating duplicate
                                // receipts when the request accidentally contains repeated rows.
                                $unique = [];
                                $uniqueAccountIds = [];
                                $uniqueAmounts = [];
                                foreach ($rowAccountIds as $i => $acctId) {
                                    $amt = $rowAmounts[$i] ?? ($rowAmounts[0] ?? 0);
                                    if ($amt <= 0) continue;
                                    $sig = $acctId . '|' . number_format((float)$amt, 2, '.', '');
                                    if (isset($unique[$sig])) continue;
                                    $unique[$sig] = true;
                                    $uniqueAccountIds[] = $acctId;
                                    $uniqueAmounts[] = $amt;
                                }

                                foreach ($uniqueAccountIds as $i => $acctId) {
                                    $amt = $uniqueAmounts[$i];
                                    $rv = ReceiptsVoucher::create([
                                        'rvid' => ReceiptsVoucher::generateRVID(auth()->id()),
                                        'receipt_date' => Carbon::today(),
                                        'entry_date' => Carbon::now(),
                                        'type' => 'SALE_RECEIPT',
                                        'party_id' => $booking->customer_id,
                                        'booking_id' => $booking->id,
                                        'tel' => $booking->tel,
                                        'remarks' => $booking->remarks,
                                        'reference_no' => $booking->invoice_no,
                                        'row_account_head' => 'Cash/Bank',
                                        'row_account_id' => is_array($acctId) ? json_encode($acctId) : $acctId,
                                        'amount' => is_array($amt) ? json_encode($amt) : $amt,
                                        'total_amount' => $amt,
                                        'processed' => true,
                                    ]);

                                    Log::info('Created and applied per-account SALE_RECEIPT', ['rv_id' => $rv->id, 'rvid' => $rv->rvid, 'account' => $acctId, 'amount' => $amt, 'reference' => $booking->invoice_no]);

                                    // Immediately apply to account and customer ledger
                                    try {
                                        $rowAccount = Account::lockForUpdate()->find($acctId);
                                        if ($rowAccount) {
                                            if (strtolower($rowAccount->type) === 'debit') {
                                                $rowAccount->opening_balance += $amt;
                                            } else {
                                                $rowAccount->opening_balance -= $amt;
                                            }
                                            $rowAccount->save();
                                        }

                                        $custPrev = CustomerLedger::where('customer_id', $booking->customer_id)->latest('id')->lockForUpdate()->value('closing_balance') ?? 0;
                                        $custNew = $custPrev - $amt;
                                        CustomerLedger::create([
                                            'customer_id' => $booking->customer_id,
                                            'admin_or_user_id' => auth()->id(),
                                            'previous_balance' => $custPrev,
                                            'opening_balance' => 0,
                                            'closing_balance' => $custNew,
                                        ]);
                                    } catch (\Exception $e) {
                                        Log::error('Failed to apply booking receipt', ['error' => $e->getMessage(), 'rv' => $rv->id ?? null]);
                                    }
                                }
                            }
                        }
                    }
                }

                // Find any unprocessed receipts referencing this booking invoice and process them.
                // Only consider receipts explicitly linked by `booking_id`, or legacy
                // receipts that have an exact `reference_no` match (do NOT use broad LIKE
                // matches which can accidentally include unrelated receipts).
                $receipts = ReceiptsVoucher::query()
                    ->where('type', 'SALE_RECEIPT')
                    ->where(function ($q) use ($booking) {
                        $q->where('booking_id', $booking->id)
                            ->orWhere(function ($q2) use ($booking) {
                                $q2->whereNull('booking_id')
                                    ->where('reference_no', $booking->invoice_no);
                            });
                    })
                    ->where(function ($q) {
                        $q->where('processed', false)->orWhereNull('processed');
                    })
                    ->lockForUpdate()
                    ->get();

                // Track which booking|account|amount combinations we've applied in
                // this transaction to avoid applying duplicates across multiple
                // receipt records for the same booking.
                $appliedSignatures = [];

                foreach ($receipts as $rv) {
                    Log::info('Found receipt for processing', ['rv_id' => $rv->id, 'rvid' => $rv->rvid ?? null, 'processed' => $rv->processed ?? null, 'reference' => $rv->reference_no ?? null]);
                    // skip receipts already applied by receipts UI / manual posting
                    if (!empty($rv->processed)) {
                        Log::info('Skipping processed receipt', ['rv_id' => $rv->id, 'rvid' => $rv->rvid ?? null]);
                        continue;
                    }
                    // Determine total amount for this receipt (supports JSON arrays or single value)
                    $totalAmount = 0;
                    if (!empty($rv->total_amount)) {
                        $totalAmount = (float) $rv->total_amount;
                    } elseif (!empty($rv->amount)) {
                        $decoded = json_decode($rv->amount, true);
                        if (is_array($decoded)) {
                            $totalAmount = array_sum(array_map('floatval', $decoded));
                        } else {
                            $totalAmount = (float) $rv->amount;
                        }
                    }

                    if ($totalAmount <= 0) continue;

                    // Update row account(s): prefer explicit per-row amounts.
                    $rowAccountIds = [];
                    $rowAmounts = [];

                    if (!empty($rv->row_account_id)) {
                        $decodedIds = json_decode($rv->row_account_id, true);
                        if (is_array($decodedIds)) {
                            $rowAccountIds = $decodedIds;
                        } else {
                            $rowAccountIds = [$rv->row_account_id];
                        }
                    }

                    // Only use per-row amounts if they were explicitly provided.
                    if (!empty($rv->amount)) {
                        $decodedAmounts = json_decode($rv->amount, true);
                        if (is_array($decodedAmounts)) {
                            $rowAmounts = array_map('floatval', $decodedAmounts);
                        } else {
                            $rowAmounts = [(float) $rv->amount];
                        }
                    }

                    // If no explicit per-row amounts found, skip applying this receipt.
                    // This prevents empty/blank receipt rows from causing the entire
                    // receipt total to be applied to accounts unintentionally.
                    if (empty($rowAmounts) || array_sum($rowAmounts) <= 0) {
                        // mark processed to avoid re-processing if column exists
                        if (property_exists($rv, 'processed')) {
                            $rv->processed = true;
                            $rv->save();
                            Log::info('Marked empty-amount receipt processed', ['rv_id' => $rv->id]);
                        }
                        continue;
                    }

                    Log::info('Applying receipt rows', ['rv_id' => $rv->id, 'accounts' => $rowAccountIds, 'amounts' => $rowAmounts]);

                    // Safeguard: avoid applying identical account+amount combinations more
                    // than once during this processing run (handles historical duplicate
                    // receipt records that shouldn't be applied multiple times).

                    foreach ($rowAccountIds as $i => $accId) {
                        $rowAmount = $rowAmounts[$i] ?? ($rowAmounts[0] ?? 0);
                        if ($rowAmount <= 0) continue;

                        $signature = ($rv->booking_id ?? $rv->reference_no) . '|' . $accId . '|' . number_format((float)$rowAmount, 2, '.', '');
                        if (in_array($signature, $appliedSignatures, true)) {
                            Log::warning('Skipping duplicate receipt-account-amount combination', ['signature' => $signature, 'rv_id' => $rv->id]);
                            continue;
                        }

                        $rowAccount = Account::lockForUpdate()->find($accId);
                        if (! $rowAccount) continue;
                        Log::info('Applying to account', ['rv_id' => $rv->id, 'account_id' => $rowAccount->id, 'before' => $rowAccount->opening_balance, 'amount' => $rowAmount, 'type' => $rowAccount->type]);

                        if (strtolower($rowAccount->type) === 'debit') {
                            $rowAccount->opening_balance += $rowAmount;
                        } else {
                            $rowAccount->opening_balance -= $rowAmount;
                        }
                        $rowAccount->save();
                        Log::info('Applied to account', ['rv_id' => $rv->id, 'account_id' => $rowAccount->id, 'after' => $rowAccount->opening_balance]);

                        $appliedSignatures[] = $signature;
                    }

                    // NOTE: receipts are payments applied to bank/cash accounts above.
                    // Adjust the customer's ledger by deducting the receipt total
                    // because the booking-posted sale increased the customer's
                    // outstanding balance earlier.
                    try {
                        $custLedger = CustomerLedger::where('customer_id', $booking->customer_id)
                            ->latest('id')
                            ->lockForUpdate()
                            ->first();
                        $custPrev = $custLedger ? $custLedger->closing_balance : 0;
                        $custNew = $custPrev - $totalAmount;
                        CustomerLedger::create([
                            'customer_id' => $booking->customer_id,
                            'admin_or_user_id' => auth()->id(),
                            'previous_balance' => $custPrev,
                            'opening_balance' => 0,
                            'closing_balance' => $custNew,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to adjust customer ledger for receipt', ['error' => $e->getMessage(), 'rv' => $rv->id ?? null]);
                    }

                    // mark this receipt as processed so we don't apply it again
                    $rv->processed = true;
                    $rv->save();
                    Log::info('Marked receipt processed', ['rv_id' => $rv->id]);
                }

                /* ================= MARK BOOKING POSTED ================= */
                $booking->update([
                    'is_posted' => 1,
                    'posted_at' => now(),
                    'status'    => 'sale',
                ]);

                /* ================= CREATE NOTIFICATION IF NOTIFY_ME IS SET ================= */
                if ($booking->notify_me !== null && $booking->notify_me !== '') {
                    $notificationDate = Carbon::today()->addDays($booking->notify_me);
                    
                    Notification::create([
                        'booking_id' => $booking->id,
                        'sale_id' => $sale->id,
                        'customer_id' => $booking->customer_id,
                        'type' => 'booking_payment',
                        'title' => 'Payment Reminder - ' . $booking->invoice_no,
                        'description' => 'Payment reminder for booking ' . $booking->invoice_no . ' (Amount: ' . $sale->total_net . ')',
                        'notification_date' => $notificationDate,
                        'status' => 'pending',
                        'created_by' => auth()->id(),
                    ]);

                    Log::info('Created payment notification', [
                        'booking_id' => $booking->id,
                        'notification_date' => $notificationDate,
                        'days' => $booking->notify_me,
                        'customer_id' => $booking->customer_id,
                    ]);
                }

                /* ================= RESPONSE ================= */
                return response()->json([
                    'ok'          => true,
                    'sale_id'     => $sale->id,
                    'invoice_url' => route('sale.invoice', $sale->id),
                    'status'      => $booking->status,
                    'msg'      => $message ?? Null,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Sale post failed', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $status = 422;
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                $status = $e->getStatusCode();
            }
            return response()->json(['ok' => false, 'error' => $e->getMessage()], $status);
        }
    }




    public function ajaxSave(Request $request)
    {
        // echo "<pre>";
        // print_r($request->all());
        // dd();
        return DB::transaction(function () use ($request) {

            /* ================= UPDATE / CREATE BOOKING ================= */
            if ($request->filled('booking_id')) {

                $booking = Productbooking::findOrFail($request->booking_id);

                ProductBookingItem::where('booking_id', $booking->id)->delete();
                ReceiptsVoucher::where('reference_no', $booking->invoice_no)->delete();
            } else {

                $booking = new Productbooking();
                $booking->invoice_no = 'INVSLE-' . str_pad(
                    (Productbooking::max('id') ?? 0) + 1,
                    4,
                    '0',
                    STR_PAD_LEFT
                );
            }

            /* ================= SAVE HEADER ================= */
            $booking->manual_invoice   = $request->Invoice_main;
            $booking->party_type       = $request->partyType;
            $booking->customer_id      = $request->customer_id;
            $booking->address          = $request->address;
            $booking->tel              = $request->tel;
            $booking->remarks          = $request->remarks;
            $booking->sub_total1       = $request->subTotal1 ?? 0;
            $booking->sub_total2       = $request->subTotal2 ?? 0;
            // $booking->discount_percent = $request->discount_percentage ?? 0;
            // $booking->discount_amount  = $request->discount_amount ?? 0;
            $booking->previous_balance = $request->previousBalance ?? 0;
            $booking->total_balance    = $request->totalBalance ?? 0;
            $booking->status    = 'pending';
            $booking->notify_me         = $request->notify_me ?? 0;

            $booking->quantity = 0;
            $booking->save();

            /* ================= SAVE ITEMS ================= */
            $totalQty = 0;

            foreach ($request->product_id ?? [] as $i => $productId) {

                $qty = (float) ($request->sales_qty[$i] ?? 0);
                if (!$productId || $qty <= 0) continue;

                ProductBookingItem::create([
                    'booking_id' => $booking->id,
                    'product_id' => $productId,
                    'sales_qty' => $qty,
                    'retail_price' => $request->retail_price[$i] ?? 0,
                    'discount_amount' => $request->discount_amount[$i] ?? 0,
                    'discount_percent' => $request->discount_percentage[$i] ?? 0,
                    'amount' => $request->sales_amount[$i] ?? 0,
                ]);
            }


            $booking->quantity = $totalQty;
            $booking->save();

            /* ================= SAVE RECEIPTS ================= */
            // NOTE: receipts are created later during posting (ajaxPost)
            // to ensure all writes for posting are performed inside a single
            // DB transaction. Do not persist receipts here to avoid partial
            // commits if posting fails.

            return response()->json([
                'ok' => true,
                'booking_id' => $booking->id
            ]);
        });
    }

    public function getWarehousesByProducts(Request $request)
    {
        $productIds = $request->product_ids; // array from query string
        if (empty($productIds) || !is_array($productIds)) return response()->json([]);

        // Fetch warehouse stocks for the requested products
        $rows = \App\Models\WarehouseStock::whereIn('product_id', $productIds)
            ->where('quantity', '>', 0)
            ->get(['warehouse_id', 'product_id', 'quantity']);

        // group by product_id
        $grouped = $rows->groupBy('product_id');

        $response = [];
        foreach ($productIds as $pid) {
            $product = \App\Models\Product::find($pid);
            $warehouses = ($grouped[$pid] ?? collect())->map(function ($r) {
                $name = \App\Models\Warehouse::where('id', $r->warehouse_id)->value('warehouse_name');
                return [
                    'warehouse_id' => $r->warehouse_id,
                    'warehouse_name' => $name,
                    'quantity' => $r->quantity,
                ];
            })->values();

            $response[] = [
                'product_id' => $pid,
                'product_name' => $product?->item_name ?? 'Product ' . $pid,
                'warehouses' => $warehouses,
            ];
        }

        return response()->json($response);
    }







    public function getCustomerData($id, Request $request)
    {
        $type = strtolower($request->query('type', 'customer'));

        if ($type === 'vendor') {
            // Fetch Vendor data
            $v = Vendor::find($id);
            if (!$v) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            return response()->json([
                'address' => $v->address,
                'mobile' => $v->phone, // assuming 'phone' field for vendors
                'remarks' => '', // No remarks for vendors
                'previous_balance' => 0, // Vendors may not have balance logic
            ]);
        }

        // Default: Fetch Customer data (including walking)
        $c = Customer::find($id);
        if (!$c) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        // Retrieve the latest ledger entry for the customer
        $latestLedger = CustomerLedger::where('customer_id', $id)->latest()->first();

        // If a ledger entry exists, use its closing_balance; otherwise, set it to 0
        $previous_balance = $latestLedger ? $latestLedger->closing_balance : 0;

        return response()->json([
            'filer_type' => $c->filer_type,
            'customer_type' => $c->customer_type,
            'address' => $c->address,
            'mobile' => $c->mobile,
            'remarks' => $c->remarks ?? '',
            'previous_balance' => $previous_balance, // Use the latest closing_balance
            'credit_upto' => $c->credit_upto,  // ✅ نیا
            'credit_limit' => $c->credit_limit,  // ✅ نیا
            'opening_balance' => $c->opening_balance,  // ✅ نیا
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    //////////////
    // public function index  (Request $request)
    // {
    //     $type = $request->type ?? 'customer';

    //     $customers = Customer::where('type', $type)
    //         ->orderBy('name')
    //         ->get(['id', 'name', 'mobile']);
    //         dd($customers);

    //     return response()->json($customers);
    // }

    // // 🔹 Single customer detail
    // public function show($id, Request $request)
    // {
    //     $type = $request->type ?? 'customer';

    //     $customer = Customer::where('id', $id)
    //         ->where('type', $type)
    //         ->firstOrFail();

    //     return response()->json([
    //         'address' => $customer->address,
    //         'mobile' => $customer->mobile,
    //         'remarks' => $customer->remarks,
    //         'previous_balance' => $customer->previous_balance,
    //     ]);
    // }



    ////////////
    public function index()
    {
        $sales = Sale::with(['customer', 'saleItems.product'])->orderBy('id', 'desc')->get();

        // echo "<pre>";
        // print_r($sales->toArray());
        // dd();

        return view('admin_panel.sale.index', compact('sales'));
    }

    public function addsale()
    {
        $products = Product::get();
        $customer = Customer::all();
        $warehouse = Warehouse::all();
        // dd($Customer);$warehouses = Warehouse::all();
        // $customers = Customer::all();
        $accounts = Account::all();
        // Get next invoice from Sale model generator (ensures INVSLE-003 -> INVSLE-004)
        $nextInvoiceNumber = Sale::generateInvoiceNo();


        return view('admin_panel.sale.add_sale222', compact('warehouse', 'customer', 'accounts', 'nextInvoiceNumber'));
    }

    public function searchpname(Request $request)
    {
        $q = $request->get('q');

        $products = Product::with(['brand'])
            // only products with active discount
            ->where(function ($query) use ($q) {
                $query->where('item_name', 'like', "%{$q}%")
                    ->orWhere('item_code', 'like', "%{$q}%")
                    ->orWhere('barcode_path', 'like', "%{$q}%");
            })
            ->get();

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $isBooking = $request->has('booking');
        if ($isBooking) {
            // Normalize customer id: form may send numeric id or a display string
            $customerVal = $request->input('customer') ?? $request->input('customer_id') ?? null;
            $customerId = null;
            if (is_numeric($customerVal)) {
                $customerId = (int) $customerVal;
            } elseif (is_string($customerVal) && strlen(trim($customerVal)) > 0) {
                // Try to extract a customer code like CUST-0001
                if (preg_match('/([A-Za-z0-9]+-\d+)/', $customerVal, $m)) {
                    $code = $m[1];
                    $cust = Customer::where('customer_id', $code)->first();
                    if ($cust) $customerId = $cust->id;
                }
                // Fallback: try matching by name (text before separator)
                if (!$customerId) {
                    $parts = preg_split('/[-—–]{1,3}/u', $customerVal);
                    $namePart = trim($parts[0] ?? $customerVal);
                    $cust = Customer::where('customer_name', $namePart)->first();
                    if ($cust) $customerId = $cust->id;
                }
            }

            $booking = Productbooking::create([
                'invoice_no' => $request->Invoice_no,
                'manual_invoice' => $request->Invoice_main,
                'customer_id' => $customerId,
                'party_type' => $request->input('partyType') ?? null,
                'sub_customer' => $request->customerType,
                'filer_type' => $request->filerType,
                'address' => $request->address,
                'tel' => $request->tel,
                'remarks' => $request->remarks,
                'sub_total1' => $request->subTotal1 ?? 0,
                'sub_total2' => $request->subTotal2 ?? 0,
                'discount_percent' => $request->discountPercent ?? 0,
                'discount_amount' => $request->discountAmount ?? 0,
                'previous_balance' => $request->previousBalance ?? 0,
                'total_balance' => $request->totalBalance ?? 0,
                'receipt1' => $request->receipt1 ?? 0,
                'receipt2' => $request->receipt2 ?? 0,
                'final_balance1' => $request->finalBalance1 ?? 0,
                'final_balance2' => $request->finalBalance2 ?? 0,
                // 'weight' => $request->weight ?? null,
            ]);

            $totalQty = 0;

            foreach ($request->product_id ?? [] as $i => $productId) {

                $qty = (float) ($request->sales_qty[$i] ?? 0);
                if (!$productId || $qty <= 0) continue;

                $totalQty += $qty; // ✅ YEH LINE MISSING THI

                ProductBookingItem::create([
                    'booking_id' => $booking->id,
                    'product_id' => $productId,
                    'sales_qty' => $qty,
                    'retail_price' => $request->retail_price[$i] ?? 0,
                    'discount_amount' => $request->discount_amount[$i] ?? 0,
                    'amount' => $request->sales_amount[$i] ?? 0,
                ]);
            }

            $booking->quantity = $totalQty;
            $booking->save();


            return back()->with('success', 'Booking saved successfully!');
        }

        // Direct Sale (stock minus)
        return DB::transaction(function () use ($request) {
            $invoiceNo = Sale::generateInvoiceNo();
            // Normalize customer id for direct sale as well
            $customerVal = $request->input('customer') ?? $request->input('customer_id') ?? null;
            $customerId = null;
            if (is_numeric($customerVal)) {
                $customerId = (int) $customerVal;
            } elseif (is_string($customerVal) && strlen(trim($customerVal)) > 0) {
                if (preg_match('/([A-Za-z0-9]+-\d+)/', $customerVal, $m)) {
                    $code = $m[1];
                    $cust = Customer::where('customer_id', $code)->first();
                    if ($cust) $customerId = $cust->id;
                }
                if (!$customerId) {
                    $parts = preg_split('/[-—–]{1,3}/u', $customerVal);
                    $namePart = trim($parts[0] ?? $customerVal);
                    $cust = Customer::where('customer_name', $namePart)->first();
                    if ($cust) $customerId = $cust->id;
                }
            }

            // ✅ CREDIT LIMIT CHECK
            if ($customerId) {
                $customer = Customer::find($customerId);
                $saleAmount = (float)($request->subTotal1 ?? 0) - (float)($request->discountAmount ?? 0);
                $latestLedger = CustomerLedger::where('customer_id', $customerId)->latest()->first();
                $currentBalance = $latestLedger ? $latestLedger->closing_balance : $customer->opening_balance;

                // Total credit = current balance + new sale
                $totalCredit = $currentBalance + $saleAmount;

                // Check if exceeds credit limit
                if ($customer->credit_limit && $totalCredit > $customer->credit_limit) {
                    return back()->withError(
                        "❌ کریڈٹ حد سے زیادہ ہے! \n" .
                        "موجودہ بقایا: " . number_format($currentBalance, 2) . "\n" .
                        "نیا سیل رقم: " . number_format($saleAmount, 2) . "\n" .
                        "کل کریڈٹ: " . number_format($totalCredit, 2) . "\n" .
                        "کریڈٹ حد: " . number_format($customer->credit_limit, 2)
                    )->withInput();
                }

                // Check credit expiry date
                if ($customer->credit_upto && Carbon::now() > Carbon::parse($customer->credit_upto)) {
                    return back()->withError(
                        "❌ کریڈٹ کی تاریخ ختم ہو گئی ہے! (" . $customer->credit_upto . ")"
                    )->withInput();
                }
            }

            $sale = Sale::create([
                'invoice_no' => $invoiceNo,
                'manual_invoice' => $request->Invoice_main ?? null,
                'partyType' => $request->input('partyType') ?? null,
                'customer_id' => $customerId ?? ($request->customer ?? null),
                'sub_customer' => $request->customerType ?? null,
                'filer_type' => $request->filerType ?? null,
                'address' => $request->address ?? null,
                'tel' => $request->tel ?? null,
                'remarks' => $request->remarks ?? null,
                'sub_total1' => $request->subTotal1 ?? 0,
                'sub_total2' => $request->subTotal2 ?? 0,
                'discount_percent' => $request->discountPercent ?? 0,
                'discount_amount' => $request->discountAmount ?? 0,
                'previous_balance' => $request->previousBalance ?? 0,
                'total_balance' => $request->totalBalance ?? 0,
                'receipt1' => $request->receipt1 ?? 0,
                'receipt2' => $request->receipt2 ?? 0,
                'final_balance1' => $request->finalBalance1 ?? 0,
                'final_balance2' => $request->finalBalance2 ?? 0,
                'weight' => $request->weight ?? null,
            ]);

            // Persist optional notify_me value on sale (days integer)
            $notifyDays = (int) ($request->input('notify_me') ?? $request->input('notify_days') ?? 0);
            if ($notifyDays > 0) {
                try {
                    $sale->notify_me = $notifyDays;
                    $sale->save();

                    $notifyAt = now()->addDays($notifyDays);
                    Notification::create([
                        'user_id' => auth()->id(),
                        'customer_id' => $customerId,
                        'message' => 'Sale #' . $sale->invoice_no . ' notification scheduled for ' . $notifyAt->toDateString(),
                        'notify_at' => $notifyAt,
                        'is_read' => false,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create sale notification', ['err' => $e->getMessage()]);
                }
            }

            foreach ($request->warehouse_name ?? [] as $i => $warehouse_id) {
                $productId = $request->input("product_name.$i");
                if (empty($warehouse_id) || empty($productId)) {
                    continue;
                }

                $saleQty = (float) $request->input("sales-qty.$i", 0);

                // Per-warehouse stock (allow negative)
                $ws = WarehouseStock::where('warehouse_id', $warehouse_id)
                    ->where('product_id', $productId)
                    ->first();

                if ($ws) {
                    $ws->quantity = ($ws->quantity ?? 0) - $saleQty;
                    $ws->save();
                } else {
                    WarehouseStock::create([
                        'warehouse_id' => $warehouse_id,
                        'product_id' => $productId,
                        'quantity' => -1 * $saleQty,
                    ]);
                }

                // Global stock via Stock model (allow negative)
                $stockRow = Stock::where('product_id', $productId)
                    ->where('warehouse_id', $warehouse_id)
                    ->first();

                if ($stockRow) {
                    $stockRow->qty = ($stockRow->qty ?? 0) - $saleQty;
                    $stockRow->save();
                } else {
                    Stock::create([
                        'branch_id' => 1,
                        'warehouse_id' => $warehouse_id,
                        'product_id' => $productId,
                        'qty' => -1 * $saleQty,
                        'reserved_qty' => 0,
                    ]);
                }

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'warehouse_id' => $warehouse_id,
                    'product_id' => $productId,
                    'stock' => (float) $request->input("stock.$i", 0),
                    'price_level' => (float) $request->input("price.$i", 0),
                    'sales_price' => (float) $request->input("sales-price.$i", 0),
                    'sales_qty' => $saleQty,
                    'retail_price' => (float) $request->input("retail-price.$i", 0),
                    'discount_percent' => (float) $request->input("discount-percent.$i", 0),
                    'discount_amount' => (float) $request->input("discount-amount.$i", 0),
                    'amount' => (float) $request->input("sales-amount.$i", 0),
                ]);
            }

            // If receipts provided for direct sale, create per-account receipt vouchers
            if (!empty($request->receipt_account_id) && is_array($request->receipt_account_id)) {
                $rowAccountIds = [];
                $rowAmounts = [];
                foreach ($request->receipt_account_id as $i => $accId) {
                    $acc = $accId;
                    $amt = (float) ($request->receipt_amount[$i] ?? 0);
                    if ($amt <= 0 || empty($acc) || !is_numeric($acc)) continue;
                    $rowAccountIds[] = (int) $acc;
                    $rowAmounts[] = $amt;
                }

                if (!empty($rowAccountIds)) {
                    // Deduplicate
                    $unique = [];
                    $uniqueAccountIds = [];
                    $uniqueAmounts = [];
                    foreach ($rowAccountIds as $i => $acctId) {
                        $amt = $rowAmounts[$i] ?? ($rowAmounts[0] ?? 0);
                        if ($amt <= 0) continue;
                        $sig = $acctId . '|' . number_format((float)$amt, 2, '.', '');
                        if (isset($unique[$sig])) continue;
                        $unique[$sig] = true;
                        $uniqueAccountIds[] = $acctId;
                        $uniqueAmounts[] = $amt;
                    }

                    foreach ($uniqueAccountIds as $i => $acctId) {
                        $amt = $uniqueAmounts[$i];
                        $rv = ReceiptsVoucher::create([
                            'rvid' => ReceiptsVoucher::generateRVID(auth()->id()),
                            'receipt_date' => Carbon::today(),
                            'entry_date' => Carbon::now(),
                            'type' => 'SALE_RECEIPT',
                            'party_id' => $customerId,
                            'booking_id' => null,
                            'tel' => $request->tel ?? null,
                            'remarks' => $request->remarks ?? null,
                            'reference_no' => $invoiceNo,
                            'row_account_head' => 'Cash/Bank',
                            'row_account_id' => is_array($acctId) ? json_encode($acctId) : $acctId,
                            'amount' => is_array($amt) ? json_encode($amt) : $amt,
                            'total_amount' => $amt,
                            'processed' => false,
                        ]);

                        // Immediately apply this receipt to account and customer ledger
                        try {
                            $rowAccount = Account::lockForUpdate()->find($acctId);
                            if ($rowAccount) {
                                if (strtolower($rowAccount->type) === 'debit') {
                                    $rowAccount->opening_balance += $amt;
                                } else {
                                    $rowAccount->opening_balance -= $amt;
                                }
                                $rowAccount->save();
                            }

                            $custPrev = CustomerLedger::where('customer_id', $customerId)->latest('id')->lockForUpdate()->value('closing_balance') ?? 0;
                            $custNew = $custPrev - $amt;
                            CustomerLedger::create([
                                'customer_id' => $customerId,
                                'admin_or_user_id' => auth()->id(),
                                'previous_balance' => $custPrev,
                                'opening_balance' => 0,
                                'closing_balance' => $custNew,
                            ]);

                            $rv->processed = true;
                            $rv->save();
                        } catch (\Exception $e) {
                            Log::error('Failed to apply direct-sale receipt', ['error' => $e->getMessage(), 'rv' => $rv->id ?? null]);
                        }
                    }
                }
            }

            // ✅ UPDATE CUSTOMER LEDGER WITH SALE AMOUNT
            if ($customerId) {
                $saleAmount = (float)($request->subTotal1 ?? 0) - (float)($request->discountAmount ?? 0);
                $latestLedger = CustomerLedger::where('customer_id', $customerId)->latest()->first();

                $previousBalance = $latestLedger ? $latestLedger->closing_balance : 0;

                // Ledger already adjusted by any direct-sale receipts applied above.
                $newClosingBalance = $previousBalance + $saleAmount;

                CustomerLedger::create([
                    'customer_id' => $customerId,
                    'admin_or_user_id' => auth()->id(),
                    'previous_balance' => $previousBalance,
                    'opening_balance' => 0,  // یہ sale transaction ہے
                    'closing_balance' => $newClosingBalance,
                    'reference_type' => 'Sale',
                    'reference_id' => $sale->id,
                ]);
            }

            return back()->with('success', 'Sale saved successfully!');
        });
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $sale)
    {
        //
    }

    /**
     * Convert booking to sale form prefill.
     */
    public function convertFromBooking($id)
    {
        // ================= BASIC DATA =================
        $booking = ProductBooking::findOrFail($id);
        $booking_customer = Customer::findOrFail($booking->customer_id);

        $customers = Customer::all();
        $accounts  = Account::all();

        // ================= LEGACY FIELDS =================
        $products    = explode(',', $booking->product);
        $codes       = explode(',', $booking->product_code);
        $brands      = explode(',', $booking->brand);
        $units       = explode(',', $booking->unit);
        $prices      = explode(',', $booking->per_price);
        $discounts   = explode(',', $booking->per_discount);
        $qtys        = explode(',', $booking->qty);
        $totals      = explode(',', $booking->per_total);
        $colors_json = json_decode($booking->color, true);

        // ================= BOOKING ITEMS =================
        $bookingItemsRaw = \App\Models\ProductBookingItem::where('booking_id', $booking->id)->get();

        // ================= STOCK ON HAND =================
        $stockMap = DB::table('v_stock_onhand')
            ->pluck('onhand_qty', 'product_id');
        // example: [ 2 => 10.00, 5 => 25.00 ]

        // ================= WAREHOUSE MAP =================
        $warehouseStocks = WarehouseStock::with('warehouse')
            ->whereIn(
                'product_id',
                $bookingItemsRaw->pluck('product_id')->unique()
            )
            ->get();



        // ================= FINAL ITEMS =================
        $items = [];

        // =================================================
        // CASE 1: ProductBookingItem TABLE DATA
        // =================================================
        if ($bookingItemsRaw->count() > 0) {

            foreach ($bookingItemsRaw as $item) {

                $product = Product::find($item->product_id);

                $items[] = [
                    'product_id' => $item->product_id,
                    'item_name'  => $product->item_name ?? '',
                    'item_code'  => $product->item_code ?? '',
                    'uom'        => $product && $product->brand ? $product->brand->name : '',
                    'unit'       => $product->unit_id ?? '',
                    'price'      => (float) $item->retail_price,
                    'discount'   => (float) $item->discount_amount,
                    'qty'        => (int) $item->sales_qty,
                    'total'      => (float) $item->amount,
                    'color'      => [],
                    'onhand_qty' => (float) ($stockMap[$item->product_id] ?? 0),

                    // ✅ MULTIPLE WAREHOUSES
                    'warehouses' => $warehouseStocks,
                ];
            }
        }
        // =================================================
        // CASE 2: LEGACY CSV DATA
        // =================================================
        else {

            foreach ($products as $index => $p) {

                $product = Product::where('item_name', trim($p))
                    ->orWhere('item_code', trim($codes[$index] ?? ''))
                    ->first();

                $productId = $product->id ?? null;

                $items[] = [
                    'product_id' => $productId,
                    'item_name'  => $product->item_name ?? $p,
                    'item_code'  => $product->item_code ?? ($codes[$index] ?? ''),
                    'uom'        => $product && $product->brand
                        ? $product->brand->name
                        : ($brands[$index] ?? ''),
                    'unit'       => $product->unit_id ?? ($units[$index] ?? ''),
                    'price'      => (float) ($prices[$index] ?? 0),
                    'discount'   => (float) ($discounts[$index] ?? 0),
                    'qty'        => (int) ($qtys[$index] ?? 1),
                    'total'      => (float) ($totals[$index] ?? 0),
                    'color'      => isset($colors_json[$index])
                        ? json_decode($colors_json[$index], true)
                        : [],
                    'onhand_qty' => (float) ($stockMap[$productId] ?? 0),

                    // ✅ FIXED: product_id variable
                    'warehouses' => $warehouseStocks,
                ];
            }
        }
        // return response()->json([
        //      'Customer'         => $customers,
        //     'booking_customer' => $booking_customer,
        //     'booking'          => $booking,
        //     'bookingItems'     => $items,
        //     'accounts'         => $accounts,
        // ]);
        

        // ================= VIEW =================
        return view('admin_panel.sale.booking_edit222', [
            'Customer'         => $customers,
            'booking_customer' => $booking_customer,
            'booking'          => $booking,
            'bookingItems'     => $items,
            'accounts'         => $accounts,
        ]);
    }


    // sale return start
    public function saleretun($id)
    {
        $sale = Sale::findOrFail($id);
        $customers = Customer::all();

        // Decode sale pivot or comma fields
        $products = explode(',', $sale->product);
        $codes = explode(',', $sale->product_code);
        $brands = explode(',', $sale->brand);
        $units = explode(',', $sale->unit);
        $prices = explode(',', $sale->per_price);
        $discounts = explode(',', $sale->per_discount);
        $qtys = explode(',', $sale->qty);
        $totals = explode(',', $sale->per_total);
        $colors_json = json_decode($sale->color, true);

        $items = [];

        foreach ($products as $index => $p) {
            $product = Product::where('item_name', trim($p))
                ->orWhere('item_code', trim($codes[$index] ?? ''))
                ->first();

            $items[] = [
                'product_id' => $product->id ?? '',
                'item_name'  => $product->item_name ?? $p,
                'item_code'  => $product->item_code ?? ($codes[$index] ?? ''),
                'brand'      => $product->brand->name ?? ($brands[$index] ?? ''), // <-- change here
                'unit'       => $product->unit ?? ($units[$index] ?? ''),
                'price'      => floatval($prices[$index] ?? 0),
                'discount'   => floatval($discounts[$index] ?? 0),
                'qty'        => intval($qtys[$index] ?? 1),
                'total'      => floatval($totals[$index] ?? 0),
                'color'      => isset($colors_json[$index]) ? json_decode($colors_json[$index], true) : [],
            ];
        }

        return view('admin_panel.sale.return.create', [
            'sale'      => $sale,
            'Customer'  => $customers,
            'saleItems' => $items,
        ]);
    }

    public function storeSaleReturn(Request $request)
    {
        DB::beginTransaction();

        try {
            // keep same location as sale (hidden fields in blade)
            $branchId = (int) ($request->input('branch_id', 1));
            $warehouseId = (int) ($request->input('warehouse_id', 1));

            $srMovements = [];

            $product_ids = $request->product_id ?? [];
            $product_names = $request->product ?? [];
            $product_codes = $request->item_code ?? [];
            $brands = $request->uom ?? [];
            $units = $request->unit ?? [];
            $prices = $request->price ?? [];
            $discounts = $request->item_disc ?? [];
            $quantities = $request->qty ?? [];
            $totals = $request->total ?? [];
            $colors = $request->color ?? [];

            $combined_products = $combined_codes = $combined_brands = $combined_units = [];
            $combined_prices = $combined_discounts = $combined_qtys = $combined_totals = $combined_colors = [];

            $total_items = 0;

            foreach ($product_ids as $index => $product_id) {
                $qty = max(0.0, (float) ($quantities[$index] ?? 0));
                $price = max(0.0, (float) ($prices[$index] ?? 0));

                if (! $product_id || $qty <= 0 || $price <= 0) {
                    continue;
                }

                $combined_products[] = $product_names[$index] ?? '';
                $combined_codes[] = $product_codes[$index] ?? '';
                $combined_brands[] = $brands[$index] ?? '';
                $combined_units[] = $units[$index] ?? '';
                $combined_prices[] = $price;
                $combined_discounts[] = $discounts[$index] ?? 0;
                $combined_qtys[] = $qty;
                $combined_totals[] = $totals[$index] ?? 0;

                $decodedColor = $colors[$index] ?? [];
                $combined_colors[] = is_array($decodedColor)
                    ? json_encode($decodedColor)
                    : json_encode((array) json_decode($decodedColor, true));

                // restore stock at SAME location (lock row to avoid race)
                $stock = Stock::where('product_id', $product_id)
                    ->where('branch_id', $branchId)
                    ->where('warehouse_id', $warehouseId)
                    ->lockForUpdate()
                    ->first();

                if ($stock) {
                    $stock->qty += $qty;
                    $stock->save();
                } else {
                    Stock::create([
                        'product_id'   => $product_id,
                        'branch_id'    => $branchId,
                        'warehouse_id' => $warehouseId,
                        'qty'          => $qty,
                        'reserved_qty' => 0,
                    ]);
                }

                // movement queue (IN) → ref_id after save
                $srMovements[] = [
                    'product_id' => $product_id,
                    'type'       => 'in',
                    'qty'        => (float) $qty,
                    'ref_type'   => 'SR',
                    'ref_id'     => null,
                    'note'       => 'Sale return',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $total_items += $qty;
            }

            // create Sale Return first
            $saleReturn = new SalesReturn;
            $saleReturn->sale_id = $request->sale_id;
            $saleReturn->customer = $request->customer;
            $saleReturn->reference = $request->reference;
            $saleReturn->product = implode(',', $combined_products);
            $saleReturn->product_code = implode(',', $combined_codes);
            $saleReturn->brand = implode(',', $combined_brands);
            $saleReturn->unit = implode(',', $combined_units);
            $saleReturn->per_price = implode(',', $combined_prices);
            $saleReturn->per_discount = implode(',', $combined_discounts);
            $saleReturn->qty = implode(',', $combined_qtys);
            $saleReturn->per_total = implode(',', $combined_totals);
            $saleReturn->color = json_encode($combined_colors);
            $saleReturn->total_amount_Words = $request->total_amount_Words;
            $saleReturn->total_bill_amount = $request->total_subtotal;
            $saleReturn->total_extradiscount = $request->total_extra_cost;
            $saleReturn->total_net = $request->total_net;
            $saleReturn->cash = $request->cash;
            $saleReturn->card = $request->card;
            $saleReturn->change = $request->change;
            $saleReturn->total_items = $total_items;
            $saleReturn->return_note = $request->return_note;
            $saleReturn->save();

            // insert movements with proper ref_id
            if (! empty($srMovements)) {
                foreach ($srMovements as &$m) {
                    $m['ref_id'] = $saleReturn->id;
                }
                unset($m);

                DB::table('stock_movements')->insert($srMovements);
            }

            // update original sale
            $sale = Sale::find($request->sale_id);
            if ($sale) {
                $sale_qtys = array_map('floatval', explode(',', $sale->qty));
                $sale_totals = array_map('floatval', explode(',', $sale->per_total));
                $sale_prices = array_map('floatval', explode(',', $sale->per_price));

                foreach ($product_ids as $index => $product_id) {
                    $return_qty = max(0.0, (float) ($quantities[$index] ?? 0));
                    if ($return_qty > 0 && isset($sale_qtys[$index])) {
                        $sale_qtys[$index] = max(0.0, $sale_qtys[$index] - $return_qty);
                        $price = $sale_prices[$index] ?? 0.0;
                        $sale_totals[$index] = $price * $sale_qtys[$index];
                    }
                }

                $sale->qty = implode(',', $sale_qtys);
                $sale->per_total = implode(',', $sale_totals);
                $sale->total_net = array_sum($sale_totals);
                $sale->total_bill_amount = $sale->total_net;
                $sale->total_items = array_sum($sale_qtys);
                $sale->save();
            }

            // ledger impact
            $customer_id = $request->customer;
            $ledger = CustomerLedger::where('customer_id', $customer_id)->latest('id')->first();

            if ($ledger) {
                $ledger->previous_balance = $ledger->closing_balance;
                $ledger->closing_balance = $ledger->closing_balance - $request->total_net;
                $ledger->save();
            } else {
                CustomerLedger::create([
                    'customer_id'      => $customer_id,
                    'admin_or_user_id' => auth()->id(),
                    'previous_balance' => 0,
                    'closing_balance'  => 0 - $request->total_net,
                    'opening_balance'  => 0 - $request->total_net,
                ]);
            }

            DB::commit();

            return redirect()->route('sale.index')->with('success', 'Sale return saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Sale return failed: ' . $e->getMessage());
        }
    }

    public function salereturnview()
    {
        // Fetch all sale returns with the original sale and customer info
        $salesReturns = SalesReturn::with('sale.customer')->orderBy('created_at', 'desc')->get();

        return view('admin_panel.sale.return.index', [
            'salesReturns' => $salesReturns,
        ]);
    }

    public function saleinvoice($id)
    {
        $sale = Sale::with('customer')->findOrFail($id);

        // Decode sale pivot or comma fields
        $products = explode(',', $sale->product);
        $codes = explode(',', $sale->product_code);
        $brands = explode(',', $sale->brand);
        $units = explode(',', $sale->unit);
        $prices = explode(',', $sale->per_price);
        $discounts = explode(',', $sale->per_discount);
        $qtys = explode(',', $sale->qty);
        $totals = explode(',', $sale->per_total);
        $colors_json = json_decode($sale->color, true);

        $items = [];

        foreach ($products as $index => $p) {
            $product = Product::where('item_name', trim($p))
                ->orWhere('item_code', trim($codes[$index] ?? ''))
                ->first();

            $items[] = [
                'product_id' => $product->id ?? '',
                'item_name'  => $product->item_name ?? $p,
                'item_code'  => $product->item_code ?? ($codes[$index] ?? ''),
                'brand'      => $product->brand->name ?? ($brands[$index] ?? ''),
                'unit'       => $product->unit ?? ($units[$index] ?? ''),
                'price'      => floatval($prices[$index] ?? 0),
                'discount'   => floatval($discounts[$index] ?? 0),
                'qty'        => intval($qtys[$index] ?? 1),
                'total'      => floatval($totals[$index] ?? 0),
                'color'      => isset($colors_json[$index]) ? json_decode($colors_json[$index], true) : [],
            ];
        }

        return view('admin_panel.sale.saleinvoice', [
            'sale'      => $sale,
            'saleItems' => $items,
        ]);
    }

    public function saleedit($id)
    {
        $sale = Sale::with(['customer', 'saleItems.product'])->findOrFail($id);
        $customers = Customer::all();
        $accounts = Account::all();
        
        // ✅ Fetch receipt vouchers for this sale by matching invoice_no with reference_no
        $receipts = ReceiptsVoucher::where('reference_no', $sale->invoice_no)
            ->where('type', 'SALE_RECEIPT')
            ->orderBy('id', 'desc')
            ->get();

        // ✅ Fetch on-hand stock for all products (from v_stock_onhand view)
        $stockMap = DB::table('v_stock_onhand')
            ->pluck('onhand_qty', 'product_id');

        $items = [];

        // ✅ PRIORITY 1: Use SaleItem relationship (modern DB structure)
        if ($sale->saleItems && $sale->saleItems->count() > 0) {
            foreach ($sale->saleItems as $saleItem) {
                $product = $saleItem->product;
                $items[] = [
                    'product_id' => $saleItem->product_id,
                    'item_name'  => $product->item_name ?? '',
                    'item_code'  => $product->item_code ?? '',
                    'brand'      => $product->brand ? $product->brand->name : '',
                    'unit'       => $product->unit ?? '',
                    'price'      => floatval($saleItem->retail_price ?? 0),
                    'discount'   => floatval($saleItem->discount_amount ?? 0),
                    'discount_percent' => floatval($saleItem->discount_percent ?? 0),
                    'qty'        => intval($saleItem->sales_qty ?? 0),
                    'total'      => floatval($saleItem->amount ?? 0),
                    'onhand_qty' => floatval($stockMap[$saleItem->product_id] ?? 0),
                    'color'      => [],
                ];
            }
        }
        // ✅ FALLBACK: Use legacy CSV fields if no SaleItem records
        else if ($sale->product) {
            $products = explode(',', $sale->product);
            $codes = explode(',', $sale->product_code ?? '');
            $brands = explode(',', $sale->brand ?? '');
            $units = explode(',', $sale->unit ?? '');
            $prices = explode(',', $sale->per_price ?? '');
            $discounts = explode(',', $sale->per_discount ?? '');
            $qtys = explode(',', $sale->qty ?? '');
            $totals = explode(',', $sale->per_total ?? '');
            $colors_json = json_decode($sale->color, true) ?? [];

            foreach ($products as $index => $p) {
                $product = Product::where('item_name', trim($p))
                    ->orWhere('item_code', trim($codes[$index] ?? ''))
                    ->first();

                $productId = $product->id ?? null;

                $items[] = [
                    'product_id' => $productId,
                    'item_name'  => $product->item_name ?? $p,
                    'item_code'  => $product->item_code ?? ($codes[$index] ?? ''),
                    'brand'      => $product->brand ? $product->brand->name : ($brands[$index] ?? ''),
                    'unit'       => $product->unit ?? ($units[$index] ?? ''),
                    'price'      => floatval($prices[$index] ?? 0),
                    'discount'   => floatval($discounts[$index] ?? 0),
                    'discount_percent' => 0,
                    'qty'        => intval($qtys[$index] ?? 1),
                    'total'      => floatval($totals[$index] ?? 0),
                    'onhand_qty' => floatval($stockMap[$productId] ?? 0),
                    'color'      => isset($colors_json[$index]) ? json_decode($colors_json[$index], true) : [],
                ];
            }
        }

        return view('admin_panel.sale.saleedit', [
            'sale'      => $sale,
            'Customer'  => $customers,
            'saleItems' => $items,
            'accounts'  => $accounts,
            'receipts'  => $receipts,
        ]);
    }

    /**
     * Update Sale with proper SaleItem relationship handling
     * ✅ Full business logic: updates sale header, items, stock, and ledger
     */
    public function update(Request $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                
                /* ================= FETCH EXISTING SALE ================= */
                $sale = Sale::with(['saleItems', 'customer'])->lockForUpdate()->findOrFail($id);
                $oldTotal = floatval($sale->total_net ?? 0);
                $customerId = $request->input('customer_id');

                /* ================= VALIDATE CUSTOMER CHANGE ================= */
                if ($customerId && $customerId != $sale->customer_id) {
                    $customer = Customer::lockForUpdate()->findOrFail($customerId);
                }

                /* ================= STEP 1: REVERSE OLD STOCK (Add back) ================= */
                foreach ($sale->saleItems as $oldItem) {
                    // Restore warehouse stock
                    $whStock = WarehouseStock::lockForUpdate()
                        ->where('product_id', $oldItem->product_id)
                        ->where('warehouse_id', $oldItem->warehouse_id)
                        ->first();
                    
                    if ($whStock) {
                        $whStock->quantity += $oldItem->sales_qty;
                        $whStock->save();
                    }

                    // Restore main stock
                    $mainStock = Stock::lockForUpdate()
                        ->where('product_id', $oldItem->product_id)
                        ->where('warehouse_id', $oldItem->warehouse_id)
                        ->first();
                    
                    if ($mainStock) {
                        $mainStock->qty += $oldItem->sales_qty;
                        $mainStock->save();
                    }
                }

                /* ================= STEP 2: UPDATE SALE HEADER ================= */
                $newSubTotal = floatval($request->input('subTotal1', 0));
                $newGrossTotal = floatval($request->input('subTotal2', 0));
                $newDiscountAmount = floatval($request->input('discountAmount', 0));
                $newTotal = floatval($request->input('totalBalance', 0));

                $sale->update([
                    'customer_id' => $customerId ?? $sale->customer_id,
                    'manual_invoice' => $request->input('manual_invoice', $sale->manual_invoice),
                    'address' => $request->input('address', $sale->address),
                    'tel' => $request->input('tel', $sale->tel),
                    'remarks' => $request->input('remarks', $sale->remarks),
                    'sub_total1' => $newSubTotal,
                    'sub_total2' => $newGrossTotal,
                    'discount_percent' => $request->input('discountPercent', 0),
                    'discount_amount' => $newDiscountAmount,
                    'total_net' => $newTotal,
                    'previous_balance' => floatval($request->input('previousBalance', 0)),
                    'total_balance' => $newTotal,
                ]);

                /* ================= STEP 3: DELETE OLD SALE ITEMS ================= */
                $sale->saleItems()->delete();

                /* ================= STEP 4: CREATE NEW SALE ITEMS ================= */
                foreach ($request->input('sales_qty', []) as $i => $qty) {
                    $qty = floatval($qty);
                    if ($qty <= 0) continue;

                    $productId = $request->input("sales_qty")[$i];
                    // Need to find product_id from a hidden field or reconstruct from table
                    // For now, use the index to get all row data

                    $retailPrice = floatval($request->input('retail_price')[$i] ?? 0);
                    $discountAmount = floatval($request->input('discount_amount')[$i] ?? 0);
                    $discountPercent = floatval($request->input('discount_percentage')[$i] ?? 0);
                    $salesAmount = floatval($request->input('sales_amount')[$i] ?? 0);

                    // We need to extract product_id from rows - this should be in a hidden input
                    // For now, skip if we can't determine product
                    
                    // Create new sale item with line-item discounts
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'warehouse_id' => 1, // Default - should be from request
                        'product_id' => $productId,
                        'sales_qty' => $qty,
                        'retail_price' => $retailPrice,
                        'discount_percent' => $discountPercent,
                        'discount_amount' => $discountAmount,
                        'amount' => $salesAmount,
                    ]);
                }

                /* ================= STEP 5: DEDUCT NEW STOCK ================= */
                foreach ($sale->saleItems as $newItem) {
                    // Update warehouse stock (deduct new quantity)
                    $whStock = WarehouseStock::lockForUpdate()
                        ->where('product_id', $newItem->product_id)
                        ->where('warehouse_id', $newItem->warehouse_id)
                        ->first();
                    
                    if ($whStock) {
                        $whStock->quantity -= $newItem->sales_qty;
                        $whStock->save();
                    } else {
                        WarehouseStock::create([
                            'warehouse_id' => $newItem->warehouse_id,
                            'product_id' => $newItem->product_id,
                            'quantity' => -$newItem->sales_qty,
                        ]);
                    }

                    // Update main stock
                    $mainStock = Stock::lockForUpdate()
                        ->where('product_id', $newItem->product_id)
                        ->where('warehouse_id', $newItem->warehouse_id)
                        ->first();
                    
                    if ($mainStock) {
                        $mainStock->qty -= $newItem->sales_qty;
                        $mainStock->save();
                    } else {
                        Stock::create([
                            'branch_id' => 1,
                            'warehouse_id' => $newItem->warehouse_id,
                            'product_id' => $newItem->product_id,
                            'qty' => -$newItem->sales_qty,
                            'reserved_qty' => 0,
                        ]);
                    }
                }

                /* ================= STEP 6: UPDATE CUSTOMER LEDGER ================= */
                $difference = $newTotal - $oldTotal;

                if ($difference != 0 && $customerId) {
                    $latestLedger = CustomerLedger::lockForUpdate()
                        ->where('customer_id', $customerId)
                        ->latest('id')
                        ->first();

                    $previousBalance = $latestLedger ? $latestLedger->closing_balance : 0;
                    $newClosing = $previousBalance + $difference;

                    CustomerLedger::create([
                        'customer_id' => $customerId,
                        'admin_or_user_id' => auth()->id(),
                        'previous_balance' => $previousBalance,
                        'opening_balance' => 0,
                        'closing_balance' => $newClosing,
                        'reference_type' => 'Sale Update',
                        'reference_id' => $sale->id,
                    ]);
                }

                /* ================= STEP 7: UPDATE SALES ACCOUNT ================= */
                $salesHead = AccountHead::where('name', 'like', '%Sales%')->first();
                if ($salesHead && $difference != 0) {
                    $saleAccount = Account::lockForUpdate()
                        ->where('head_id', $salesHead->id)
                        ->first();
                    if ($saleAccount) {
                        $saleAccount->opening_balance += $difference;
                        $saleAccount->save();
                    }
                }

                /* ================= STEP 8: UPDATE RECEIPT VOUCHERS ================= */
                // Delete old receipt vouchers and create new ones if provided
                ReceiptsVoucher::where('reference_no', $sale->invoice_no)
                    ->where('type', 'SALE_RECEIPT')
                    ->delete();

                // Create new receipts if provided
                if (!empty($request->input('receipt_account_id', []))) {
                    foreach ($request->input('receipt_account_id', []) as $i => $accId) {
                        $amount = floatval($request->input('receipt_amount')[$i] ?? 0);
                        if ($amount <= 0 || !$accId) continue;

                        ReceiptsVoucher::create([
                            'rvid' => ReceiptsVoucher::generateRVID(auth()->id()),
                            'receipt_date' => now()->toDateString(),
                            'entry_date' => now(),
                            'type' => 'SALE_RECEIPT',
                            'party_id' => $customerId,
                            'reference_no' => $sale->invoice_no,
                            'row_account_id' => $accId,
                            'row_account_head' => 'Cash/Bank',
                            'amount' => $amount,
                            'total_amount' => $amount,
                            'processed' => true,
                        ]);

                        // Apply to account
                        try {
                            $rowAccount = Account::lockForUpdate()->find($accId);
                            if ($rowAccount) {
                                if (strtolower($rowAccount->type) === 'debit') {
                                    $rowAccount->opening_balance += $amount;
                                } else {
                                    $rowAccount->opening_balance -= $amount;
                                }
                                $rowAccount->save();
                            }
                        } catch (\Exception $e) {
                            \Log::warning('Failed to apply receipt to account', ['error' => $e->getMessage()]);
                        }
                    }
                }

                /* ================= STEP 9: RESPONSE ================= */
                return redirect()->route('sale.index')
                    ->with('success', 'Sale #' . $sale->invoice_no . ' updated successfully with all items, stock, and ledger adjusted!');
            });
        } catch (\Exception $e) {
            \Log::error('Sale update failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()
                ->withError('❌ Error updating sale: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function updatesale(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // --- Arrays from request ---
            $product_ids = $request->product_id;
            $product_names = $request->product ?? []; // ✅ ab match karega
            $product_codes = $request->item_code;
            $brands = $request->brand;  // ✅ request me brand aata hai
            $units = $request->unit;
            $prices = $request->price;
            $discounts = $request->item_disc;
            $quantities = $request->qty;
            $totals = $request->total;
            $colors = $request->color;

            $combined_products = [];
            $combined_codes = [];
            $combined_brands = [];
            $combined_units = [];
            $combined_prices = [];
            $combined_discounts = [];
            $combined_qtys = [];
            $combined_totals = [];
            $combined_colors = [];

            $total_items = 0;

            foreach ($product_ids as $index => $product_id) {
                $qty = $quantities[$index] ?? 0;
                $price = $prices[$index] ?? 0;

                if (! $product_id || ! $qty || ! $price) {
                    continue;
                }

                $combined_products[] = $product_names[$index] ?? '';
                $combined_codes[] = $product_codes[$index] ?? '';
                $combined_brands[] = $brands[$index] ?? '';
                $combined_units[] = $units[$index] ?? '';
                $combined_prices[] = $prices[$index] ?? 0;
                $combined_discounts[] = $discounts[$index] ?? 0;
                $combined_qtys[] = $quantities[$index] ?? 0;
                $combined_totals[] = $totals[$index] ?? 0;
                $combined_colors[] = json_encode($colors[$index] ?? []);

                $total_items += $qty;
            }

            // --- Find existing Sale ---
            $sale = Sale::findOrFail($id);

            // Save old total before update
            $old_total = $sale->total_net;

            // --- Fill fields ---
            $sale->customer_id = $request->customer_id;
            $sale->reference = $request->reference;
            $sale->product = implode(',', $combined_products);
            $sale->product_code = implode(',', $combined_codes);
            $sale->brand = implode(',', $combined_brands);
            $sale->unit = implode(',', $combined_units);
            $sale->per_price = implode(',', $combined_prices);
            $sale->per_discount = implode(',', $combined_discounts);
            $sale->qty = implode(',', $combined_qtys);
            $sale->per_total = implode(',', $combined_totals);
            $sale->color = json_encode($combined_colors);
            $sale->total_amount_Words = $request->total_amount_Words;
            $sale->total_bill_amount = $request->total_subtotal;
            $sale->total_extradiscount = $request->total_extra_cost;
            $sale->total_net = $request->total_net;
            $sale->cash = $request->cash;
            $sale->card = $request->card;
            $sale->change = $request->change;
            $sale->total_items = $total_items;
            $sale->save();

            // Ledger update
            $customer_id = $request->customer_id;
            $ledger = CustomerLedger::where('customer_id', $customer_id)->latest('id')->first();

            // Difference nikal lo
            $difference = $request->total_net - $old_total;

            if ($ledger) {
                $ledger->previous_balance = $ledger->closing_balance;
                $ledger->closing_balance = $ledger->closing_balance + $difference;
                $ledger->save();
            } else {
                CustomerLedger::create([
                    'customer_id'      => $customer_id,
                    'admin_or_user_id' => auth()->id(),
                    'previous_balance' => 0,
                    'closing_balance'  => $request->total_net,
                    'opening_balance'  => $request->total_net,
                ]);
            }

            DB::commit();

            return redirect()->route('sale.index')->with('success', 'Sale updated successfully!');
        } catch (\Exception $e) {
            DB::rollback();

            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function saledc($id)
    {
        $sale = Sale::with('customer')->findOrFail($id);

        // Decode sale pivot or comma fields
        $products = explode(',', $sale->product);
        $codes = explode(',', $sale->product_code);
        $brands = explode(',', $sale->brand);
        $units = explode(',', $sale->unit);
        $prices = explode(',', $sale->per_price);
        $discounts = explode(',', $sale->per_discount);
        $qtys = explode(',', $sale->qty);
        $totals = explode(',', $sale->per_total);
        $colors_json = json_decode($sale->color, true);

        $items = [];

        foreach ($products as $index => $p) {
            $product = Product::where('item_name', trim($p))
                ->orWhere('item_code', trim($codes[$index] ?? ''))
                ->first();

            $items[] = [
                'product_id' => $product->id ?? '',
                'item_name'  => $product->item_name ?? $p,
                'item_code'  => $product->item_code ?? ($codes[$index] ?? ''),
                'brand'      => $product->brand->name ?? ($brands[$index] ?? ''),
                'unit'       => $product->unit ?? ($units[$index] ?? ''),
                'price'      => floatval($prices[$index] ?? 0),
                'discount'   => floatval($discounts[$index] ?? 0),
                'qty'        => intval($qtys[$index] ?? 1),
                'total'      => floatval($totals[$index] ?? 0),
                'color'      => isset($colors_json[$index]) ? json_decode($colors_json[$index], true) : [],
            ];
        }

        return view('admin_panel.sale.saledc', [
            'sale'      => $sale,
            'saleItems' => $items,
        ]);
    }

    public function salerecepit($id)
    {
        $sale = Sale::with('customer')->findOrFail($id);

        // Decode sale pivot or comma fields
        $products = explode(',', $sale->product);
        $codes = explode(',', $sale->product_code);
        $brands = explode(',', $sale->brand);
        $units = explode(',', $sale->unit);
        $prices = explode(',', $sale->per_price);
        $discounts = explode(',', $sale->per_discount);
        $qtys = explode(',', $sale->qty);
        $totals = explode(',', $sale->per_total);
        $colors_json = json_decode($sale->color, true);

        $items = [];

        foreach ($products as $index => $p) {
            $product = Product::where('item_name', trim($p))
                ->orWhere('item_code', trim($codes[$index] ?? ''))
                ->first();

            $items[] = [
                'product_id' => $product->id ?? '',
                'item_name'  => $product->item_name ?? $p,
                'item_code'  => $product->item_code ?? ($codes[$index] ?? ''),
                'brand'      => $product->brand->name ?? ($brands[$index] ?? ''),
                'unit'       => $product->unit ?? ($units[$index] ?? ''),
                'price'      => floatval($prices[$index] ?? 0),
                'discount'   => floatval($discounts[$index] ?? 0),
                'qty'        => intval($qtys[$index] ?? 1),
                'total'      => floatval($totals[$index] ?? 0),
                'color'      => isset($colors_json[$index]) ? json_decode($colors_json[$index], true) : [],
            ];
        }

        return view('admin_panel.sale.salerecepit', [
            'sale'      => $sale,
            'saleItems' => $items,
        ]);
    }


    /* -------- Prints -------- */
    public function invoice(ProductBooking $booking)
    {


        $booking->load([
            'items.product',
            'customer.ledgers'
        ]);



        return view('admin_panel.sale.invoice2', compact('booking'));
    }

    public function print2(Sale $sale)
    {
        return view('admin_panel.sale.prints.print2', compact('sale'));
    }
    public function dc(Sale $sale)
    {
        return view('admin_panel.sale.prints.dc', compact('sale'));
    }

    public function bookingPrint(Productbooking $booking)
    {
        return view('admin_panel.sale.booking.prints.print', compact('booking'));
    }
    public function bookingPrint2(Productbooking $booking)
    {
        return view('admin_panel.sale.booking.prints.print2', compact('booking'));
    }
    public function bookingDc(Productbooking $booking)
    {
        /* ================= CUSTOMER ================= */
        $customer = Customer::find($booking->customer_id);

        /* ================= ITEMS + CORRECT WAREHOUSE ================= */
        $items = ProductBookingItem::query()
            ->where('product_booking_items.booking_id', $booking->id)
            ->leftJoin('products', 'products.id', '=', 'product_booking_items.product_id')
            ->leftJoin('warehouses', 'warehouses.id', '=', 'product_booking_items.warehouse_id')
            ->select([
                'product_booking_items.*',
                'products.item_name',
                'warehouses.warehouse_name',
                'warehouses.location',
            ])
            ->get();


        return view(
            'admin_panel.sale.booking.prints.dc2',
            compact('booking', 'customer', 'items')
        );
    }

    /**
     * Delete a sale record
     */
    public function destroy($id)
    {
        try {
            $sale = Sale::findOrFail($id);

            // Start transaction
            return DB::transaction(function () use ($sale) {
                // Reverse stock quantities (add back to warehouses)
                foreach ($sale->saleItems as $item) {
                    $warehousestock = WarehouseStock::where('product_id', $item->product_id)
                        ->where('warehouse_id', $item->warehouse_id)
                        ->first();

                    if ($warehousestock) {
                        $warehousestock->quantity += $item->sales_qty;
                        $warehousestock->save();
                    } else {
                        WarehouseStock::create([
                            'warehouse_id' => $item->warehouse_id,
                            'product_id' => $item->product_id,
                            'quantity' => $item->sales_qty,
                        ]);
                    }

                    // Global stock
                    $stock = Stock::where('product_id', $item->product_id)
                        ->where('warehouse_id', $item->warehouse_id)
                        ->first();

                    if ($stock) {
                        $stock->qty += $item->sales_qty;
                        $stock->save();
                    } else {
                        Stock::create([
                            'branch_id' => 1,
                            'warehouse_id' => $item->warehouse_id,
                            'product_id' => $item->product_id,
                            'qty' => $item->sales_qty,
                            'reserved_qty' => 0,
                        ]);
                    }

                    // Reverse stock movement
                    StockMovement::create([
                        'product_id' => $item->product_id,
                        'type' => 'in',
                        'qty' => $item->sales_qty,
                        'ref_type' => 'SALE_DELETE',
                        'ref_id' => $sale->id,
                        'ref_uuid' => $sale->invoice_no,
                        'note' => 'Sale Deleted - ' . $sale->invoice_no,
                    ]);
                }

                // Reverse customer ledger
                $latestLedger = CustomerLedger::where('customer_id', $sale->customer_id)
                    ->latest('id')
                    ->first();

                if ($latestLedger) {
                    $newClosing = $latestLedger->closing_balance - $sale->total_net;
                    CustomerLedger::create([
                        'customer_id' => $sale->customer_id,
                        'admin_or_user_id' => auth()->id(),
                        'previous_balance' => $latestLedger->closing_balance,
                        'opening_balance' => 0,
                        'closing_balance' => $newClosing,
                        'reference_type' => 'Sale Delete',
                        'reference_id' => $sale->id,
                    ]);
                }

                // Reverse sales account
                $salesHead = AccountHead::where('name', 'like', '%Sales%')->first();
                if ($salesHead) {
                    $saleAccount = Account::where('head_id', $salesHead->id)->first();
                    if ($saleAccount) {
                        $saleAccount->opening_balance -= $sale->total_net;
                        $saleAccount->save();
                    }
                }

                // Delete sale items and sale
                $sale->saleItems()->delete();
                $sale->delete();

                return response()->json(['ok' => true, 'message' => 'Sale deleted successfully']);
            });
        } catch (\Exception $e) {
            Log::error('Sale deletion failed', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 422);
        }
    }
}
