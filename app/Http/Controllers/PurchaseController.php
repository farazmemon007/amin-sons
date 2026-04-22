<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Inwardgatepass;
use App\Models\PaymentVoucher;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\VendorLedger;
use App\Models\PurchaseItem;
use App\Models\Stock;
use App\Models\Vendor;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Concerns\BranchScope;

class PurchaseController extends Controller
{
    use BranchScope;
    public function getPartyList(Request $request)
    {
        $type = strtolower($request->query('type', 'Main Customer'));
        // echo $type;
        // dd();
        if ($type === 'customer') {

            $customers = Customer::with(['ledgers' => function ($q) {
                $q->latest();   // last inserted ledger
            }])
                ->where('customer_type', 'Main Customer')
                ->get()
                ->map(function ($customer) {

                    $ledger = $customer->ledgers->first(); // latest ledger

                    // Agar ledger exist karta hai
                    if ($ledger) {
                        $closing = $ledger->closing_balance;
                    } else {
                        // Agar ledger hi nahi bana
                        $closing = $customer->opening_balance ?? 0;
                    }

                    return [
                        'id'              => $customer->id,
                        'text'            => $customer->customer_name,
                        'closing_balance' => $closing,
                    ];
                });

            return response()->json($customers);
        } elseif ($type === 'walkin') {
            $walkins = Customer::where('customer_type', 'Walking Customer')
                ->select('id', 'customer_name as text')
                ->get();
            return response()->json($walkins);
        }

        return response()->json([]);
    }
    /** 
     * ✅ UPDATE stocks (BRANCH-LEVEL SUMMARY)
     * stocks table is AGGREGATE - only tracks (product_id, branch_id) without warehouse detail
     * This is the branch-level total quantity across all warehouses
     */
    private function updateBranchStock(int $productId, float $qtyDelta, int $branchId): void
    {
        $affected = DB::table('stocks')
            ->where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->update([
                'qty'        => DB::raw('qty + ' . ($qtyDelta + 0)),
                'updated_at' => now(),
            ]);

        if ($affected === 0) {
            DB::table('stocks')->insert([
                'product_id'   => $productId,
                'branch_id'    => $branchId,
                'qty'          => $qtyDelta,
                'reserved_qty' => 0,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }

    /**
     * ✅ UPSERT STOCKS - Wrapper for backwards compatibility
     * Ignores warehouse_id parameter (stocks table doesn't have warehouse_id)
     * Updates only branch-level stock (product_id, branch_id)
     */
    private function upsertStocks(int $productId, float $qtyDelta, int $branchId, int $warehouseId): void
    {
        // stocks table doesn't have warehouse_id, so ignore it
        $this->updateBranchStock($productId, $qtyDelta, $branchId);
    }

    /**
     * ✅ ADD PURCHASE STOCK - Update inventory when purchase is created
     * Increases both warehouse_stocks (warehouse-level) and stocks (branch-level)
     * Used when: Creating a purchase (inbound stock movement)
     * 
     * Flow:
     * 1. Update warehouse_stocks: (product_id, branch_id, warehouse_id) += qty
     * 2. Update stocks (branch-level): (product_id, branch_id) += qty
     */
    private function addPurchaseStock(int $productId, float $qty, int $branchId, int $warehouseId): void
    {
        try {
            // ✅ STEP 1: UPDATE warehouse_stocks (WAREHOUSE-LEVEL DETAIL)
            // Each warehouse tracks its own inventory
            $warehouseStock = WarehouseStock::lockForUpdate()
                ->where('product_id', $productId)
                ->where('branch_id', $branchId)
                ->where('warehouse_id', $warehouseId)
                ->first();

            if ($warehouseStock) {
                // Entry exists - add to existing quantity
                $warehouseStock->quantity += $qty;
                $warehouseStock->save();
                
                Log::info('✅ Added to warehouse_stocks', [
                    'product_id' => $productId,
                    'branch_id' => $branchId,
                    'warehouse_id' => $warehouseId,
                    'qty_added' => $qty,
                    'new_qty' => $warehouseStock->quantity,
                ]);
            } else {
                // Entry doesn't exist - create new one
                WarehouseStock::create([
                    'product_id' => $productId,
                    'branch_id' => $branchId,
                    'warehouse_id' => $warehouseId,
                    'quantity' => $qty,
                    'remarks' => 'Created via Purchase'
                ]);
                
                Log::info('✅ Created warehouse_stocks entry', [
                    'product_id' => $productId,
                    'branch_id' => $branchId,
                    'warehouse_id' => $warehouseId,
                    'qty' => $qty,
                ]);
            }

            // ✅ STEP 2: UPDATE stocks (BRANCH-LEVEL SUMMARY)
            // stocks table is aggregate: only (product_id, branch_id), no warehouse_id
            // Sums all warehouse quantities for the branch
            $this->updateBranchStock($productId, $qty, $branchId);

        } catch (\Exception $e) {
            Log::error('❌ Failed to add purchase stock', [
                'product_id' => $productId,
                'qty' => $qty,
                'branch_id' => $branchId,
                'warehouse_id' => $warehouseId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function index()
    {
        // ✅ ERP STANDARD: Branch-based filtering
        // Super admin sees all purchases, regular users see only their branch
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super admin');
        
        // Get allowed branches for this user
        $allowedBranches = $this->allowedBranches('purchase');
        
        // Build purchase query
        $query = Purchase::with(['branch', 'warehouse', 'vendor', 'items.warehouse']);  // ✅ Added: eager load warehouse for each item
        
        // Apply branch filter unless super admin
        if (!$isSuperAdmin && !empty($allowedBranches)) {
            $query->whereIn('branch_id', $allowedBranches);
        }
        
        $Purchase = $query->latest()->get();
        
        // Determine if branch column should be shown
        // Show branch column if: user is super admin OR user has access to multiple branches
        $showBranchColumn = $isSuperAdmin || (count($allowedBranches) > 1);
        $userBranchId = $user->branch_id;
        
        return view("admin_panel.purchase.index", compact('Purchase', 'showBranchColumn', 'isSuperAdmin', 'userBranchId'));
    }

    /**
     * ✅ SHOW PENDING PURCHASE DETAILS
     * Shows complete purchase with ordered items and receiving status
     * User can click "Receive" to go to InwardGatepass form
     * 
     * @param int $id Purchase ID
     * @return \Illuminate\View\View
     */
    public function showPending($id)
    {
        $purchase = Purchase::with([
            'branch',
            'warehouse',
            'vendor',
            'items.product',
            'inwardGatepasses.items'
        ])->findOrFail($id);

        // ✅ Get vendor remaining for all products in this purchase
        $vendorRemainingMap = \App\Models\VendorRemaining::where('purchase_id', $id)
            ->get()
            ->keyBy('product_id');

        // ✅ Calculate total remaining qty across all products
        $remainingTotal = $vendorRemainingMap->sum('remaining_qty');

        return view('admin_panel.purchase.pending-details', compact('purchase', 'vendorRemainingMap', 'remainingTotal'));
    }

    public function addBill($gatepassId)
    {
        // Fetch the gatepass along with its related items and products
        $gatepass = InwardGatepass::with('items.product')->findOrFail($gatepassId);

        // Pass the gatepass data to the view
        return view('admin_panel.inward.add_bill', compact('gatepass'));
    }

    public function add_purchase()
    {
        // $userId = Auth::id();
        $Purchase = Purchase::get();
        $Vendor = Vendor::get();
        
        // ✅ ERP STANDARD: Filter warehouses by current user's branch
        $currentBranch = Auth::user()->branch_id ?? 1;
        $isSuperAdmin = Auth::user() && Auth::user()->hasRole('super admin');
        
        // For simple users: Filter to their branch warehouses only
        // For super admin: Get all warehouses (will show all in dropdown)
        if ($isSuperAdmin) {
            $Warehouse = Warehouse::get();
        } else {
            $Warehouse = Warehouse::whereHas('branches', function($q) use ($currentBranch) {
                $q->where('branch_id', $currentBranch);
            })->get();
        }
        
        $Branch = Branch::all();  // ✅ Get all branches for super admin selection
        
        // ✅ Calculate NEXT purchase invoice using branch counter (ERP Standard)
        // Same pattern as Sales: P-INV-0001, P-INV-0002, etc. per branch
        $branch = Branch::find($currentBranch);
        $nextPurchaseNumber = ((int)($branch->purchase_counter ?? 0)) + 1;
        $nextInvoice = 'P-INV-' . str_pad($nextPurchaseNumber, 4, '0', STR_PAD_LEFT);
        
        // ✅ Get all Debit accounts (Bank, Cash) for payment selection
        $bankAccounts = \App\Models\Account::with('head')
            ->where('status', 'active')
            ->whereHas('head', function ($q) {
                $q->whereIn('title', ['Bank', 'Cash', 'Asset']);
            })
            ->get();
        
        // Fallback: if no accounts found, get all active accounts
        if ($bankAccounts->isEmpty()) {
            $bankAccounts = \App\Models\Account::with('head')
                ->where('status', 'active')
                ->get();
        }
        
        return view('admin_panel.purchase.add_purchase', compact('Vendor', "Warehouse", 'Purchase', 'bankAccounts', 'nextInvoice', 'currentBranch', 'Branch', 'isSuperAdmin'));
    }
    public function store(Request $request, $gatepassId = null)
    {
        // (A) Gatepass fetch if provided
        $gatepass = null;
        if ($gatepassId) {
            $gatepass = \App\Models\InwardGatepass::with('purchase')->findOrFail($gatepassId);
            if ($gatepass->purchase) {
                return back()->with('error', 'This gatepass already has an associated bill.');
            }
        }

        // (B) Validation (warehouse_id now OPTIONAL - each line specifies its own warehouse)
        $validated = $request->validate([
            'invoice_no'          => 'nullable|string',
            'vendor_id'           => 'nullable|exists:vendors,id',
            'purchase_date'       => 'nullable|date',
            'branch_id'           => 'nullable|exists:branches,id',
            'warehouse_id'        => 'nullable|exists:warehouses,id',  // ✅ CHANGED: Now optional (per-line assignment)
            'note'                => 'nullable|string',
            'discount'            => 'nullable|numeric|min:0',
            'extra_cost'          => 'nullable|numeric|min:0',

            'product_id'          => 'array',
            'product_id.*'        => 'nullable|exists:products,id',
            'qty'                 => 'array',
            'qty.*'               => 'nullable|required_with:product_id.*|numeric|min:1',
            'price'               => 'array',
            'price.*'             => 'nullable|required_with:product_id.*|numeric|min:0',
            'unit'                => 'array',
            'unit.*'              => 'nullable|required_with:product_id.*|string',
            'item_discount'       => 'nullable|array',
            'item_discount.*'     => 'nullable|numeric|min:0',
            
            // ✅ ERP STANDARD: Per-line warehouse assignment (OPTIONAL - fallback to header)
            'line_warehouse_id'   => 'nullable|array',
            'line_warehouse_id.*' => 'nullable|exists:warehouses,id',  // ✅ Optional - uses header warehouse if not specified
            
            // ✅ NEW: Payment fields
            'payment_type'        => 'nullable|in:pay_now,pay_later',
            'payment_account_id'  => 'nullable|required_if:payment_type,pay_now|exists:accounts,id',
            'payment_amount'      => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $request, $gatepass) {
            // ✅ INTERNATIONAL ERP STANDARD: Branch-Specific Invoice Numbering
            // Uses branch.purchase_counter (locked for update to prevent race conditions)
            // Format: P-INV-0001, P-INV-0002, P-INV-0003, etc.
            // Each branch maintains independent sequence
            // ✅ ERP STANDARD: Use authenticated user's branch as default (or super admin's selected branch)
            $branchId = (int)($validated['branch_id'] ?? Auth::user()->branch_id ?? 1);
            
            $invoiceNo = null;
            try {
                if ($branchId) {
                    // Lock the branch row to prevent concurrent counter updates
                    $branch = Branch::lockForUpdate()->find($branchId);
                    if ($branch) {
                        // Increment and save the purchase counter
                        $branch->purchase_counter = ((int)($branch->purchase_counter ?? 0)) + 1;
                        $branch->save();
                        $invoiceNo = 'P-INV-' . str_pad($branch->purchase_counter, 4, '0', STR_PAD_LEFT);
                        \Log::info('Generated purchase invoice for branch', [
                            'branch_id' => $branchId,
                            'invoice_no' => $invoiceNo,
                            'counter' => $branch->purchase_counter
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Failed to generate branch purchase counter', [
                    'branch_id' => $branchId,
                    'error' => $e->getMessage()
                ]);
            }
            
            // Fallback if something went wrong (rare)
            if (!$invoiceNo) {
                $maxPurchaseId = Purchase::where('branch_id', $branchId)->max('id') ?? 0;
                $invoiceNo = 'P-INV-' . str_pad($maxPurchaseId + 1, 4, '0', STR_PAD_LEFT);
                \Log::warning('Using fallback purchase invoice', ['invoice_no' => $invoiceNo]);
            }
            
            // ✅ CHANGED: warehouse_id now optional (each line specifies its own warehouse)
            $warehouseId = (int)($validated['warehouse_id'] ?? 0);  // May be 0 if not selected

            // create header
            $purchase = Purchase::create([
                'branch_id'     => $branchId,
                'warehouse_id'  => $warehouseId,  // May be 0 - per-line warehouses take precedence
                'vendor_id'     => $validated['vendor_id'] ?? null,
                'purchase_date' => $validated['purchase_date'] ?? now(),
                'invoice_no'    => $invoiceNo, // ✅ Always use the calculated ERP invoice number
                'note'          => $validated['note'] ?? null,
                'subtotal'      => 0,
                'discount'      => 0,
                'extra_cost'    => 0,
                'net_amount'    => 0,
                'paid_amount'   => 0,
                'due_amount'    => 0,
            ]);

            $subtotal = 0;
            $pids = $validated['product_id'] ?? [];
            $qtys = $validated['qty'] ?? [];
            $prices = $validated['price'] ?? [];
            $units = $validated['unit'] ?? [];
            $itemDiscs = $validated['item_discount'] ?? [];
            $lineWarehouseIds = $validated['line_warehouse_id'] ?? [];  // ✅ NEW: Per-line warehouse

            foreach ($pids as $i => $pid) {
                $pid = (int)($pid ?? 0);
                $qty = (float)($qtys[$i] ?? 0);
                $price = (float)($prices[$i] ?? 0);
                if (!$pid || $qty <= 0 || $price < 0) continue;

                $disc = (float)($itemDiscs[$i] ?? 0);
                $unit = $units[$i] ?? null;
                $lineTotal = ($price * $qty) - $disc;
                
                // ✅ ERP STANDARD: Per-line warehouse assignment
                // Use line warehouse if specified, otherwise use purchase header warehouse
                $lineWarehouse = (int)($lineWarehouseIds[$i] ?? 0);
                $itemWarehouse = ($lineWarehouse > 0) ? $lineWarehouse : $warehouseId;

                PurchaseItem::create([
                    'purchase_id'   => $purchase->id,
                    'product_id'    => $pid,
                    'warehouse_id'  => $itemWarehouse,  // ✅ NEW: Store per-line warehouse
                    'unit'          => $unit,
                    'price'         => $price,
                    'item_discount' => $disc,
                    'qty'           => $qty,
                    'line_total'    => $lineTotal,
                ]);

                $subtotal += $lineTotal;

                // ⚠️ IMPORTANT: Stock update DISABLED - ERP STANDARD WORKFLOW
                // Stock is NOT updated here. Instead, it's updated when Warehouse Incharge
                // confirms receipt via InwardGatepass.store() → upsertStocks()
                // This achieves proper separation: Purchase = Order, InwardGatepass = Receipt
                // See purchase-inward-gatepass-workflow.md for details
                // 
                // DISABLED LINE (was causing duplicate stock updates):
                // $this->addPurchaseStock($pid, $qty, $branchId, $warehouseId);
            }

            // totals
            $discount  = (float)($request->discount ?? 0);
            $extraCost = (float)($request->extra_cost ?? 0);
            $netAmount = ($subtotal - $discount) + $extraCost;

            // ✅ Payment handling
            $paymentType = $validated['payment_type'] ?? 'pay_later';
            $paymentAccountId = $validated['payment_account_id'] ?? null;
            $paymentAmount = (float)($validated['payment_amount'] ?? 0);
            
            // ⚠️ IMPORTANT: Do NOT default payment to full amount
            // User must explicitly specify payment amount (supports partial payments)
            // If user doesn't input amount and selects pay_now, require explicit input
            if ($paymentType === 'pay_now' && $paymentAmount <= 0) {
                return back()->with('error', 'When paying now, please enter the payment amount. Partial payments are supported.');
            }

            // Update purchase with payment info
            $paidAmount = ($paymentType === 'pay_now') ? $paymentAmount : 0;
            $dueAmount = $netAmount - $paidAmount;

            // ✅ Validation: Can't pay more than purchase amount
            if ($paidAmount > $netAmount) {
                return back()->with('error', "Payment amount ({$paidAmount}) cannot exceed purchase amount ({$netAmount}).");
            }

            $purchase->update([
                'subtotal'    => $subtotal,
                'discount'    => $discount,
                'extra_cost'  => $extraCost,
                'net_amount'  => $netAmount,
                'paid_amount' => $paidAmount,
                'due_amount'  => $dueAmount,
            ]);

            // ✅ PAYMENT LOGIC - ERP Standard (Double-Entry Bookkeeping)
            
            // ✅ VENDOR LEDGER LOGIC - International Accounting Standard (IFRS/IAS)
            // Each transaction creates a NEW ledger entry (not overwrite with updateOrCreate)
            
            $vendorId = $validated['vendor_id'] ?? null;
            
            // Get vendor's current outstanding balance BEFORE this purchase
            $vendorCurrentBalance = VendorLedger::where('vendor_id', $vendorId)
                ->orderBy('created_at', 'desc')
                ->first()?->closing_balance ?? 0;

            // ===== STEP 1: Record PURCHASE in Vendor Ledger =====
            // Purchase INCREASES what we owe the vendor (liability/accounts payable)
            VendorLedger::create([
                'vendor_id'        => $vendorId,
                'admin_or_user_id' => auth()->id(),
                'opening_balance'  => $vendorCurrentBalance,
                'previous_balance' => $vendorCurrentBalance,
                'closing_balance'  => $vendorCurrentBalance + $netAmount, // ✅ INCREASE after purchase
            ]);

            // Step 2: If paying now, create payment transaction
            if ($paymentType === 'pay_now' && $paymentAccountId && $paidAmount > 0) {
                // ===== PAY NOW: Create Payment Voucher & Update Account =====
                
                // 2a. Debit source account (Bank/Cash) - REDUCE balance
                $sourceAccount = \App\Models\Account::find($paymentAccountId);
                if ($sourceAccount) {
                    $sourceAccount->opening_balance = $sourceAccount->opening_balance - $paidAmount;
                    $sourceAccount->save();
                }

                // 2b. Create Payment Voucher record (for audit trail & reporting)
                $pvid = \App\Models\PaymentVoucher::generateInvoiceNo(); // PVID-001, PVID-002, etc.
                
                \App\Models\PaymentVoucher::create([
                    'pvid'                => $pvid,
                    'receipt_date'        => now()->format('Y-m-d'),
                    'entry_date'          => now()->format('Y-m-d'),
                    'type'                => 'vendor',
                    'party_id'            => $validated['vendor_id'] ?? null,
                    'tel'                 => $request->tel ?? null,
                    'remarks'             => 'Payment for Purchase Invoice: ' . $purchase->invoice_no,
                    'narration_id'        => json_encode(['1']), // Default narration
                    'reference_no'        => json_encode([$purchase->invoice_no]),
                    'row_account_head'    => json_encode([$sourceAccount->head_id ?? 1]),
                    'row_account_id'      => json_encode([$paymentAccountId]),
                    'discount_value'      => json_encode([0]),
                    'amount'              => json_encode([$paidAmount]),
                    'total_amount'        => $paidAmount,
                ]);

                // 2c. Record PAYMENT in Vendor Ledger (Accounts Payable decreases)
                // Get the latest vendor balance AFTER the purchase entry
                $latestVendorBalance = VendorLedger::where('vendor_id', $vendorId)
                    ->orderBy('created_at', 'desc')
                    ->first()?->closing_balance ?? 0;

                // Payment reduces the outstanding balance
                VendorLedger::create([
                    'vendor_id'        => $vendorId,
                    'admin_or_user_id' => auth()->id(),
                    'opening_balance'  => $latestVendorBalance,
                    'previous_balance' => $latestVendorBalance,
                    'closing_balance'  => $latestVendorBalance - $paidAmount, // ✅ DECREASE after payment
                ]);
            }

            // link gatepass -> purchase (and keep status)
            if ($gatepass) {
                $gatepass->purchase_id = $purchase->id;
                $gatepass->status = 'linked';
                $gatepass->save();
            }
        });

        return redirect()->route('Purchase.home')->with('success', 'Purchase saved successfully.');
    }







    // public function store(Request $request)
    // {
    //     // ✅ Validation
    //     $validated = $request->validate([
    //         'invoice_no'     => 'nullable|string',
    //         'vendor_id'      => 'nullable|exists:vendors,id',
    //         'purchase_date'  => 'nullable|date',
    //         'warehouse_id'   => 'nullable|exists:warehouses,id',
    //         'note'           => 'nullable|string',
    //         'discount'       => 'nullable|numeric|min:0',
    //         'extra_cost'     => 'nullable|numeric|min:0',

    //         // Purchase Items
    //         'product_id'       => 'nullable|array',
    //         'product_id.*'     => 'nullable|exists:products,id',
    //         'qty'              => 'nullable|array',
    //         'qty.*'            => 'nullable|numeric|min:1',
    //         'price'            => 'nullable|array',
    //         'price.*'          => 'nullable|numeric|min:0',
    //         'unit'             => 'nullable|array',
    //         'unit.*'           => 'nullable|string',
    //         'item_discount'    => 'nullable|array',
    //         'item_discount.*'  => 'nullable|numeric|min:0',
    //     ]);

    //     DB::transaction(function () use ($validated, $request) {

    //         // 🧾 Generate Next Invoice No
    //         $lastInvoice = Purchase::latest()->value('invoice_no');
    //         $nextInvoice = $lastInvoice
    //             ? 'INV-' . str_pad(((int) filter_var($lastInvoice, FILTER_SANITIZE_NUMBER_INT)) + 1, 5, '0', STR_PAD_LEFT)
    //             : 'INV-00001';

    //         // ✍️ Create Purchase with temporary values
    //         $purchase = Purchase::create([
    //             'branch_id'     => auth()->user()->id,
    //             'warehouse_id'  => $validated['warehouse_id'],
    //             'vendor_id'     => $validated['vendor_id'] ?? null,
    //             'purchase_date' => $validated['purchase_date'] ?? now(),
    //             'invoice_no'    => $validated['invoice_no'] ?? $nextInvoice,
    //             'note'          => $validated['note'] ?? null,
    //             'subtotal'      => 0,
    //             'discount'      => 0,
    //             'extra_cost'    => 0,
    //             'net_amount'    => 0,
    //             'paid_amount'   => 0,
    //             'due_amount'    => 0,
    //         ]);

    //         $subtotal = 0;

    //         // 🧾 Purchase Items
    //         $productIds = $validated['product_id'] ?? [];
    //         foreach ($productIds as $index => $productId) {
    //             $qty   = $validated['qty'][$index] ?? null;
    //             $price = $validated['price'][$index] ?? null;

    //             if (empty($productId) || empty($qty) || empty($price)) {
    //                 continue;
    //             }

    //             $disc = $validated['item_discount'][$index] ?? 0; // ✅ Correct name
    //             $unit = $validated['unit'][$index] ?? null;

    //             $lineTotal = ($price * $qty) - $disc;

    //             PurchaseItem::create([
    //                 'purchase_id'   => $purchase->id,
    //                 'product_id'    => $productId,
    //                 'unit'          => $unit,
    //                 'price'         => $price,
    //                 'item_discount' => $disc,
    //                 'qty'           => $qty,
    //                 'line_total'    => $lineTotal,
    //             ]);

    //             $subtotal += $lineTotal;

    //             // 📦 Update Stock
    //             $stock = Stock::where('branch_id', auth()->user()->id)
    //                 ->where('warehouse_id', $validated['warehouse_id'])
    //                 ->where('product_id', $productId)
    //                 ->first();

    //             if ($stock) {
    //                 $stock->qty += $qty;
    //                 $stock->save();
    //             } else {
    //                 Stock::create([
    //                     'branch_id'     => auth()->user()->id,
    //                     'warehouse_id'  => $validated['warehouse_id'],
    //                     'product_id'    => $productId,
    //                     'qty'           => $qty,
    //                 ]);
    //             }
    //         }

    //         // 💵 Final Calculations (use values from request safely)
    //         $discount   = $request->discount ?? 0;
    //         $extraCost  = $request->extra_cost ?? 0;
    //         $netAmount  = ($subtotal - $discount) + $extraCost;

    //         $purchase->update([
    //             'subtotal'    => $subtotal,
    //             'discount'    => $discount,
    //             'extra_cost'  => $extraCost,
    //             'net_amount'  => $netAmount,
    //             'due_amount'  => $netAmount,
    //         ]);

    //         // 📘 Vendor Ledger Update
    //         $previousBalance = VendorLedger::where('vendor_id', $validated['vendor_id'])
    //             ->value('closing_balance') ?? 0;

    //         $newClosingBalance = $previousBalance + $netAmount;

    //         VendorLedger::updateOrCreate(
    //             ['vendor_id' => $validated['vendor_id']],
    //             [
    //                 'vendor_id'         => $validated['vendor_id'],
    //                 'admin_or_user_id'  => auth()->id(),
    //                 'previous_balance'  => $subtotal,
    //                 'closing_balance'   => $newClosingBalance,
    //             ]
    //         );
    //     });

    //     return back()->with('success', 'Purchase saved successfully!');
    // }


    // public function store(Request $request)
    // {

    //         $validated = $request->validate([
    //             'invoice_no'     => 'nullable|string',
    //             'vendor_id'      => 'nullable|exists:vendors,id',
    //             // 'branch_id'      => 'required|exists:branches,id',
    //             'purchase_date'  => 'nullable|date',
    //             'warehouse_id'   => 'nullable|exists:warehouses,id',
    //             'note'           => 'nullable|string',
    //     'discount'       => 'nullable|numeric|min:0',
    //     'extra_cost'     => 'nullable|numeric|min:0',

    //             // Purchase Items
    //             'product_id'     => 'nullable|array',
    //             'product_id.*'   => 'nullable|exists:products,id',
    //             'qty'            => 'nullable|array',
    //             'qty.*'          => 'nullable|numeric|min:1',
    //             'price'          => 'nullable|array',
    //             'price.*'        => 'nullable|numeric|min:0',
    //             'unit'           => 'nullable|array',
    //             'unit.*'         => 'nullable|string',
    //             'item_discount'  => 'nullable|array',
    //             'item_discount.*'=> 'nullable|numeric|min:0',
    //         ]);
    // DB::transaction(function () use ($validated) {

    //     $lastInvoice = Purchase::latest()->value('invoice_no');

    //     $nextInvoice = $lastInvoice
    //         ? 'INV-' . str_pad(((int) filter_var($lastInvoice, FILTER_SANITIZE_NUMBER_INT)) + 1, 5, '0', STR_PAD_LEFT)
    //         : 'INV-00001';

    //     // 1️⃣ Create purchase
    //     $purchase = Purchase::create([
    //         'branch_id'     => Auth()->user()->id,
    //         'warehouse_id'  => $validated['warehouse_id'],
    //         'vendor_id'     => $validated['vendor_id'] ?? null,
    //         'purchase_date' => $validated['purchase_date'] ?? now(),
    //         'invoice_no'    => $validated['invoice_no'] ?? $nextInvoice,
    //         'note'          => $validated['note'] ?? null,
    //         'subtotal'      => $validated['subtotal'] ?? 0,
    //         'discount'      => $validated['discount'] ?? 0,
    //         'extra_cost'    => $validated['extra_cost'] ?? 0,
    //         'net_amount'    => $validated['net_amount'] ?? 0,
    //         'paid_amount'   => 0,
    //         'due_amount'    => 0,

    //     ]);

    //     $subtotal = 0;

    //     // 2️⃣ Loop & filter rows
    //     $productIds = $validated['product_id'] ?? [];
    //     foreach ($productIds as $index => $productId) {
    //         $qty   = $validated['qty'][$index] ?? null;
    //         $price = $validated['price'][$index] ?? null;

    //         // Skip row if any essential field is empty
    //         if (empty($productId) || empty($qty) || empty($price)) {
    //             continue;
    //         }

    //         $disc = $validated['item_disc'][$index] ?? 0;
    //         $unit = $validated['unit'][$index] ?? null;

    //         $lineTotal = ($price * $qty) - $disc;

    //         // Save item
    //         PurchaseItem::create([
    //             'purchase_id'   => $purchase->id,
    //             'product_id'    => $productId,
    //             'unit'          => $unit,
    //             'price'         => $price,
    //             'item_discount' => $disc,
    //             'qty'           => $qty,
    //             'line_total'    => $lineTotal,
    //         ]);

    //         $subtotal += $lineTotal;

    //         // 3️⃣ Update stock
    //         $stock = Stock::where('branch_id', Auth()->user()->id)
    //             ->where('warehouse_id', $validated['warehouse_id'])
    //             ->where('product_id', $productId)
    //             ->first();

    //         if ($stock) {
    //             $stock->qty += $qty;
    //             $stock->save();
    //         } else {
    //             Stock::create([
    //                 'branch_id'     => Auth()->user()->id,
    //                 'warehouse_id'  => $validated['warehouse_id'],
    //                 'product_id'    => $productId,
    //                 'qty'           => $qty,
    //             ]);
    //         }
    //     }

    //     // 4️⃣ Update totals
    //     $purchase->update([
    //         'subtotal'    => $subtotal,
    //         'net_amount'  => $subtotal,
    //         'due_amount'  => $subtotal,
    //     ]);

    //     // 5️⃣ Vendor ledger
    //     $previousBalance = VendorLedger::where('vendor_id', $validated['vendor_id'])
    //         ->value('closing_balance') ?? 0;

    //     $newClosingBalance = $previousBalance + $subtotal;

    //     VendorLedger::updateOrCreate(
    //         ['vendor_id' => $validated['vendor_id']],
    //         [
    //             'vendor_id' => $validated['vendor_id'],
    //             'admin_or_user_id' => Auth::id(),
    //             'previous_balance' => $subtotal,
    //             'closing_balance' => $newClosingBalance,
    //         ]
    //     );

    // });

    // // DB::transaction(function () use ($validated) {

    // // $lastInvoice = Purchase::latest()->value('invoice_no');

    // // // Agar last invoice mila to +1 karo, warna start karo INV-00001
    // // $nextInvoice = $lastInvoice
    // //     ? 'INV-' . str_pad(((int) filter_var($lastInvoice, FILTER_SANITIZE_NUMBER_INT)) + 1, 5, '0', STR_PAD_LEFT)
    // //     : 'INV-00001';

    // //     // 1️⃣ Save main Purchase
    // //     $purchase = Purchase::create([

    // //         'branch_id'     => Auth()->user()->id,
    // //         'warehouse_id'  => $validated['warehouse_id'],
    // //         'vendor_id'     => $validated['vendor_id'] ?? null,
    // //         'purchase_date' => $validated['purchase_date'] ?? now(),
    // //         'invoice_no'    => $validated['invoice_no'] ?? $nextInvoice,
    // //         'note'          => $validated['note'] ?? null,
    // //         'subtotal'      => 0,
    // //         'discount'      => 0,
    // //         'extra_cost'    => 0,
    // //         'net_amount'    => 0,
    // //         'paid_amount'   => 0,
    // //         'due_amount'    => 0,
    // //     ]);

    // //     $subtotal = 0;

    // //     // 2️⃣ Loop purchase items
    // //     foreach ($validated['product_id'] as $index => $productId) {
    // //         $qty     = $validated['qty'][$index];
    // //         $price   = $validated['price'][$index];
    // //         $disc    = $validated['item_discount'][$index] ?? 0;
    // //         $lineTotal = ($price * $qty) - $disc;

    // //         // Save purchase item
    // //         PurchaseItem::create([
    // //             'purchase_id'   => $purchase->id,
    // //             'product_id'    => $productId,
    // //             'unit'          => $validated['unit'][$index] ?? null,
    // //             'price'         => $price,
    // //             'item_discount' => $disc,
    // //             'qty'           => $qty,
    // //             'line_total'    => $lineTotal,
    // //         ]);

    // //         $subtotal += $lineTotal;

    // //         // 3️⃣ Update stock
    // //         $stock = Stock::where('branch_id',  Auth()->user()->id,)
    // //             ->where('warehouse_id', $validated['warehouse_id'])
    // //             ->where('product_id', $productId)
    // //             ->first();

    // //         if ($stock) {
    // //             $stock->qty += $qty;
    // //             $stock->save();
    // //         } else {
    // //             Stock::create([
    // //                 'branch_id'     => Auth()->user()->id,
    // //                 'warehouse_id'  => $validated['warehouse_id'],
    // //                 'product_id'    => $productId,
    // //                 'qty'           => $qty,
    // //             ]);
    // //         }
    // //     }

    // //     // 4️⃣ Update totals in purchase
    // //     $purchase->update([
    // //         'subtotal'    => $subtotal,
    // //         'net_amount'  => $subtotal,
    // //         'due_amount'  => $subtotal,
    // //     ]);

    // //     $previousBalance = VendorLedger::where('vendor_id', $validated['vendor_id'])
    // //         ->value('closing_balance') ?? 0; // If no previous balance, start from 0
    // //     // Calculate new balances

    // //     $newPreviousBalance = $subtotal;

    // //     $newClosingBalance = $previousBalance + $subtotal;
    // //     $userId = Auth::id();

    // //     // Update or create distributor ledger
    // //     VendorLedger::updateOrCreate(
    // //         ['vendor_id' => $validated['vendor_id']],
    // //         [
    // //             'vendor_id' => $validated['vendor_id'],
    // //             'admin_or_user_id' => $userId,
    // //             'previous_balance' => $newPreviousBalance,
    // //             'closing_balance' => $newClosingBalance,
    // //         ]
    // //     );

    // });

    //     return redirect()->back()->with('success', 'Purchase saved successfully!');
    // }


    public function edit($id)
    {
        $purchase = Purchase::with(['items.product', 'branch'])->findOrFail($id);
        $Vendor = Vendor::all();
        
        // ✅ Check if user is super admin
        $isSuperAdmin = Auth::user() && Auth::user()->hasRole('super admin');
        
        // ✅ ERP STANDARD: Fetch branches for dropdown
        $Branch = Branch::orderBy('name')->get();
        
        if ($isSuperAdmin) {
            $Warehouse = Warehouse::all();
        } else {
            $allowedBranchIds = $this->allowedBranches('purchase.view');
            $Warehouse = Warehouse::whereHas('branches', function ($q) use ($allowedBranchIds) {
                $q->whereIn('branches.id', $allowedBranchIds);
            })->get();
        }
        
        // ✅ CRITICAL: Fetch vendor_remaining for delivery tracking
        // Shows what's been received vs what's still pending
        $vendorRemaining = \App\Models\VendorRemaining::where('purchase_id', $purchase->id)
            ->get()
            ->keyBy('product_id');  // Index by product_id for easy lookup

        return view('admin_panel.purchase.edit', compact('purchase', 'Vendor', 'Warehouse', 'Branch', 'vendorRemaining', 'isSuperAdmin'));
    }



    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'invoice_no'      => 'nullable|string',
            'vendor_id'       => 'nullable|exists:vendors,id',
            'purchase_date'   => 'nullable|date',
            'branch_id'       => 'nullable|exists:branches,id',
            'warehouse_id'    => 'nullable|exists:warehouses,id',  // ✅ Now optional (per-line)
            'note'            => 'nullable|string',
            'transport_name'  => 'nullable|string',  // ✅ NEW: Transport field
            'discount'        => 'nullable|numeric|min:0',
            'extra_cost'      => 'nullable|numeric|min:0',

            'product_id'      => 'array',
            'product_id.*'    => 'nullable|exists:products,id',
            'qty'             => 'array',
            'qty.*'           => 'nullable|required_with:product_id.*|numeric|min:1',
            'price'           => 'array',
            'price.*'         => 'nullable|required_with:product_id.*|numeric|min:0',
            'unit'            => 'array',
            'unit.*'          => 'nullable|required_with:product_id.*|string',
            'item_discount'   => 'nullable|array',
            'item_discount.*' => 'nullable|numeric|min:0',
            
            // ✅ ERP STANDARD: Per-line warehouse assignment (REQUIRED)
            'line_warehouse_id'   => 'required|array',
            'line_warehouse_id.*' => 'required|exists:warehouses,id',
        ]);

        DB::transaction(function () use ($validated, $request, $id) {
            $purchase = Purchase::with('items')->findOrFail($id);

            $branchId    = (int)($validated['branch_id'] ?? $purchase->branch_id ?? 1);
            $warehouseId = (int)($validated['warehouse_id'] ?? $purchase->warehouse_id);

            // ✅ CRITICAL: If purchase is linked to gatepass, validate qty restrictions
            // User cannot decrease qty below what's already been received
            $isLinkedToGatepass = \App\Models\InwardGatepass::where('purchase_id', $purchase->id)->exists();
            $vendorRemainings = collect();
            
            if ($isLinkedToGatepass) {
                $vendorRemainings = \App\Models\VendorRemaining::where('purchase_id', $purchase->id)
                    ->get()
                    ->keyBy('product_id');
            }

            // Map old totals per product
            $oldMap = $purchase->items->groupBy('product_id')->map(fn($g) => (float)$g->sum('qty'));

            // Rebuild items
            $purchase->items()->delete();

            $subtotal = 0;
            $newMap = collect();

            $pids = $validated['product_id'] ?? [];
            $qtys = $validated['qty'] ?? [];
            $prices = $validated['price'] ?? [];
            $units = $validated['unit'] ?? [];
            $itemDiscs = $validated['item_discount'] ?? [];
            $lineWarehouseIds = $validated['line_warehouse_id'] ?? [];  // ✅ NEW: Per-line warehouse

            foreach ($pids as $i => $pid) {
                $pid = (int)($pid ?? 0);
                $qty = (float)($qtys[$i] ?? 0);
                $price = (float)($prices[$i] ?? 0);
                if (!$pid || $qty <= 0 || $price < 0) continue;

                // ✅ ERP STANDARD: Validate qty against received qty
                if ($isLinkedToGatepass && $vendorRemainings->has($pid)) {
                    $remaining = $vendorRemainings[$pid];
                    // New qty cannot be less than what's already received
                    if ($qty < $remaining->received_qty) {
                        \Log::warning('Purchase edit: Qty reduction blocked', [
                            'product_id' => $pid,
                            'new_qty' => $qty,
                            'already_received' => $remaining->received_qty
                        ]);
                        return; // Skip this product or throw validation error
                    }
                }

                $disc = (float)($itemDiscs[$i] ?? 0);
                $unit = $units[$i] ?? null;
                $lineTotal = ($price * $qty) - $disc;
                
                // ✅ ERP STANDARD: Per-line warehouse assignment
                $lineWarehouse = (int)($lineWarehouseIds[$i] ?? 0);
                $itemWarehouse = ($lineWarehouse > 0) ? $lineWarehouse : $warehouseId;

                PurchaseItem::create([
                    'purchase_id'   => $purchase->id,
                    'product_id'    => $pid,
                    'warehouse_id'  => $itemWarehouse,  // ✅ NEW: Store per-line warehouse
                    'unit'          => $unit,
                    'price'         => $price,
                    'item_discount' => $disc,
                    'qty'           => $qty,
                    'line_total'    => $lineTotal,
                ]);

                $subtotal += $lineTotal;
                $newMap[$pid] = ($newMap[$pid] ?? 0) + $qty;
            }

            // header update
            $purchase->update([
                'vendor_id'     => $validated['vendor_id'] ?? $purchase->vendor_id,
                'branch_id'     => $branchId,
                'warehouse_id'  => $warehouseId,
                'purchase_date' => $validated['purchase_date'] ?? $purchase->purchase_date,
                'invoice_no'    => $validated['invoice_no'] ?? $purchase->invoice_no,
                'note'          => $validated['note'] ?? $purchase->note,
                'transport_name'=> $validated['transport_name'] ?? $purchase->transport_name,  // ✅ NEW
            ]);

            // totals
            $discount  = (float)($request->discount ?? 0);
            $extraCost = (float)($request->extra_cost ?? 0);
            $netAmount = ($subtotal - $discount) + $extraCost;

            $purchase->update([
                'subtotal'    => $subtotal,
                'discount'    => $discount,
                'extra_cost'  => $extraCost,
                'net_amount'  => $netAmount,
                'due_amount'  => $netAmount,
            ]);

            // ✅ CRITICAL: Update vendor_remaining if qty changed
            // When purchase qty changes, remaining_qty must be recalculated
            $vendorRemainingsForUpdate = \App\Models\VendorRemaining::where('purchase_id', $purchase->id)->get();
            foreach ($vendorRemainingsForUpdate as $vr) {
                $pid = (int)$vr->product_id;
                $newQty = (int)($newMap[$pid] ?? 0);
                $receivedQty = (int)$vr->received_qty;
                
                if ($newQty !== (int)$vr->ordered_qty) {
                    // Qty changed! Update ordered_qty and recalculate remaining_qty
                    $newRemaining = $newQty - $receivedQty;
                    
                    $vr->update([
                        'ordered_qty'  => $newQty,
                        'remaining_qty' => max(0, $newRemaining),  // Never negative
                        // ✅ Auto-update status based on new qty
                        'status' => ($newRemaining <= 0) ? 'completed' : (($receivedQty > 0) ? 'partial' : 'pending'),
                    ]);
                    
                    \Log::info('Purchase edit: Updated vendor_remaining', [
                        'product_id' => $pid,
                        'old_ordered_qty' => $vr->ordered_qty,
                        'new_ordered_qty' => $newQty,
                        'received_qty' => $receivedQty,
                        'new_remaining' => $newRemaining,
                    ]);
                }
            }

            // If this purchase is linked to a gatepass => NO stock changes here
            $isLinkedToGatepass = \App\Models\InwardGatepass::where('purchase_id', $purchase->id)->exists();

            if (!$isLinkedToGatepass) {
                // deltas for movements + stocks
                $movs = [];
                $now = now();
                $all = $oldMap->keys()->merge($newMap->keys())->unique();
                foreach ($all as $pid) {
                    $oldQ = (float)($oldMap[$pid] ?? 0);
                    $newQ = (float)($newMap[$pid] ?? 0);
                    $delta = $newQ - $oldQ;
                    if ($delta == 0) continue;

                    $type = $delta > 0 ? 'in' : 'out';
                    $qty  = abs($delta);

                    $movs[] = [
                        'product_id' => (int)$pid,
                        'type'       => $type,
                        'qty'        => $qty,
                        'ref_type'   => 'PURCHASE_EDIT',
                        'ref_id'     => $purchase->id,
                        'note'       => 'Purchase edit delta',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    $this->upsertStocks((int)$pid, ($type === 'in' ? +$qty : -$qty), $branchId, $warehouseId);
                }
                if (!empty($movs)) {
                    DB::table('stock_movements')->insert($movs);
                }
            }

            // Vendor ledger (simple overwrite pattern)
            $prevClosing = \App\Models\VendorLedger::where('vendor_id', $purchase->vendor_id)
                ->value('closing_balance') ?? 0;
            \App\Models\VendorLedger::updateOrCreate(
                ['vendor_id' => $purchase->vendor_id],
                [
                    'vendor_id'         => $purchase->vendor_id,
                    'admin_or_user_id'  => auth()->id(),
                    'previous_balance'  => $prevClosing,
                    'opening_balance'   => $prevClosing,
                    'closing_balance'   => $prevClosing + $netAmount,
                ]
            );
        });

        return redirect()->route('Purchase.home')->with('success', 'Purchase updated successfully!');
    }



    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $purchase = Purchase::with('items')->findOrFail($id);

            $branchId    = (int)($purchase->branch_id ?? 1);
            $warehouseId = (int)($purchase->warehouse_id);

            // linked to gatepass? then NO stock changes
            $isLinkedToGatepass = \App\Models\InwardGatepass::where('purchase_id', $purchase->id)->exists();

            if (!$isLinkedToGatepass) {
                $movs = [];
                $now = now();

                foreach ($purchase->items as $it) {
                    $pid = (int)$it->product_id;
                    $qty = (float)$it->qty;

                    $movs[] = [
                        'product_id' => $pid,
                        'type'       => 'out',
                        'qty'        => $qty,
                        'ref_type'   => 'PURCHASE_DELETE',
                        'ref_id'     => $purchase->id,
                        'note'       => 'Delete purchase (reverse)',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    // stocks rollback
                    $this->upsertStocks($pid, -$qty, $branchId, $warehouseId);
                }

                if (!empty($movs)) {
                    DB::table('stock_movements')->insert($movs);
                }
            }

            $purchase->items()->delete();
            $purchase->delete();
        });

        return redirect()->back()->with('success', 'Purchase deleted successfully.');
    }



    public function Invoice($id)
    {
        // ✅ Load branch relationship to display login branch in invoice header
        $purchase   = Purchase::with(['items.product', 'branch', 'vendor', 'warehouse'])->findOrFail($id);
        $Vendor     = Vendor::all();
        if (Auth::check() && Auth::user()->hasRole('super admin')) {
            $Warehouse = Warehouse::all();
        } else {
            $allowedBranchIds = $this->allowedBranches('purchase.view');
            $Warehouse = Warehouse::whereHas('branches', function ($q) use ($allowedBranchIds) {
                $q->whereIn('branches.id', $allowedBranchIds);
            })->get();
        }

        return view('admin_panel.purchase.Invoice', compact('purchase', 'Vendor', 'Warehouse'));
    }





    // purchase_reutun



    public function showReturnForm($id)
    {
        $purchase = Purchase::with(['vendor', 'warehouse', 'items.product'])->findOrFail($id);
        $Vendor = \App\Models\Vendor::all();
        if (Auth::check() && Auth::user()->hasRole('super admin')) {
            $Warehouse = \App\Models\Warehouse::all();
        } else {
            $allowedBranchIds = $this->allowedBranches('purchase.view');
            $Warehouse = \App\Models\Warehouse::whereHas('branches', function ($q) use ($allowedBranchIds) {
                $q->whereIn('branches.id', $allowedBranchIds);
            })->get();
        }

        return view('admin_panel.purchase.purchase_return.create', compact('purchase', 'Vendor', 'Warehouse'));
    }

    // store return
    public function storeReturn(Request $request)
    {
        $validated = $request->validate([
            'vendor_id'        => 'required|exists:vendors,id',
            'warehouse_id'     => 'required|exists:warehouses,id',
            'return_date'      => 'required|date',
            'return_reason'    => 'nullable|string|max:255',
            'remarks'          => 'nullable|string',
            'product_id'       => 'required|array',
            'product_id.*'     => 'required|exists:products,id',
            'qty'              => 'required|array',
            'qty.*'            => 'required|numeric|min:1',
            'price'            => 'required|array',
            'price.*'          => 'required|numeric|min:0',
            'unit'             => 'required|array',
            'unit.*'           => 'required|string',
            'item_disc'        => 'nullable|array',
            'item_disc.*'      => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            // Generate Return Invoice #
            $lastReturn = \App\Models\PurchaseReturn::latest()->first();
            $nextInvoice = 'RTN-' . str_pad(optional($lastReturn)->id + 1 ?? 1, 5, '0', STR_PAD_LEFT);

            // Create main return record
            $return = \App\Models\PurchaseReturn::create([
                'vendor_id'     => $validated['vendor_id'],
                'warehouse_id'  => $validated['warehouse_id'],
                'return_invoice' => $nextInvoice,
                'return_date'   => $validated['return_date'],
                'return_reason' => $validated['return_reason'] ?? null,
                'bill_amount'   => 0, // calculated below
                'item_discount' => 0,
                'extra_discount' => 0,
                'net_amount'    => 0,
                'paid'          => 0,
                'balance'       => 0,
                'remarks'       => $validated['remarks'] ?? null,
            ]);

            $subtotal = 0;

            foreach ($validated['product_id'] as $index => $productId) {
                $qty   = $validated['qty'][$index];
                $price = $validated['price'][$index];
                $disc  = $validated['item_disc'][$index] ?? 0;
                $unit  = $validated['unit'][$index];
                $lineTotal = ($price * $qty) - $disc;

                \App\Models\PurchaseReturnItem::create([
                    'purchase_return_id' => $return->id,
                    'product_id'         => $productId,
                    'qty'                => $qty,
                    'price'              => $price,
                    'item_discount'      => $disc,
                    'unit'               => $unit,
                    'line_total'         => $lineTotal,
                ]);

                // Update stock (deduct)
                $stock = \App\Models\Stock::where('branch_id', auth()->id())
                    ->where('warehouse_id', $validated['warehouse_id'])
                    ->where('product_id', $productId)
                    ->first();

                if ($stock) {
                    $stock->qty -= $qty;
                    $stock->save();
                }

                $subtotal += $lineTotal;
            }

            $discount    = $validated['item_disc'] ? array_sum($validated['item_disc']) : 0;
            $extraDisc   = $request->extra_discount ?? 0;
            $netAmount   = ($subtotal - $discount) - $extraDisc;

            $return->update([
                'bill_amount'   => $subtotal,
                'item_discount' => $discount,
                'extra_discount' => $extraDisc,
                'net_amount'    => $netAmount,
                'balance'       => $netAmount,
            ]);

            // Update Vendor Ledger (subtract amount)
            $ledger = \App\Models\VendorLedger::where('vendor_id', $validated['vendor_id'])->first();
            $openingBalance = $ledger ? $ledger->closing_balance : 0;
            $closingBalance = $openingBalance - $netAmount;

            \App\Models\VendorLedger::updateOrCreate(
                ['vendor_id' => $validated['vendor_id']],
                [
                    'admin_or_user_id' => auth()->id(),
                    'opening_balance'  => $openingBalance,
                    'closing_balance'  => $closingBalance,
                    'previous_balance' => $openingBalance,
                ]
            );
        });

        return redirect()->route('purchase.return.index')->with('success', 'Purchase return successfully created.');
    }

    public function purchaseReturnIndex()
    {
        $returns = \App\Models\PurchaseReturn::with(['vendor', 'warehouse'])->latest()->get();
        return view('admin_panel.purchase.purchase_return.index', compact('returns'));
    }
}
