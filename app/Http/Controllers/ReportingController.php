<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountHead;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\Product;
use App\Models\ReceiptsVoucher;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturn;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\SalesOfficer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportingController extends Controller
{

    public function onhand(Request $request)
    {
        $user = Auth::user();
        $isSuper = $user && $user->hasRole('super admin');

        $branchId = null;
        if ($isSuper) {
            $branches = Branch::orderBy('name')->get();
            if ($request->filled('branch_id') && $request->branch_id !== 'all') {
                $branchId = (int)$request->branch_id;
            }
        } else {
            $branches = $user->branch_id ? Branch::where('id', $user->branch_id)->get() : collect();
            $branchId = $user->branch_id;
        }

        // Fetch Warehouses based on branch
        if ($branchId) {
            $warehouses = Warehouse::whereHas('branches', function($q) use ($branchId) {
                $q->where('branches.id', $branchId);
            })->orderBy('warehouse_name')->get();
        } else {
            $warehouses = Warehouse::orderBy('warehouse_name')->get();
        }

        $warehouseId = null;
        if ($request->filled('warehouse_id') && $request->warehouse_id !== 'all') {
            $warehouseId = (int)$request->warehouse_id;
        }

        $productId = null;
        if ($request->filled('product_id') && $request->product_id !== 'all') {
            $productId = (int)$request->product_id;
        }

        // Products list for searchable dropdown
        $allProducts = Product::orderBy('item_name')->get(['id', 'item_name', 'item_code']);

        // Main Query
        $query = Product::query()
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->leftJoin('units', 'units.id', '=', 'products.unit_id');

        if ($branchId || $warehouseId) {
            $query->leftJoin('warehouse_stocks as ws', function($join) use ($branchId, $warehouseId) {
                $join->on('ws.product_id', '=', 'products.id');
                if ($branchId) {
                    $join->where('ws.branch_id', '=', $branchId);
                }
                if ($warehouseId) {
                    $join->where('ws.warehouse_id', '=', $warehouseId);
                }
            })
            ->selectRaw("
                products.id,
                products.item_code,
                products.item_name,
                COALESCE(brands.name, '') as brand_name,
                COALESCE(units.name, '') as unit_name,
                COALESCE(SUM(ws.quantity), 0) as onhand_qty,
                products.is_part,
                products.is_assembled
            ")
            ->groupBy(
                'products.id',
                'products.item_code',
                'products.item_name',
                'brands.name',
                'units.name',
                'products.is_part',
                'products.is_assembled'
            );
        } else {
            $query->leftJoin('v_stock_onhand as soh', 'soh.product_id', '=', 'products.id')
            ->selectRaw("
                products.id,
                products.item_code,
                products.item_name,
                COALESCE(brands.name, '') as brand_name,
                COALESCE(units.name, '') as unit_name,
                COALESCE(soh.onhand_qty, 0) as onhand_qty,
                products.is_part,
                products.is_assembled
            ");
        }

        if ($productId) {
            $query->where('products.id', $productId);
        }

        $rows = $query->orderBy('products.item_name')->get();

        if ($request->ajax()) {
            return response()->json([
                'rows' => $rows,
                'totalItems' => $rows->count(),
                'totalQty' => $rows->sum('onhand_qty'),
                'partsCount' => $rows->where('is_part', 1)->count(),
                'assembledCount' => $rows->where('is_assembled', 1)->count(),
            ]);
        }

        return view('admin_panel.reporting.onhand', compact('rows', 'branches', 'warehouses', 'allProducts', 'isSuper', 'branchId', 'warehouseId', 'productId'));
    }
    public function customer_ledger_new(){
        $user = Auth::user();

        // Determine which branches user can view
        if ($user->hasRole('super admin')) {
            $branches = Branch::orderBy('name')->get();
        } else {
            $branches = Branch::where('id', $user->branch_id)->get();
        }

        // Get customers for non-admin users (super admin will load via AJAX)
        $customers = [];
        if (!$user->hasRole('super admin') && $user->branch_id) {
            $customers = Customer::where('branch_id', $user->branch_id)
                ->where('status', 'active')
                ->select('id', 'customer_name', 'customer_type', 'credit_limit', 'address', 'mobile', 'email_address', 'opening_balance')
                ->orderBy('customer_name')
                ->get();
        }

        $startDate = date('Y-m-01');
        $endDate   = date('Y-m-d');

        return view('admin_panel.reporting.customer_leger_new', compact('branches', 'customers', 'startDate', 'endDate'));
    }

    /* ═══════════════════════════════════════════════════════════════════════
     *  FETCH CUSTOMER LEDGER (NEW – International ERP Standard)
     *
     *  Builds a chronological ledger by DIRECTLY joining:
     *    1. Sales            → Debit  (SIN-xxxxx / invoice_no)
     *    2. Sale Returns     → Credit (return_note)
     *    3. Receipt Vouchers → Credit (BRV / cash payment)
     *    4. Payment Vouchers → Credit (discount / expense against customer)
     *  Each sale row shows ONE row per sale-item (Qty + Rate).
     *  Gate Pass & DC numbers are looked up from outward_gatepasses.
     *  Running balance is calculated in PHP to stay accurate.
     * ═══════════════════════════════════════════════════════════════════════ */
    public function fetch_customer_ledger_new(Request $request)
    {
        $user       = auth()->user();
        $customerId = (int) $request->customer_id;
        $start      = $request->start_date;          // Y-m-d
        $end        = $request->end_date;             // Y-m-d

        if (!$customerId || !$start || !$end) {
            return response()->json(['error' => 'Missing required parameters'], 422);
        }

        // ── Authorization ──────────────────────────────────────────────────
        $customer     = Customer::findOrFail($customerId);
        $custBranchId = $customer->branch_id ?? null;
        $allowed      = false;

        if ($user && $user->hasRole('super admin')) {
            $allowed = true;
        } elseif ($user && ($user->branch_id ?? null) && $custBranchId && $user->branch_id == $custBranchId) {
            $allowed = true;
        } elseif ($user && $user->can('report.customer.ledger.branch.view')) {
            $allowed = true;
        }

        if (!$allowed) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // ── Opening Balance (last ledger entry BEFORE start_date) ──────────
        $prevLedger     = CustomerLedger::where('customer_id', $customerId)
            ->where('created_at', '<', $start . ' 00:00:00')
            ->latest('created_at')
            ->first();
        $openingBalance = $prevLedger
            ? floatval($prevLedger->closing_balance)
            : floatval($customer->opening_balance ?? 0);

        // ── Gate Pass lookup map: invoice_no → {dc_no, gatepass_number} ───
        $gpMap = [];
        $gpRows = DB::table('outward_gatepasses')
            ->whereNotNull('invoice_no')
            ->select('invoice_no', 'dc_no', 'gatepass_number', 'created_at')
            ->get();
        foreach ($gpRows as $gp) {
            // Store the LATEST gatepass per invoice (there can be multiple DCs)
            $gpMap[$gp->invoice_no][] = [
                'dc_no'            => $gp->dc_no ?? '-',
                'gatepass_number'  => $gp->gatepass_number ?? '-',
            ];
        }

        // ══════════════════════════════════════════════════════════════════
        // PART 1 – SALES  (one entry per sale-item for Qty/Rate breakdown)
        // ══════════════════════════════════════════════════════════════════
        $salesRaw = DB::table('sales')
            ->join('sale_items', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products',   'products.id',        '=', 'sale_items.product_id')
            ->where('sales.customer_id', $customerId)
            ->whereBetween(DB::raw('DATE(sales.created_at)'), [$start, $end])
            ->select(
                'sales.id          as sale_id',
                'sales.invoice_no  as invoice_no',
                'sales.manual_invoice as manual_invoice',
                DB::raw('COALESCE(sales.manual_invoice, "-") as bill_no'),
                DB::raw('COALESCE(sales.total_net, sales.sub_total1 - sales.discount_amount) as total_net'),
                'sales.additional_discount as additional_discount',
                'sales.extra_charges       as extra_charges',
                'sales.created_at  as txn_date',
                'products.item_name as item_name',
                'products.price    as retail_price',
                'products.wholesale_price as n_price',
                'sale_items.product_id as product_id',
                'sale_items.sales_qty  as qty',
                'sale_items.discount_amount as item_discount',
                DB::raw('CASE WHEN sale_items.sales_price > 0 THEN sale_items.sales_price WHEN sale_items.sales_qty > 0 THEN ROUND((sale_items.amount + sale_items.discount_amount) / sale_items.sales_qty, 2) ELSE 0 END as rate'),
                'sale_items.amount  as line_amount'
            )
            ->orderBy('sales.created_at', 'asc')
            ->orderBy('sales.id', 'asc')
            ->orderBy('sale_items.id', 'asc')
            ->get();

        // Build avg purchase price map per product (total_cost / total_qty)
        $avgPriceMap = [];
        $avgPriceRaw = DB::table('purchase_items')
            ->select(
                'product_id',
                DB::raw('COALESCE(SUM(qty),0) as total_qty'),
                DB::raw('COALESCE(SUM(line_total),0) as total_cost')
            )
            ->groupBy('product_id')
            ->get();
        foreach ($avgPriceRaw as $ap) {
            $avgPriceMap[$ap->product_id] = $ap->total_qty > 0
                ? floatval($ap->total_cost) / floatval($ap->total_qty)
                : 0;
        }

        // Group sale items under their parent sale
        // For running balance: debit hits ONCE per sale (total_net), not per item
        $salesGrouped = [];
        foreach ($salesRaw as $row) {
            $salesGrouped[$row->sale_id]['header'] = [
                'invoice_no'          => $row->invoice_no,
                'manual_invoice'      => $row->manual_invoice ?? '-',
                'total_net'           => floatval($row->total_net ?? 0),
                'additional_discount' => floatval($row->additional_discount ?? 0),
                'extra_charges'       => floatval($row->extra_charges ?? 0),
                'txn_date'            => $row->txn_date,
            ];
            $qty       = floatval($row->qty ?? 0);
            $avgPrice  = $avgPriceMap[$row->product_id] ?? 0;
            $nPrice    = floatval($row->n_price ?? 0);
            $salesGrouped[$row->sale_id]['items'][] = [
                'item_name'    => $row->item_name,
                'qty'          => $qty,
                'rate'         => floatval($row->rate ?? 0),
                'item_discount'=> floatval($row->item_discount ?? 0),
                'line_amount'  => floatval($row->line_amount ?? 0),
                'retail_price' => floatval($row->retail_price ?? 0),
                'policy_price' => floatval($row->rate ?? 0),
                'avg_price'    => $avgPrice,
                'avg_s_value'  => $avgPrice * $qty,
                'n_price'      => $nPrice,
                'stock_value'  => floatval($row->retail_price ?? 0) * $qty,
            ];
        }

        // ══════════════════════════════════════════════════════════════════
        // PART 2 – SALE RETURNS  (Credit entries)
        // ══════════════════════════════════════════════════════════════════
        $returnsRaw = DB::table('sales_returns')
            ->join('sales', 'sales.id', '=', 'sales_returns.sale_id')
            ->where('sales.customer_id', $customerId)
            ->whereBetween(DB::raw('DATE(sales_returns.created_at)'), [$start, $end])
            ->select(
                'sales_returns.id         as id',
                'sales.invoice_no         as invoice_no',
                'sales_returns.return_note as return_note',
                'sales_returns.product    as item_name',
                'sales_returns.qty        as qty',
                'sales_returns.per_price  as rate',
                'sales_returns.total_net  as credit_amount',
                'sales_returns.created_at as txn_date'
            )
            ->orderBy('sales_returns.created_at', 'asc')
            ->get();

        // ══════════════════════════════════════════════════════════════════
        // PART 3 – RECEIPT VOUCHERS  (Cash + Bank payments from customer)
        // ══════════════════════════════════════════════════════════════════
        $receiptsRaw = DB::table('receipts_vouchers')
            ->leftJoin('accounts',      'accounts.id',       '=', 'receipts_vouchers.row_account_id')
            ->leftJoin('account_heads', 'account_heads.id',  '=', 'receipts_vouchers.row_account_head')
            ->leftJoin('narrations',    'narrations.id',     '=', 'receipts_vouchers.narration_id')
            ->where('receipts_vouchers.party_id', $customerId)
            ->whereBetween(DB::raw('DATE(receipts_vouchers.receipt_date)'), [$start, $end])
            ->select(
                'receipts_vouchers.id           as id',
                'receipts_vouchers.rvid         as rvid',
                'receipts_vouchers.receipt_date as txn_date',
                'receipts_vouchers.total_amount as credit_amount',
                'receipts_vouchers.reference_no as reference_no',
                'receipts_vouchers.remarks      as remarks',
                'narrations.narration           as narration_text',
                'accounts.title                 as account_title',
                'account_heads.name             as head_name'
            )
            ->orderBy('receipts_vouchers.receipt_date', 'asc')
            ->get();

        // ══════════════════════════════════════════════════════════════════
        // PART 4 – PAYMENT VOUCHERS  (Discounts / Expenses against customer)
        // ══════════════════════════════════════════════════════════════════
        $paymentVouchersRaw = DB::table('payment_vouchers')
            ->leftJoin('narrations', 'narrations.id', '=', 'payment_vouchers.narration_id')
            ->leftJoin('accounts', 'accounts.id', '=', 'payment_vouchers.row_account_id')
            ->leftJoin('account_heads', 'account_heads.id', '=', 'accounts.head_id')
            ->where('payment_vouchers.party_id', $customerId)
            ->whereBetween(DB::raw('DATE(payment_vouchers.receipt_date)'), [$start, $end])
            ->select(
                'payment_vouchers.id           as id',
                'payment_vouchers.pvid         as pvid',
                'payment_vouchers.receipt_date as txn_date',
                'payment_vouchers.total_amount as credit_amount',
                'payment_vouchers.reference_no as reference_no',
                'payment_vouchers.remarks      as remarks',
                'narrations.narration          as narration_text',
                'account_heads.name            as head_name',
                'accounts.title                as account_title'
            )
            ->orderBy('payment_vouchers.receipt_date', 'asc')
            ->get();

        // ══════════════════════════════════════════════════════════════════
        // BUILD UNIFIED EVENT LIST  (sorted by date → then type priority)
        // ══════════════════════════════════════════════════════════════════
        $events = [];

        // Add Sales
        foreach ($salesGrouped as $saleId => $saleData) {
            $header  = $saleData['header'];
            $items   = $saleData['items'];
            $gp      = $gpMap[$header['invoice_no']] ?? [];
            $dcNo    = !empty($gp) ? implode(' / ', array_unique(array_column($gp, 'dc_no'))) : '-';
            $gpNo    = !empty($gp) ? implode(' / ', array_unique(array_column($gp, 'gatepass_number'))) : '-';


            $events[] = [
                'sort_date'   => $header['txn_date'],
                'type'        => 'sale',
                'priority'    => 1,
                'data'        => [
                    'invoice_no'  => $header['invoice_no'],
                    'bill_no'     => $header['manual_invoice'] ?? '-',
                    'dc_no'       => $dcNo,
                    'gp_no'       => $gpNo,
                    'txn_date'    => $header['txn_date'],
                    'debit'       => $header['total_net'],
                    'add_disc'    => $header['additional_discount'] ?? 0,
                    'extra_chg'   => $header['extra_charges'] ?? 0,
                    'items'       => $items,
                ],
            ];
        }

        // Add Sale Returns
        foreach ($returnsRaw as $ret) {
            $events[] = [
                'sort_date' => $ret->txn_date,
                'type'      => 'return',
                'priority'  => 2,
                'data'      => [
                    'invoice_no'  => $ret->invoice_no ?? '-',
                    'return_note' => $ret->return_note ?? 'RETURN',
                    'txn_date'    => $ret->txn_date,
                    'credit'      => floatval($ret->credit_amount ?? 0),
                    'item_name'   => $ret->item_name ?? '-',
                    'qty'         => floatval($ret->qty ?? 0),
                    'rate'        => floatval($ret->rate ?? 0),
                ],
            ];
        }

        // Add Receipt Vouchers
        foreach ($receiptsRaw as $rec) {
            $isBank = (isset($rec->head_name) && str_contains(strtolower($rec->head_name), 'bank')) 
                || (isset($rec->account_title) && str_contains(strtolower($rec->account_title), 'bank'))
                || (isset($rec->account_title) && in_array(strtoupper($rec->account_title), ['HBL', 'MCB', 'UBL', 'MEEZAN', 'ALLIED', 'ASKARI']));
            $vno    = $rec->rvid ?? ('BRV-' . $rec->id);
            // Description: narration or remarks or default
            $desc   = $rec->narration_text
                ?? ($rec->remarks ?: ($isBank ? 'ONLINE / BANK RECEIVED' : 'CASH RECEIVED'));
            $events[] = [
                'sort_date' => $rec->txn_date,
                'type'      => 'receipt',
                'priority'  => 3,
                'data'      => [
                    'vno'          => $vno,
                    'reference_no' => $rec->reference_no ?? '-',
                    'account'      => $rec->account_title ?? ($isBank ? 'Bank A/c' : 'Cash'),
                    'description'  => strtoupper($desc),
                    'txn_date'     => $rec->txn_date,
                    'credit'       => floatval($rec->credit_amount ?? 0),
                    'is_bank'      => $isBank,
                ],
            ];
        }

        // Add Payment Vouchers (discounts, tour expense, etc.)
        foreach ($paymentVouchersRaw as $pv) {
            $isBank = (isset($pv->head_name) && str_contains(strtolower($pv->head_name), 'bank')) 
                || (isset($pv->account_title) && str_contains(strtolower($pv->account_title), 'bank'))
                || (isset($pv->account_title) && in_array(strtoupper($pv->account_title), ['HBL', 'MCB', 'UBL', 'MEEZAN', 'ALLIED', 'ASKARI']));
            $vno  = $pv->pvid ?? ('PV-' . $pv->id);
            $desc = $pv->narration_text ?? ($pv->remarks ?: 'PAYMENT VOUCHER');
            $events[] = [
                'sort_date' => $pv->txn_date,
                'type'      => 'payment_voucher',
                'priority'  => 4,
                'data'      => [
                    'vno'          => $vno,
                    'reference_no' => $pv->reference_no ?? '-',
                    'description'  => strtoupper($desc),
                    'txn_date'     => $pv->txn_date,
                    'credit'       => floatval($pv->credit_amount ?? 0),
                    'is_bank'      => $isBank,
                ],
            ];
        }

        // Sort all events chronologically
        usort($events, function ($a, $b) {
            $dateCompare = strcmp($a['sort_date'], $b['sort_date']);
            return $dateCompare !== 0 ? $dateCompare : ($a['priority'] - $b['priority']);
        });

        // ══════════════════════════════════════════════════════════════════
        // BUILD FINAL TRANSACTION ROWS WITH RUNNING BALANCE
        // ══════════════════════════════════════════════════════════════════
        $transactions   = [];
        $runningBalance = $openingBalance;
        $totalDebit     = 0;
        $totalCredit    = 0;
        $brCounter      = 0;
        $crCounter      = 0;
        $bpCounter      = 0;
        $cpCounter      = 0;

        foreach ($events as $event) {
            $type = $event['type'];
            $d    = $event['data'];

            if ($type === 'sale') {
                // ── SALE: one parent row (no qty/rate), then item sub-rows ──
                $debit          = $d['debit'];
                $runningBalance += $debit;
                $totalDebit     += $debit;

                // Parent summary row
                $transactions[] = [
                    'row_type'    => 'sale_header',
                    'date'        => date('d-m-y', strtotime($d['txn_date'])),
                    'vno'         => $d['invoice_no'],
                    'bill'        => $d['bill_no'],
                    'dc_no'       => $d['dc_no'],
                    'gp_no'       => $d['gp_no'],
                    'description' => 'SALE',
                    'item_name'   => null,
                    'qty'         => null,
                    'rate'        => null,
                    'debit'       => $debit,
                    'credit'      => 0,
                    'balance'     => $runningBalance,
                ];

                // Item detail sub-rows (qty + rate + stock pricing, no balance change)
                $invoiceTotalQty = 0;
                $invoiceTotalStk = 0;
                foreach ($d['items'] as $item) {
                    $invoiceTotalQty += $item['qty'];
                    $invoiceTotalStk += $item['stock_value'] ?? 0;
                    $transactions[] = [
                        'row_type'     => 'sale_item',
                        'date'         => null,
                        'vno'          => null,
                        'bill'         => null,
                        'dc_no'        => null,
                        'gp_no'        => null,
                        'description'  => null,
                        'item_name'    => $item['item_name'],
                        'qty'          => $item['qty'],
                        'rate'         => $item['rate'],
                        'item_discount'=> $item['item_discount'] ?? 0,
                        'line_amount'  => $item['line_amount']   ?? 0,
                        'policy_price' => $item['policy_price'] ?? 0,
                        'avg_price'    => $item['avg_price']    ?? 0,
                        'avg_s_value'  => $item['avg_s_value']  ?? 0,
                        'n_price'      => $item['n_price']      ?? 0,
                        'stock_value'  => $item['stock_value']  ?? 0,
                        'debit'        => null,
                        'credit'       => null,
                        'balance'      => null,
                    ];
                }

                // Total Qty + Stock Value + Additional Discount + Extra Charges
                $transactions[] = [
                    'row_type'  => 'sale_total',
                    'total_qty' => $invoiceTotalQty,
                    'total_stk' => $invoiceTotalStk,
                    'add_disc'  => $d['add_disc'] ?? 0,
                    'extra_chg' => $d['extra_chg'] ?? 0,
                    'total_net' => $d['debit'],
                ];

            } elseif ($type === 'return') {
                $credit          = $d['credit'];
                $runningBalance -= $credit;
                $totalCredit    += $credit;

                $transactions[] = [
                    'row_type'    => 'return',
                    'date'        => date('d-m-y', strtotime($d['txn_date'])),
                    'vno'         => $d['invoice_no'],
                    'bill'        => $d['return_note'],
                    'dc_no'       => '-',
                    'gp_no'       => '-',
                    'description' => 'SALE RETURN',
                    'item_name'   => $d['item_name'],
                    'qty'         => $d['qty'] > 0 ? $d['qty'] : null,
                    'rate'        => $d['rate'] > 0 ? $d['rate'] : null,
                    'debit'       => 0,
                    'credit'      => $credit,
                    'balance'     => $runningBalance,
                ];

            } elseif ($type === 'receipt') {
                $credit          = $d['credit'];
                $runningBalance -= $credit;
                $totalCredit    += $credit;

                $isBank = $d['is_bank'] ?? false;
                $prefix = $isBank ? 'BR' : 'CR';
                $count  = $isBank ? ++$brCounter : ++$crCounter;
                $displayVno = $d['vno'] . " ($prefix-$count)";

                $transactions[] = [
                    'row_type'    => 'receipt',
                    'date'        => date('d-m-y', strtotime($d['txn_date'])),
                    'vno'         => $displayVno,
                    'bill'        => $d['reference_no'],
                    'dc_no'       => '-',
                    'gp_no'       => '-',
                    'description' => $d['description'],
                    'item_name'   => $d['account'],
                    'qty'         => null,
                    'rate'        => null,
                    'debit'       => 0,
                    'credit'      => $credit,
                    'balance'     => $runningBalance,
                ];

            } elseif ($type === 'payment_voucher') {
                $credit          = $d['credit'];
                $runningBalance -= $credit;
                $totalCredit    += $credit;

                $isBank = $d['is_bank'] ?? false;
                $prefix = $isBank ? 'BP' : 'CP';
                $count  = $isBank ? ++$bpCounter : ++$cpCounter;
                $displayVno = $d['vno'] . " ($prefix-$count)";

                $transactions[] = [
                    'row_type'    => 'payment_voucher',
                    'date'        => date('d-m-y', strtotime($d['txn_date'])),
                    'vno'         => $displayVno,
                    'bill'        => $d['reference_no'],
                    'dc_no'       => '-',
                    'gp_no'       => '-',
                    'description' => $d['description'],
                    'item_name'   => null,
                    'qty'         => null,
                    'rate'        => null,
                    'debit'       => 0,
                    'credit'      => $credit,
                    'balance'     => $runningBalance,
                ];

            } elseif ($type === 'discount') {
                // ERP Standard: Invoice-level discount credited to customer
                $credit          = $d['credit'];
                $runningBalance -= $credit;
                $totalCredit    += $credit;

                $transactions[] = [
                    'row_type'    => 'discount',
                    'date'        => date('d-m-y', strtotime($d['txn_date'])),
                    'vno'         => $d['invoice_no'],
                    'bill'        => null,
                    'dc_no'       => null,
                    'gp_no'       => null,
                    'description' => $d['description'],
                    'item_name'   => null,
                    'qty'         => null,
                    'rate'        => null,
                    'debit'       => 0,
                    'credit'      => $credit,
                    'balance'     => $runningBalance,
                ];
            }
        }

        return response()->json([
            'customer' => [
                'id'             => $customer->id,
                'name'           => $customer->customer_name,
                'type'           => $customer->customer_type,
                'address'        => $customer->address ?? '-',
                'mobile'         => $customer->mobile ?? '-',
                'email'          => $customer->email_address ?? '-',
                'credit_limit'   => floatval($customer->credit_limit ?? 0),
                'opening_balance'=> $openingBalance,
            ],
            'period' => [
                'start' => $start,
                'end'   => $end,
            ],
            'opening_balance' => $openingBalance,
            'total_debit'     => $totalDebit,
            'total_credit'    => $totalCredit,
            'closing_balance' => $runningBalance,
            'transactions'    => $transactions,
        ]);
    }

    public function vendor_ledger_new()
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        // Determine which branches user can view
        if ($user->hasRole('super admin')) {
            $branches = \App\Models\Branch::orderBy('name')->get();
        } else {
            $branches = \App\Models\Branch::where('id', $user->branch_id)->get();
        }

        // Vendors are now BRANCH-SPECIFIC
        $vendorQuery = \App\Models\Vendor::select('id', 'name', 'phone', 'email', 'opening_balance');
        
        if (!$user->hasRole('super admin')) {
            $vendorQuery->where('branch_id', $user->branch_id ?? 0);
        }
        
        $vendors = $vendorQuery->orderBy('name')->get();

        $startDate = date('Y-m-01');
        $endDate   = date('Y-m-d');

        return view('admin_panel.reporting.vendor_ledger_new', compact('branches', 'vendors', 'startDate', 'endDate'));
    }

    public function vendorsByBranch(Request $request)
    {
        $branchId = $request->branch_id ?? null;
        $query = \App\Models\Vendor::query();

        if ($branchId && $branchId !== 'all') {
            $query->where('branch_id', $branchId);
        }

        $vendors = $query->select('id', 'name', 'phone')
            ->orderBy('name')
            ->get();
            
        // Map to match customer format & standard format
        $result = $vendors->map(function($v) {
            return [
                'id' => $v->id,
                'name' => $v->name,
                'customer_name' => $v->name, // Using customer_name key so JS doesn't break
                'customer_type' => '-'
            ];
        });
            
        return response()->json($result);
    }

    public function fetch_vendor_ledger_new(Request $request)
    {
        $user       = auth()->user();
        $vendorId   = (int) $request->vendor_id;
        $branchId   = (int) $request->branch_id; // Add branchId filtering
        $start      = $request->start_date;
        $end        = $request->end_date;

        if (!$vendorId || !$branchId || !$start || !$end) {
            return response()->json(['error' => 'Missing required parameters (vendor, branch, dates)'], 422);
        }

        $vendor       = \App\Models\Vendor::findOrFail($vendorId);
        
        // Ensure branch isolation
        if (!$user->hasRole('super admin') && $vendor->branch_id != ($user->branch_id ?? 0)) {
            return response()->json(['error' => 'Unauthorized vendor access'], 403);
        }
        $allowed      = false;

        if ($user && $user->hasRole('super admin')) {
            $allowed = true;
        } elseif ($user && ($user->branch_id ?? null) && $user->branch_id == $branchId) {
            $allowed = true;
        } elseif ($user && $user->can('report.vendor.ledger.branch.view')) {
            $allowed = true;
        }

        if (!$allowed) {
            return response()->json(['error' => 'Unauthorized branch access'], 403);
        }

        // ── Branch-Specific Opening Balance Calculation ──────────
        // Since vendors are global, the running balance for a branch is the sum of all its prior transactions
        
        // 1. Prior Purchases (Credit)
        $priorPurchases = DB::table('purchases')
            ->where('vendor_id', $vendorId)
            ->where('branch_id', $branchId)
            ->where('created_at', '<', $start . ' 00:00:00')
            ->sum('net_amount');
            
        // 2. Prior Purchase Returns (Debit)
        // Since purchase_returns lacks a direct branch_id, we will show all returns globally for this vendor
        $priorReturns = DB::table('purchase_returns')
            ->where('vendor_id', $vendorId)
            ->where('created_at', '<', $start . ' 00:00:00')
            ->sum('net_amount');

        // 3. Prior Payment Vouchers (Debit)
        // Note: payment_vouchers table does not have a branch_id column, so we count all payments globally or exclude them?
        // Since we cannot filter by branch, we will not filter by branch_id here to avoid SQL errors.
        $priorPayments = DB::table('payment_vouchers')
            ->where('party_id', $vendorId)
            ->where('type', 'vendor')
            ->where('receipt_date', '<', $start)
            ->sum('amount');

        // 4. Prior Receipt Vouchers (Credit)
        $priorReceipts = DB::table('receipts_vouchers')
            ->where('party_id', $vendorId)
            ->where('type', 'vendor')
            ->where('receipt_date', '<', $start)
            ->sum('amount');

        // 5. Prior Vendor Payments (Legacy) (Debit)
        // Check if vendor_payments has branch_id, assuming no for now as it uses admin_or_user_id. 
        // We will exclude legacy vendor_payments from branch logic if it has no branch_id, or assume 0 for branch.
        $priorLegacyPayments = 0; 
        
        // Net Branch Opening Balance = (Initial) + (Credits) - (Debits)
        // Vendors are now branch-specific, so we include their base opening balance
        $openingBalance = (float)($vendor->opening_balance ?? 0) + ($priorPurchases + $priorReceipts) - ($priorReturns + $priorPayments);

        // ── Gate Pass lookup map for Purchases: purchase_id → {dc_no, gp_no} ───
        $gpMap = [];
        $gpRows = DB::table('inward_gatepasses')
            ->whereNotNull('purchase_id')
            ->select('purchase_id', 'gatepass_no', 'bilty_no') // Assuming bilty_no might be used as DC
            ->get();
        foreach ($gpRows as $gp) {
            $gpMap[$gp->purchase_id] = [
                'dc_no'  => $gp->bilty_no ?? '-',
                'gp_no'  => $gp->gatepass_no ?? '-',
            ];
        }

        // ══════════════════════════════════════════════════════════════════
        // PART 1 – PURCHASES (Credit)
        // ══════════════════════════════════════════════════════════════════
        $purchasesRaw = DB::table('purchases')
            ->leftJoin('purchase_items', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->leftJoin('products', 'products.id', '=', 'purchase_items.product_id')
            ->where('purchases.vendor_id', $vendorId)
            ->where('purchases.branch_id', $branchId)
            ->whereBetween(DB::raw('DATE(purchases.created_at)'), [$start, $end])
            ->select(
                'purchases.id          as purchase_id',
                'purchases.invoice_no  as invoice_no',
                DB::raw('COALESCE(purchases.net_amount, 0) as total_net'),
                'purchases.discount    as additional_discount',
                'purchases.extra_cost  as extra_charges',
                'purchases.created_at  as txn_date',
                'products.item_name    as item_name',
                'purchase_items.qty    as qty',
                'purchase_items.item_discount as item_discount',
                'purchase_items.price   as rate',
                'purchase_items.line_total as line_amount'
            )
            ->orderBy('purchases.created_at', 'asc')
            ->orderBy('purchases.id', 'asc')
            ->get();

        $purchasesGrouped = [];
        foreach ($purchasesRaw as $row) {
            if (!isset($purchasesGrouped[$row->purchase_id])) {
                $purchasesGrouped[$row->purchase_id]['header'] = [
                    'invoice_no'          => $row->invoice_no,
                    'total_net'           => floatval($row->total_net ?? 0),
                    'additional_discount' => floatval($row->additional_discount ?? 0),
                    'extra_charges'       => floatval($row->extra_charges ?? 0),
                    'txn_date'            => $row->txn_date,
                ];
            }
            if ($row->item_name) {
                $purchasesGrouped[$row->purchase_id]['items'][] = [
                    'item_name'    => $row->item_name,
                    'qty'          => floatval($row->qty ?? 0),
                    'rate'         => floatval($row->rate ?? 0),
                    'item_discount'=> floatval($row->item_discount ?? 0),
                    'line_amount'  => floatval($row->line_amount ?? 0),
                ];
            }
        }

        // ══════════════════════════════════════════════════════════════════
        // PART 2 – PURCHASE RETURNS (Debit)
        // ══════════════════════════════════════════════════════════════════
        $returnsRaw = DB::table('purchase_returns')
            ->where('vendor_id', $vendorId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$start, $end])
            ->select(
                'id',
                'return_invoice as invoice_no',
                'return_reason as return_note',
                'net_amount as debit_amount',
                'created_at as txn_date'
            )
            ->orderBy('created_at', 'asc')
            ->get();

        // ══════════════════════════════════════════════════════════════════
        // PART 3 – PAYMENT VOUCHERS (Payments TO vendor - Debit)
        // ══════════════════════════════════════════════════════════════════
        $paymentVouchersRaw = DB::table('payment_vouchers')
            ->leftJoin('narrations', 'narrations.id', '=', 'payment_vouchers.narration_id')
            ->leftJoin('accounts', 'accounts.id', '=', 'payment_vouchers.row_account_id')
            ->leftJoin('account_heads', 'account_heads.id', '=', 'accounts.head_id') // Corrected column name
            ->where('payment_vouchers.party_id', $vendorId)
            ->where('payment_vouchers.type', 'vendor')
            ->whereBetween(DB::raw('DATE(payment_vouchers.receipt_date)'), [$start, $end])
            ->select(
                'payment_vouchers.id           as id',
                'payment_vouchers.pvid         as pvid',
                'payment_vouchers.receipt_date as txn_date',
                'payment_vouchers.amount       as amount',
                'payment_vouchers.total_amount as debit_amount',
                'payment_vouchers.reference_no as reference_no',
                'payment_vouchers.remarks      as remarks',
                'payment_vouchers.row_account_id as row_account_id',
                'narrations.narration          as narration_text',
                'account_heads.name            as head_name',
                'accounts.title                as account_title'
            )
            ->orderBy('payment_vouchers.receipt_date', 'asc')
            ->get();

        // ══════════════════════════════════════════════════════════════════
        // PART 4 – RECEIPT VOUCHERS (Refunds FROM vendor - Credit)
        // ══════════════════════════════════════════════════════════════════
        $receiptsRaw = DB::table('receipts_vouchers')
            ->leftJoin('narrations', 'narrations.id', '=', 'receipts_vouchers.narration_id')
            ->leftJoin('accounts', 'accounts.id', '=', 'receipts_vouchers.row_account_id')
            ->leftJoin('account_heads', 'account_heads.id', '=', 'accounts.head_id')
            ->where('receipts_vouchers.party_id', $vendorId)
            ->where('receipts_vouchers.type', 'vendor')
            ->whereBetween(DB::raw('DATE(receipts_vouchers.receipt_date)'), [$start, $end])
            ->select(
                'receipts_vouchers.id           as id',
                'receipts_vouchers.rvid         as rvid',
                'receipts_vouchers.receipt_date as txn_date',
                'receipts_vouchers.amount       as amount',
                'receipts_vouchers.total_amount as credit_amount',
                'receipts_vouchers.reference_no as reference_no',
                'receipts_vouchers.remarks      as remarks',
                'receipts_vouchers.row_account_id as row_account_id',
                'narrations.narration           as narration_text',
                'account_heads.name             as head_name',
                'accounts.title                 as account_title'
            )
            ->orderBy('receipts_vouchers.receipt_date', 'asc')
            ->get();
            
        // ══════════════════════════════════════════════════════════════════
        // PART 5 – VENDOR PAYMENTS (Legacy alternative - Debit)
        // ══════════════════════════════════════════════════════════════════
        $vendorPaymentsRaw = DB::table('vendor_payments')
            ->where('vendor_id', $vendorId)
            ->whereBetween(DB::raw('DATE(payment_date)'), [$start, $end])
            ->select(
                'id',
                'payment_date as txn_date',
                'amount as debit_amount',
                'payment_method',
                'note as remarks'
            )
            ->orderBy('payment_date', 'asc')
            ->get();

        // ══════════════════════════════════════════════════════════════════
        // BUILD UNIFIED EVENT LIST
        // ══════════════════════════════════════════════════════════════════
        $events = [];

        // Add Purchases
        foreach ($purchasesGrouped as $purchaseId => $purchaseData) {
            $header  = $purchaseData['header'];
            $items   = $purchaseData['items'] ?? [];
            $gp      = $gpMap[$purchaseId] ?? ['dc_no' => '-', 'gp_no' => '-'];

            $events[] = [
                'sort_date'   => $header['txn_date'],
                'type'        => 'purchase',
                'priority'    => 1,
                'data'        => [
                    'invoice_no'  => $header['invoice_no'],
                    'bill_no'     => '-',
                    'dc_no'       => $gp['dc_no'],
                    'gp_no'       => $gp['gp_no'],
                    'txn_date'    => $header['txn_date'],
                    'credit'      => $header['total_net'], // Purchases are Credit for Vendor
                    'add_disc'    => $header['additional_discount'] ?? 0,
                    'extra_chg'   => $header['extra_charges'] ?? 0,
                    'items'       => $items,
                ],
            ];
        }

        // Add Purchase Returns
        foreach ($returnsRaw as $ret) {
            $events[] = [
                'sort_date' => $ret->txn_date,
                'type'      => 'return',
                'priority'  => 2,
                'data'      => [
                    'invoice_no'  => $ret->invoice_no ?? '-',
                    'return_note' => $ret->return_note ?? 'RETURN',
                    'txn_date'    => $ret->txn_date,
                    'debit'       => floatval($ret->debit_amount ?? 0), // Returns are Debit
                    'item_name'   => 'PURCHASE RETURN',
                    'qty'         => null,
                    'rate'        => null,
                ],
            ];
        }

        // Add Receipt Vouchers (Refunds FROM vendor - Credit)
        foreach ($receiptsRaw as $rec) {
            $isBank = (isset($rec->head_name) && str_contains(strtolower($rec->head_name), 'bank'))
                || (isset($rec->account_title) && str_contains(strtolower($rec->account_title), 'bank')) // Using account_title join if available
                || (isset($rec->remarks) && str_contains(strtolower($rec->remarks), 'bank'));
            $vno  = $rec->rvid ?? ('RV-' . $rec->id);
            $desc = $rec->narration_text ?? ($rec->remarks ?: 'REFUND RECEIVED');
            
            $accIds = json_decode($rec->row_account_id, true) ?: [$rec->row_account_id];
            $amounts = json_decode($rec->amount, true) ?: [];
            $accountInfo = null;
            if (!empty($accIds)) {
                $accounts = DB::table('accounts')
                    ->whereIn('id', $accIds)
                    ->select('id', 'title', 'account_code')
                    ->get()
                    ->keyBy('id');
                
                $accDetails = [];
                // If amounts is a flat array corresponding to accIds indices
                foreach ($accIds as $idx => $id) {
                    if (isset($accounts[$id])) {
                        $acc = $accounts[$id];
                        $amt = isset($amounts[$idx]) ? number_format($amounts[$idx], 2) : '0.00';
                        $accDetails[] = $acc->title . ($acc->account_code ? " ({$acc->account_code})" : "") . ": " . $amt;
                    }
                }
                $accountInfo = implode(", ", $accDetails);
            }

            $events[] = [
                'sort_date' => $rec->txn_date,
                'type'      => 'receipt',
                'priority'  => 3,
                'data'      => [
                    'vno'          => $vno,
                    'reference_no' => $rec->reference_no ?? '-',
                    'description'  => strtoupper($desc),
                    'account_info' => $accountInfo,
                    'txn_date'     => $rec->txn_date,
                    'credit'       => floatval($rec->credit_amount ?? 0),
                    'is_bank'      => $isBank,
                ],
            ];
        }

        // Add Payment Vouchers (Payments TO vendor - Debit)
        foreach ($paymentVouchersRaw as $pv) {
            $isBank = (isset($pv->head_name) && str_contains(strtolower($pv->head_name), 'bank')) 
                || (isset($pv->account_title) && str_contains(strtolower($pv->account_title), 'bank'))
                || (isset($pv->account_title) && in_array(strtoupper($pv->account_title), ['HBL', 'MCB', 'UBL', 'MEEZAN', 'ALLIED', 'ASKARI']));
            $vno  = $pv->pvid ?? ('PV-' . $pv->id);
            $desc = $pv->narration_text ?? ($pv->remarks ?: 'PAYMENT TO VENDOR');
            
            $accIds = json_decode($pv->row_account_id, true) ?: [$pv->row_account_id];
            $amounts = json_decode($pv->amount, true) ?: [];
            $accountInfo = null;
            if (!empty($accIds)) {
                $accounts = DB::table('accounts')
                    ->whereIn('id', $accIds)
                    ->select('id', 'title', 'account_code')
                    ->get()
                    ->keyBy('id');
                
                $accDetails = [];
                foreach ($accIds as $idx => $id) {
                    if (isset($accounts[$id])) {
                        $acc = $accounts[$id];
                        $amt = isset($amounts[$idx]) ? number_format($amounts[$idx], 2) : '0.00';
                        $accDetails[] = $acc->title . ($acc->account_code ? " ({$acc->account_code})" : "") . ": " . $amt;
                    }
                }
                $accountInfo = implode(", ", $accDetails);
            }

            $events[] = [
                'sort_date' => $pv->txn_date,
                'type'      => 'payment_voucher',
                'priority'  => 4,
                'data'      => [
                    'vno'          => $vno,
                    'reference_no' => $pv->reference_no ?? '-',
                    'description'  => strtoupper($desc),
                    'account_info' => $accountInfo,
                    'txn_date'     => $pv->txn_date,
                    'debit'        => floatval($pv->debit_amount ?? 0),
                    'is_bank'      => $isBank,
                ],
            ];
        }

        // Add Legacy Vendor Payments
        foreach ($vendorPaymentsRaw as $vp) {
            $events[] = [
                'sort_date' => $vp->txn_date,
                'type'      => 'legacy_payment',
                'priority'  => 5,
                'data'      => [
                    'vno'          => 'PAY-' . $vp->id,
                    'description'  => strtoupper($vp->remarks ?: 'LEGACY PAYMENT'),
                    'account_info' => strtoupper($vp->payment_method ?? 'CASH/BANK'),
                    'txn_date'     => $vp->txn_date,
                    'debit'        => floatval($vp->debit_amount ?? 0),
                ],
            ];
        }

        // Sort all events chronologically
        usort($events, function ($a, $b) {
            $dateCompare = strcmp($a['sort_date'], $b['sort_date']);
            return $dateCompare !== 0 ? $dateCompare : ($a['priority'] - $b['priority']);
        });

        // ══════════════════════════════════════════════════════════════════
        // BUILD FINAL TRANSACTION ROWS WITH RUNNING BALANCE
        // ══════════════════════════════════════════════════════════════════
        $transactions   = [];
        $runningBalance = $openingBalance;
        $totalDebit     = 0;
        $totalCredit    = 0;
        $brCounter      = 0;
        $crCounter      = 0;
        $bpCounter      = 0;
        $cpCounter      = 0;

        foreach ($events as $event) {
            $type = $event['type'];
            $d    = $event['data'];

            if ($type === 'purchase') {
                $credit         = $d['credit']; // Vendor liability increases
                $runningBalance += $credit;
                $totalCredit    += $credit;

                // Parent summary row
                $transactions[] = [
                    'row_type'    => 'sale_header', // Kept as 'sale_header' so JS coloring works same
                    'date'        => date('d-m-y', strtotime($d['txn_date'])),
                    'vno'         => $d['invoice_no'],
                    'bill'        => $d['bill_no'],
                    'dc_no'       => $d['dc_no'],
                    'gp_no'       => $d['gp_no'],
                    'description' => 'PURCHASE',
                    'item_name'   => null,
                    'qty'         => null,
                    'rate'        => null,
                    'debit'       => 0,
                    'credit'      => $credit,
                    'balance'     => $runningBalance,
                ];

                $invoiceTotalQty = 0;
                foreach ($d['items'] as $item) {
                    $invoiceTotalQty += $item['qty'];
                    $transactions[] = [
                        'row_type'     => 'sale_item',
                        'date'         => null,
                        'vno'          => null,
                        'bill'         => null,
                        'dc_no'        => null,
                        'gp_no'        => null,
                        'description'  => null,
                        'item_name'    => $item['item_name'],
                        'qty'          => $item['qty'],
                        'rate'         => $item['rate'],
                        'item_discount'=> $item['item_discount'] ?? 0,
                        'line_amount'  => $item['line_amount']   ?? 0,
                        'debit'        => null,
                        'credit'       => null,
                        'balance'      => null,
                    ];
                }

                $transactions[] = [
                    'row_type'  => 'sale_total',
                    'total_qty' => $invoiceTotalQty,
                    'add_disc'  => $d['add_disc'] ?? 0,
                    'extra_chg' => $d['extra_chg'] ?? 0,
                    'total_net' => $d['credit'], // Use credit as total net for formatting
                ];

            } elseif ($type === 'return') {
                $debit           = $d['debit'];
                $runningBalance -= $debit; // Vendor liability decreases
                $totalDebit     += $debit;

                $transactions[] = [
                    'row_type'    => 'return',
                    'date'        => date('d-m-y', strtotime($d['txn_date'])),
                    'vno'         => $d['invoice_no'],
                    'bill'        => $d['return_note'],
                    'dc_no'       => '-',
                    'gp_no'       => '-',
                    'description' => 'PURCHASE RETURN',
                    'item_name'   => $d['item_name'],
                    'qty'         => $d['qty'] > 0 ? $d['qty'] : null,
                    'rate'        => $d['rate'] > 0 ? $d['rate'] : null,
                    'debit'       => $debit,
                    'credit'      => 0,
                    'balance'     => $runningBalance,
                ];

            } elseif ($type === 'payment_voucher' || $type === 'legacy_payment') {
                $debit           = $d['debit'];
                $runningBalance -= $debit; // We pay them, liability decreases
                $totalDebit     += $debit;

                $isBank = $d['is_bank'] ?? false;
                $prefix = $isBank ? 'BP' : 'CP';
                $count  = $isBank ? ++$bpCounter : ++$cpCounter;
                $displayVno = $d['vno'] . " ($prefix-$count)";

                $transactions[] = [
                    'row_type'    => 'payment_voucher',
                    'date'        => date('d-m-y', strtotime($d['txn_date'])),
                    'vno'         => $displayVno,
                    'bill'        => $d['reference_no'] ?? '-',
                    'dc_no'       => '-',
                    'gp_no'       => '-',
                    'description' => $d['description'],
                    'item_name'   => $d['account_info'] ?? null,
                    'qty'         => null,
                    'rate'        => null,
                    'debit'       => $debit,
                    'credit'      => 0,
                    'balance'     => $runningBalance,
                ];

            } elseif ($type === 'receipt') {
                $credit          = $d['credit'];
                $runningBalance += $credit; // Refund to us increases liability
                $totalCredit    += $credit;

                $isBank = $d['is_bank'] ?? false;
                $prefix = $isBank ? 'BR' : 'CR';
                $count  = $isBank ? ++$brCounter : ++$crCounter;
                $displayVno = $d['vno'] . " ($prefix-$count)";

                $transactions[] = [
                    'row_type'    => 'receipt',
                    'date'        => date('d-m-y', strtotime($d['txn_date'])),
                    'vno'         => $displayVno,
                    'bill'        => $d['reference_no'] ?? '-',
                    'dc_no'       => '-',
                    'gp_no'       => '-',
                    'description' => $d['description'],
                    'item_name'   => $d['account_info'] ?? null,
                    'qty'         => null,
                    'rate'        => null,
                    'debit'       => 0,
                    'credit'      => $credit,
                    'balance'     => $runningBalance,
                ];
            }
        }

        return response()->json([
            'vendor' => [
                'id'             => $vendor->id,
                'name'           => $vendor->name,
                'company'        => $vendor->company_name ?? '-',
                'mobile'         => $vendor->phone ?? '-',
                'email'          => $vendor->email ?? '-',
                'opening_balance'=> $openingBalance,
            ],
            'period' => [
                'start' => $start,
                'end'   => $end,
            ],
            'opening_balance' => $openingBalance,
            'total_debit'     => $totalDebit,
            'total_credit'    => $totalCredit,
            'closing_balance' => $runningBalance,
            'transactions'    => $transactions,
        ]);
    }

    public function item_stock_report()
    {
        // Determine which branches user can view
        $user = Auth::user();
        $userBranches = [];
        $selectedBranchId = null;

        if ($user->hasRole('super admin')) {
            // Super admin can view all branches
            $userBranches = \App\Models\Branch::orderBy('name')->get();
            $selectedBranchId = request('branch_id') ?? $userBranches->first()?->id ?? 1;
        } elseif ($user->hasRole('branch admin') || $user->hasRole('warehouse manager')) {
            // Branch admin/manager can only view their own branch
            $userBranches = [\App\Models\Branch::find($user->branch_id)];
            $selectedBranchId = $user->branch_id;
        } else {
            // Regular users see only their branch
            $userBranches = [\App\Models\Branch::find($user->branch_id)];
            $selectedBranchId = $user->branch_id;
        }

        // Get products for selected branch - include all products with warehouse stock, purchases, or sales
        $products = Product::where(function ($query) use ($selectedBranchId) {
            $query->whereHas('warehouseStocks', function ($q) use ($selectedBranchId) {
                $q->where('branch_id', $selectedBranchId);
            });
            
            // Also check purchases for this branch
            $query->orWhereIn('id', function($subQuery) use ($selectedBranchId) {
                $subQuery->select('product_id')
                    ->from('purchase_items')
                    ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
                    ->where('purchases.branch_id', $selectedBranchId);
            });
            
            // Also check sales for this branch
            $query->orWhereIn('id', function($subQuery) use ($selectedBranchId) {
                $subQuery->select('product_id')
                    ->from('sale_items')
                    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->where('sales.branch_id', $selectedBranchId);
            });
        })->orderBy('item_name')->get();

        return view('admin_panel.reporting.item_stock_report', [
            'products' => $products,
            'userBranches' => $userBranches,
            'selectedBranchId' => $selectedBranchId,
            'isSuperAdmin' => $user->hasRole('super admin')
        ]);
    }

    /**
     * ✅ FETCH ITEM STOCK REPORT - Branch-Aware, ERP Standard
     * 
     * Features:
     * 1. Super admin: Can view any branch
     * 2. Branch admin: Views only their branch
     * 3. Simple user: Views only their branch
     * 4. Shows warehouse-wise breakdown per branch
     * 5. International ERP standards compliance
     */
    public function fetchItemStock(Request $request)
    {
        $productId = $request->product_id;
        $requestedBranchId = $request->branch_id;

        // ================= BRANCH ACCESS CONTROL =================
        $user = Auth::user();
        $allowedBranchId = null;

        if ($user->hasRole('super admin')) {
            // Super admin can view any branch
            $allowedBranchId = $requestedBranchId ? (int)$requestedBranchId : $user->branch_id;
        } else {
            // Non-admin can only see their own branch
            $allowedBranchId = $user->branch_id;
        }

        // ================= FETCH PRODUCTS FOR THIS BRANCH ONLY =================
        $productsQuery = Product::query();
        if ($productId && $productId !== 'all') {
            // Single product view - get that specific product
            $productsQuery->where('id', $productId);
        } else {
            // All products view - include products with warehouse stock, purchases, or sales in this branch
            $productsQuery->where(function ($query) use ($allowedBranchId) {
                $query->whereHas('warehouseStocks', function ($q) use ($allowedBranchId) {
                    $q->where('branch_id', $allowedBranchId);
                });
                
                // Also check purchases for this branch
                $query->orWhereIn('id', function($subQuery) use ($allowedBranchId) {
                    $subQuery->select('product_id')
                        ->from('purchase_items')
                        ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
                        ->where('purchases.branch_id', $allowedBranchId);
                });
                
                // Also check sales for this branch
                $query->orWhereIn('id', function($subQuery) use ($allowedBranchId) {
                    $subQuery->select('product_id')
                        ->from('sale_items')
                        ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                        ->where('sales.branch_id', $allowedBranchId);
                });
            });
        }
        $products = $productsQuery->orderBy('item_name')->get();

        // ================= PRE-AGGREGATE DATA FOR ACCURACY & PERFORMANCE =================
        
        // 1. Pre-calculate DELIVERED quantities from Outward Gatepasses (JSON items)
        // This is the source of truth for items physically removed from stock
        $deliveredQtyMap = [];
        $deliveredAmountMap = [];
        
        $gpQuery = DB::table('outward_gatepasses')->whereNotNull('items');
        if (!$user->hasRole('super admin')) {
            $gpQuery->where('branch_id', $allowedBranchId);
        }
        
        foreach ($gpQuery->select('items')->cursor() as $gp) {
            $items = json_decode($gp->items, true);
            if (is_array($items)) {
                foreach ($items as $item) {
                    $pid = $item['product_id'] ?? null;
                    if ($pid) {
                        $deliveredQtyMap[$pid] = ($deliveredQtyMap[$pid] ?? 0) + floatval($item['qty'] ?? 0);
                        $deliveredAmountMap[$pid] = ($deliveredAmountMap[$pid] ?? 0) + floatval($item['amount'] ?? 0);
                    }
                }
            }
        }

        // 2. Pre-calculate TOTAL BOOKED quantities from Sale Items
        $bookedQtyMap = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->when(!$user->hasRole('super admin'), function($q) use ($allowedBranchId) {
                return $q->where('sales.branch_id', $allowedBranchId);
            })
            ->select('sale_items.product_id', DB::raw('SUM(sale_items.sales_qty) as total_qty'))
            ->groupBy('sale_items.product_id')
            ->pluck('total_qty', 'product_id')
            ->toArray();

        $rows = [];
        $grandTotalValue = 0;

        foreach ($products as $product) {
            // ================= GET STOCK FROM warehouse_stocks TABLE (BRANCH-SPECIFIC) =================
            // Note: warehouse_stocks is the single source of truth
            $warehouseStocks = WarehouseStock::where('product_id', $product->id)
                ->where('branch_id', $allowedBranchId)
                ->get();

            // ================= CALCULATE TOTAL BALANCE FROM warehouse_stocks FOR THIS BRANCH =================
            $totalBalance = floatval($warehouseStocks->sum('quantity') ?? 0);

            // ================= GET OPENING STOCK =================
            if ($product->branch_id == $allowedBranchId) {
                $openingStock = floatval($product->initial_stock ?? 0);
            } else {
                $openingStock = 0;
            }

            // ================= GET PURCHASED QTY & AMOUNT =================
            $purchaseQuery = DB::table('purchase_items')
                ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
                ->where('purchase_items.product_id', $product->id);
            
            if (!$user->hasRole('super admin')) {
                $purchaseQuery->where('purchases.branch_id', $allowedBranchId);
            }
            
            $purchaseData = $purchaseQuery->select(
                    DB::raw('COALESCE(SUM(purchase_items.qty), 0) as total_qty'),
                    DB::raw('COALESCE(SUM(purchase_items.line_total), 0) as total_amount')
                )
                ->first();

            $purchased = floatval($purchaseData->total_qty ?? 0);
            $purchaseAmount = floatval($purchaseData->total_amount ?? 0);

            // ================= GET SOLD QTY & AMOUNT (DELIVERED) =================
            // ✅ ERP STANDARD: Sold = Physically Delivered via Gatepass
            $sold = floatval($deliveredQtyMap[$product->id] ?? 0);
            $saleAmount = floatval($deliveredAmountMap[$product->id] ?? 0);

            // ================= GET RESERVED QTY (PENDING DELIVERY) =================
            // ✅ ERP STANDARD: Reserved = Total Ordered - Total Delivered (never negative)
            $totalBooked = floatval($bookedQtyMap[$product->id] ?? 0);
            $reservedQty = max(0, $totalBooked - $sold); // How many still pending delivery

            // ================= GET WAREHOUSE-WISE BREAKDOWN =================
            $warehouseBreakdown = WarehouseStock::where('product_id', $product->id)
                ->where('branch_id', $allowedBranchId)
                ->with('warehouse')
                ->select('warehouse_id', 'quantity')
                ->get()
                ->map(function ($stock) {
                    $warehouseName = $stock->warehouse_id === null ? 'Shop/Branch' : ($stock->warehouse?->warehouse_name ?? "Warehouse #{$stock->warehouse_id}");
                    $location = $stock->warehouse_id === null ? 'Main' : ($stock->warehouse?->location ?? '');
                    return [
                        'warehouse_id' => $stock->warehouse_id,
                        'warehouse_name' => $warehouseName,
                        'location' => $location,
                        'qty' => $stock->quantity
                    ];
                })
                ->toArray();

            // ================= CALCULATE STOCK VALUE =================
            $wholesalePrice = floatval($product->wholesale_price ?? 0);
            $stockValue = $totalBalance * $wholesalePrice;
            $grandTotalValue += $stockValue;

            // ================= BUILD RESPONSE ROW =================
            $rows[] = [
                'id' => $product->id,
                'item_code' => $product->item_code,
                'item_name' => $product->item_name,
                'initial_stock' => $openingStock,
                'purchased' => $purchased,
                'purchase_amount' => $purchaseAmount,
                'sold' => $sold,
                'sale_amount' => $saleAmount,
                'reserved_qty' => $reservedQty, // ✅ Pending delivery
                'balance' => $totalBalance,
                'price' => $wholesalePrice,
                'stock_value' => $stockValue,
                'warehouse_breakdown' => $warehouseBreakdown,
                'branch_id' => $allowedBranchId
            ];
        }

        return response()->json([
            'data' => $rows,
            'grand_total' => $grandTotalValue,
            'branch_id' => $allowedBranchId
        ]);
    }

    public function purchase_report()
    {
        $user = Auth::user();
        
        // Determine which branches user can view
        if ($user->hasRole('super admin')) {
            $branches = \App\Models\Branch::orderBy('name')->get();
        } else {
            $branches = \App\Models\Branch::where('id', $user->branch_id)->get();
        }

        // Vendors are now BRANCH-SPECIFIC
        $vendorQuery = \App\Models\Vendor::select('id', 'name', 'phone');
        if (!$user->hasRole('super admin')) {
            $vendorQuery->where('branch_id', $user->branch_id ?? 0);
        }
        $vendors = $vendorQuery->orderBy('name')->get();

        // ✅ ERP STANDARD: Auto-select current month date range
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->format('Y-m-d');
        
        return view('admin_panel.reporting.purchase_report', compact('startDate', 'endDate', 'vendors', 'branches', 'user'));
    }


    public function fetchPurchaseReport(Request $request)
    {
        $user      = Auth::user();
        $startDate = $request->start_date;
        $endDate   = $request->end_date;
        $vendorId  = $request->vendor_id;
        $branchId  = $request->branch_id;

        // Security: Non-admin can only see their own branch
        if (!$user->hasRole('super admin')) {
            $branchId = $user->branch_id;
        }

        $query = DB::table('purchases')
            ->leftJoin('purchase_items', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->leftJoin('products', 'purchase_items.product_id', '=', 'products.id')
            ->leftJoin('vendors', 'purchases.vendor_id', '=', 'vendors.id')
            ->when($branchId && $branchId !== 'all', function($q) use ($branchId) {
                return $q->where('purchases.branch_id', $branchId);
            })
            ->when($vendorId && $vendorId !== 'all', function($q) use ($vendorId) {
                return $q->where('purchases.vendor_id', $vendorId);
            })
            ->select(
                'purchases.purchase_date',
                'purchases.invoice_no',
                DB::raw('COALESCE(vendors.name, purchases.vendor_name, "Local Market") as vendor_name'),
                'products.item_code',
                'products.item_name',
                'purchase_items.unit',
                'purchase_items.price',
                'purchases.subtotal',
                'purchases.discount',
                'purchases.extra_cost',
                'purchases.net_amount',
                'purchases.paid_amount',
                'purchases.due_amount',
                DB::raw('SUM(purchase_items.qty) as qty'),
                DB::raw('SUM(purchase_items.item_discount) as item_discount'),
                DB::raw('SUM(purchase_items.line_total) as line_total'),
                DB::raw('GROUP_CONCAT(purchase_items.color SEPARATOR ", ") as colors')
            )
            ->groupBy(
                'purchases.id',
                'purchases.purchase_date',
                'purchases.invoice_no',
                'vendors.name',
                'purchases.vendor_name',
                'products.item_code',
                'products.item_name',
                'purchase_items.product_id',
                'purchase_items.price',
                'purchase_items.unit',
                'purchases.subtotal',
                'purchases.discount',
                'purchases.extra_cost',
                'purchases.net_amount',
                'purchases.paid_amount',
                'purchases.due_amount'
            );

        if ($startDate && $endDate) {
            $query->whereBetween('purchases.purchase_date', [$startDate, $endDate]);
        }

        if ($vendorId && $vendorId !== 'all') {
            $query->where('purchases.vendor_id', $vendorId);
        }

        $data = $query->orderBy('purchases.purchase_date', 'asc')->get();

        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * ✅ PO vs Gatepass Report (Procurement Tracking)
     * Shows what was ordered (PO) vs what was actually received (Gatepass)
     */
    public function po_vs_gatepass_report()
    {
        $user = auth()->user();
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->format('Y-m-d');
        
        // Determine which branches user can view
        if ($user->hasRole('super admin')) {
            $branches = \App\Models\Branch::orderBy('name')->get();
            $defaultBranchId = $user->branch_id ?? ($branches->first()?->id ?? 0);
        } else {
            $branches = \App\Models\Branch::where('id', $user->branch_id)->get();
            $defaultBranchId = $user->branch_id;
        }

        // Vendors are now BRANCH-SPECIFIC
        // Default to the vendors of the initially selected branch
        $vendors = \App\Models\Vendor::where('branch_id', $defaultBranchId)
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);
        
        return view('admin_panel.reporting.po_vs_gatepass', compact('startDate', 'endDate', 'vendors', 'branches', 'user'));
    }

    public function fetch_po_vs_gatepass_report(Request $request)
    {
        $user      = auth()->user();
        $startDate = $request->start_date;
        $endDate   = $request->end_date;
        $vendorId  = $request->vendor_id;
        $branchId  = $request->branch_id;

        // Security: Non-admin can only see their own branch
        if (!$user->hasRole('super admin')) {
            $branchId = $user->branch_id;
        }

        // ✅ Query: PO Items compared with their receipt status
        // Only includes POs that have been used to create a Gatepass
        $query = DB::table('purchase_orders')
            ->join('purchase_order_items', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->join('products', 'purchase_order_items.product_id', '=', 'products.id')
            ->join('vendors', 'purchase_orders.vendor_id', '=', 'vendors.id')
            ->leftJoin('branches', 'purchase_orders.branch_id', '=', 'branches.id')
            // Filter to only those POs that have at least one inward gatepass
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('inward_gatepasses')
                    ->whereRaw('inward_gatepasses.purchase_order_id = purchase_orders.id');
            })
            ->when($branchId && $branchId !== 'all', function($q) use ($branchId) {
                return $q->where('purchase_orders.branch_id', $branchId);
            })
            ->when($vendorId && $vendorId !== 'all', function($q) use ($vendorId) {
                return $q->where('purchase_orders.vendor_id', $vendorId);
            })
            ->select(
                'purchase_orders.id as po_id',
                'purchase_orders.po_number',
                'purchase_orders.order_date',
                'vendors.name as vendor_name',
                'branches.name as branch_name',
                'products.item_code',
                'products.item_name',
                DB::raw('GROUP_CONCAT(CONCAT(IFNULL(purchase_order_items.color, "Default"), ": ", purchase_order_items.qty, " / ", purchase_order_items.received_qty) SEPARATOR "||") as color_breakdown'),
                DB::raw('SUM(purchase_order_items.qty) as ordered_qty'),
                DB::raw('SUM(purchase_order_items.received_qty) as received_qty'),
                'purchase_orders.status as po_status'
            )
            ->groupBy(
                'purchase_orders.id',
                'purchase_orders.po_number',
                'purchase_orders.order_date',
                'vendors.name',
                'branches.name',
                'products.item_code',
                'products.item_name',
                'purchase_orders.status'
            );

        if ($startDate && $endDate) {
            $query->whereBetween('purchase_orders.order_date', [$startDate, $endDate]);
        }

        $data = $query->orderBy('purchase_orders.order_date', 'desc')->get();

        return response()->json([
            'data' => $data
        ]);
    }

    public function getVendorsByBranch(Request $request)
    {
        $branchId = $request->branch_id;
        $vendors = \App\Models\Vendor::where('branch_id', $branchId)
            ->orderBy('name')
            ->get(['id', 'name']);
            
        return response()->json($vendors);
    }

    public function sale_report()
    {
        // ✅ ERP STANDARD: Auto-select current month date range
        $startDate = now()->startOfMonth()->format('Y-m-d');  // 1st of current month
        $endDate = now()->format('Y-m-d');                     // Today
        
        $user = auth()->user();
        $branches = \App\Models\Branch::orderBy('name')->get();
        
        $customersQuery = \App\Models\Customer::where('status', 'active');
        
        // If not super admin, filter customers by branch
        if ($user && !$user->hasRole('super admin')) {
            $customersQuery->where('branch_id', $user->branch_id);
        }
        
        $customers = $customersQuery->orderBy('customer_name')->get();

        return view('admin_panel.reporting.sale_report', compact('branches', 'customers', 'startDate', 'endDate'));
    }

    /**
     * ✅ IMPROVED SALE REPORT - With Proper Relations & Business Logic
     * 
     * Workflow:
     * 1. Fetch Sales with customer, items, products, and returns relationships
     * 2. Filter by date range (start_date to end_date)
     * 3. For each sale, aggregate:
     *    - Sale header info (invoice, customer, address, etc.)
     *    - Sale items (product wise breakdown with prices, qty, amounts)
     *    - Sale returns (if any)
     * 4. Return structured JSON with all necessary data
     */
    public function fetchsaleReport(Request $request)
    {
        if ($request->ajax()) {
            $start = $request->start_date;
            $end = $request->end_date;

            // ================= BUILD QUERY WITH RELATIONSHIPS =================
            $query = Sale::with([
                'customer',              // Customer details
                'saleItems.product',     // Sale items with product details
                'saleItems.warehouse',   // Warehouse information
                'branch',                // Branch for reporting
            ]);

            // ================= BRANCH-LEVEL ACCESS CONTROL =================
            $user = auth()->user();
            // Default: non-super users only see their branch
            if ($user && ! $user->hasRole('super admin')) {
                $userBranch = $user->branch_id ?? null;
                if ($userBranch) {
                    $query->where('branch_id', $userBranch);
                }
            } else {
                // Super admin: may view all branches. Additionally allow a special
                // permission to grant other users the ability to view other branches.
                // If request includes a `branch_id` filter, apply it (optional).
                if ($request->filled('branch_id')) {
                    $query->where('branch_id', (int) $request->branch_id);
                }
            }

            // ================= APPLY DATE & CUSTOMER FILTER =================
            if ($start && $end) {
                $query->whereBetween(DB::raw('DATE(created_at)'), [$start, $end]);
            }

            if ($request->filled('customer_id') && $request->customer_id !== 'all') {
                $query->where('customer_id', (int) $request->customer_id);
            }

            // ================= GET SALES & FETCH RETURNS =================
            $sales = $query->orderBy('created_at', 'asc')->get();

            // ================= TRANSFORM DATA FOR FRONTEND =================
            $formattedSales = $sales->map(function ($sale) {
                
                // ============= SALE ITEMS AGGREGATION =============
                $saleItems = [];
                $totalQty = 0;
                $totalAmount = 0;

                if ($sale->saleItems && $sale->saleItems->count() > 0) {
                    foreach ($sale->saleItems as $item) {
                        $product = $item->product;
                        $warehouseName = $item->warehouse ? $item->warehouse->warehouse_name : 'Unknown';

                        $saleItems[] = [
                            'product_id' => $item->product_id,
                            'product_name' => $product ? $product->item_name : 'N/A',
                            'product_code' => $product ? $product->item_code : 'N/A',
                            'warehouse' => $warehouseName,
                            'qty' => floatval($item->sales_qty ?? 0),
                            'price' => floatval($item->retail_price ?? 0),
                            'discount_percent' => floatval($item->discount_percent ?? 0),
                            'discount_amount' => floatval($item->discount_amount ?? 0),
                            'amount' => floatval($item->amount ?? 0),
                        ];

                        $totalQty += floatval($item->sales_qty ?? 0);
                        $totalAmount += floatval($item->amount ?? 0);
                    }
                }

                // ============= SALES RETURNS =============
                $returns = SalesReturn::where('sale_id', $sale->id)->get();
                $returnItems = [];
                $totalReturnAmount = 0;

                foreach ($returns as $return) {
                    $returnItems[] = [
                        'reference' => $return->reference ?? 'RET-' . $return->id,
                        'product' => $return->product,
                        'qty' => $return->qty,
                        'total_net' => floatval($return->total_net ?? 0),
                    ];
                    $totalReturnAmount += floatval($return->total_net ?? 0);
                }

                    // ============= FINAL SALE OBJECT =============
                    $customerName = 'Walk-in Customer';
                    if ($sale->customer && !empty($sale->customer->customer_name)) {
                        $customerName = $sale->customer->customer_name;
                        if (!empty($sale->sub_customer)) {
                            $customerName .= ' (' . $sale->sub_customer . ')';
                        }
                    } elseif (!empty($sale->sub_customer)) {
                        $customerName = $sale->sub_customer . ' (Walk-in)';
                    } elseif (!empty($sale->party_type) && ($sale->party_type === 'walking' || $sale->party_type === 'walk_in')) {
                        $customerName = 'Walk-in Customer';
                    }

                    $branchName = $sale->branch ? ($sale->branch->name ?? $sale->branch->branch_name ?? '') : '';

                return [
                    'id' => $sale->id,
                    'invoice_no' => $sale->invoice_no,
                    'manual_invoice' => $sale->manual_invoice,
                    'created_at' => $sale->created_at,
                    'customer_id' => $sale->customer_id,
                    'customer_name' => $customerName,
                    'party_type' => $sale->party_type,
                    'address' => $sale->address,
                    'tel' => $sale->tel,
                    'remarks' => $sale->remarks,
                    // Branch info (for super-admin or when available)
                    'branch_id' => $sale->branch_id ?? null,
                    'branch_name' => $branchName,
                    
                    // ============= SALE HEADER AMOUNTS =============
                    'sub_total1' => floatval($sale->sub_total1 ?? 0),
                    'sub_total2' => floatval($sale->sub_total2 ?? 0),
                    'discount_percent' => floatval($sale->discount_percent ?? 0),
                    'discount_amount' => floatval($sale->discount_amount ?? 0),
                    'total_balance' => floatval($sale->total_balance ?? 0),
                    'total_net' => floatval($sale->total_net ?? 0),
                    
                    // ============= RECEIPT INFO =============
                    'receipt1' => floatval($sale->receipt1 ?? 0),
                    'receipt2' => floatval($sale->receipt2 ?? 0),
                    'final_balance1' => floatval($sale->final_balance1 ?? 0),
                    'final_balance2' => floatval($sale->final_balance2 ?? 0),
                    
                    // ============= SALE ITEMS BREAKDOWN =============
                    'items' => $saleItems,
                    'items_count' => count($saleItems),
                    'total_qty' => $totalQty,
                    'total_items_amount' => $totalAmount,
                    
                    // ============= RETURNS INFO =============
                    'returns' => $returnItems,
                    'returns_count' => count($returnItems),
                    'total_returns_amount' => $totalReturnAmount,
                ];
            });

            return response()->json($formattedSales);
        }

        return view('admin_panel.reporting.sale_report');
    }

    /**
     * ✅ CUSTOMER LEDGER REPORT VIEW - Fresh Load
     * Returns customers with full details for dropdown selection
     */
    public function customer_ledger_report()
    {
        // ✅ ERP STANDARD: Auto-select current month date range
        $startDate = now()->startOfMonth()->format('Y-m-d');  // 1st of current month
        $endDate = now()->format('Y-m-d');                     // Today
        
        // Branch list and customers depend on user role/permissions
        $user = auth()->user();
        $branches = \App\Models\Branch::orderBy('name')->get();

        if ($user && $user->hasRole('super admin')) {
            // Super admin: show branches; customers will be loaded per-branch in UI
            $customers = collect();
        } else {
            // Non-super: only customers of user's branch
            $userBranchId = $user->branch_id ?? null;
            $customers = Customer::select('id', 'customer_name', 'customer_type', 'opening_balance', 'credit_limit', 'address', 'mobile')
                ->where('status', 'active')
                ->when($userBranchId, function ($q) use ($userBranchId) {
                    $q->where('branch_id', $userBranchId);
                })
                ->where('customer_type', 'credit')
                ->get();
        }

        return view('admin_panel.reporting.customer_ledger_report', compact('branches', 'customers', 'startDate', 'endDate'));
    }

    /**
     * ✅ FETCH CUSTOMER LEDGER - Proper Business Logic with Sales & Receipts
     * 
     * Workflow:
     * 1. Get customer with full details
     * 2. Determine opening balance (from latest ledger entry before start_date)
     * 3. Fetch all sales in date range with invoice numbers
     * 4. Fetch all receipts_voucher (payments) with account details to determine payment mode
     * 5. Merge ledger entries with sales/receipts data
     * 6. Calculate running balance
     * 7. Return formatted ledger with proper transaction descriptions
     */
    public function fetch_customer_ledger(Request $request)
    {
        $user = auth()->user();

        $customerId = $request->customer_id;
        $start = $request->start_date;
        $end = $request->end_date . " 23:59:59";
        $endDate = substr($end, 0, 10);

        // ================= FETCH CUSTOMER DETAILS =================
        $customer = Customer::findOrFail($customerId);

        // ================= BRANCH-LEVEL AUTHORIZATION =================
        $custBranchId = $customer->branch_id ?? null;

        $allowed = false;
        if ($user && $user->hasRole('super admin')) {
            $allowed = true;
        } else {
            // Owner of branch can view
            if ($user && ($user->branch_id ?? null) && $custBranchId && $user->branch_id == $custBranchId) {
                $allowed = true;
            }

            // Users granted base permission can view other branches
            if (! $allowed && $user && $user->can('report.customer.ledger.branch.view')) {
                $allowed = true;
            }

            // Per-branch grant
            if (! $allowed && $user && $custBranchId && $user->can('report.customer.ledger.branch.view.' . $custBranchId)) {
                $allowed = true;
            }
        }

        if (! $allowed) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // ================= DETERMINE OPENING BALANCE =================
        $previousLedger = CustomerLedger::where('customer_id', $customerId)
            ->where('created_at', '<', $start)
            ->latest('created_at')
            ->first();

        $openingBalance = $previousLedger ? floatval($previousLedger->closing_balance) : floatval($customer->opening_balance ?? 0);

        // ================= FETCH SALES IN DATE RANGE =================
        $salesMap = [];
        $sales = Sale::where('customer_id', $customerId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$start, $endDate])
            ->select('id', 'invoice_no', 'total_net', 'created_at')
            ->get();

        foreach ($sales as $sale) {
            $key = $sale->created_at->format('Y-m-d H:i:s');
            $salesMap[$key] = [
                'invoice_no' => $sale->invoice_no,
                'total_net' => floatval($sale->total_net),
            ];
        }

        // ================= FETCH RECEIPTS (PAYMENTS) WITH ACCOUNT DETAILS =================
        $paymentsMap = [];
        $receipts = ReceiptsVoucher::where('party_id', $customerId)
            ->whereBetween(DB::raw('DATE(receipt_date)'), [$start, $endDate])
            ->orderBy('receipt_date', 'asc')
            ->get();

        foreach ($receipts as $receipt) {
            $dateKey = $receipt->receipt_date instanceof \Carbon\Carbon 
                ? $receipt->receipt_date->format('Y-m-d H:i:s')
                : \Carbon\Carbon::parse($receipt->receipt_date)->format('Y-m-d H:i:s');

            // ============= DETERMINE PAYMENT MODE =============
            $paymentMode = "Cash"; // Default
            $accountName = "-";

            // Get account details to determine payment mode
            if ($receipt->row_account_id) {
                $account = Account::find($receipt->row_account_id);
                if ($account) {
                    $accountHead = $account->head;
                    // Check if account head is a bank account
                    if ($accountHead && strtolower($accountHead->name) === 'bank') {
                        $paymentMode = "Bank";
                        $accountName = $account->title ?? "Bank A/c";
                    } else {
                        $paymentMode = "Cash";
                    }
                }
            }

            $paymentsMap[$dateKey] = [
                'amount' => floatval($receipt->total_amount ?? 0),
                'reference' => $receipt->reference_no ?? "-",
                'payment_mode' => $paymentMode,
                'account_name' => $accountName,
            ];
        }

        // ================= FETCH LEDGER ENTRIES IN DATE RANGE =================
        $ledgerEntries = CustomerLedger::where('customer_id', $customerId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$start, $endDate])
            ->orderBy('created_at', 'asc')
            ->get();

        // ================= TRANSFORM LEDGER ENTRIES FOR FRONTEND =================
        $transactions = $ledgerEntries->map(function ($entry) use ($salesMap, $paymentsMap) {
            // Calculate debit and credit from the balance changes
            $difference = floatval($entry->closing_balance) - floatval($entry->previous_balance);

            // If difference is positive = DEBIT (customer owes more)
            // If difference is negative = CREDIT (customer paid/balance reduced)
            $debit = $difference > 0 ? $difference : 0;
            $credit = $difference < 0 ? abs($difference) : 0;

            // ============= DETERMINE TRANSACTION TYPE & INVOICE =============
            $description = "Ledger Entry";
            $invoice = "-";

            // Check if it matches a sale
            $dateKey = $entry->created_at->format('Y-m-d H:i:s');
            if (isset($salesMap[$dateKey])) {
                $saleData = $salesMap[$dateKey];
                $description = "Sale";
                $invoice = $saleData['invoice_no'] ?? "-";
            }
            // Check if it matches a payment/receipt
            elseif (isset($paymentsMap[$dateKey])) {
                $paymentData = $paymentsMap[$dateKey];
                // Build description with payment mode and account name
                if ($paymentData['payment_mode'] === 'Bank') {
                    $description = "Payment Received - Bank ({$paymentData['account_name']})";
                } else {
                    $description = "Payment Received - Cash";
                }
                $invoice = $paymentData['reference'];
            }

            return [
                'date' => $entry->created_at->format('Y-m-d'),
                'invoice' => $invoice,
                'description' => $description,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => floatval($entry->closing_balance),
            ];
        });

        // ================= RETURN FORMATTED RESPONSE =================
        return response()->json([
            'customer' => [
                'id' => $customer->id,
                'customer_name' => $customer->customer_name,
                'customer_type' => $customer->customer_type,
                'address' => $customer->address,
                'mobile' => $customer->mobile,
                'email' => $customer->email_address,
                'credit_limit' => floatval($customer->credit_limit ?? 0),
            ],
            'opening_balance' => $openingBalance,
            'transactions' => $transactions->toArray(),
        ]);
    }

    /**
     * Return customers for a given branch (used by AJAX in customer ledger view)
     */
    public function customersByBranch(Request $request)
    {
        try {
            $branchId = $request->branch_id ?? null;
            $query = Customer::query();

            if ($branchId) {
                $query->where('branch_id', $branchId);
            }

            // Exclude inactive only if status is explicitly inactive
            $query->where(function($q) {
                $q->whereNull('status')
                  ->orWhere('status', '!=', 'inactive')
                  ->orWhere('status', 'active')
                  ->orWhere('status', '1');
            });

            $customers = $query->select('id', 'customer_name', 'customer_type', 'credit_limit', 'address', 'mobile')
                ->orderBy('customer_name')
                ->get();

            return response()->json($customers);
        } catch (\Exception $e) {
            \Log::error('customersByBranch error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * ✅ ENHANCED CUSTOMER LEDGER WITH PRODUCT DETAILS
     * Returns ledger with: Date, V NO, Bill, Description, Qty, Rate, Debit, Credit, Total
     * This is for the customer_leger_new.blade.php view - International ERP Standard format
     */
    public function fetch_customer_ledger_detailed(Request $request)
    {
        $user = auth()->user();
        $customerId = $request->customer_id;
        $start = $request->start_date;
        $end = $request->end_date . " 23:59:59";
        $endDate = substr($end, 0, 10);

        // ✅ Authorization
        $customer = Customer::findOrFail($customerId);
        $custBranchId = $customer->branch_id ?? null;
        $allowed = false;

        if ($user && $user->hasRole('super admin')) {
            $allowed = true;
        } else {
            if ($user && ($user->branch_id ?? null) && $custBranchId && $user->branch_id == $custBranchId) {
                $allowed = true;
            }
            if (! $allowed && $user && $user->can('report.customer.ledger.branch.view')) {
                $allowed = true;
            }
            if (! $allowed && $user && $custBranchId && $user->can('report.customer.ledger.branch.view.' . $custBranchId)) {
                $allowed = true;
            }
        }

        if (! $allowed) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // ✅ Get opening balance
        $previousLedger = CustomerLedger::where('customer_id', $customerId)
            ->where('created_at', '<', $start)
            ->latest('created_at')
            ->first();

        $openingBalance = $previousLedger ? floatval($previousLedger->closing_balance) : floatval($customer->opening_balance ?? 0);

        // ✅ Fetch all sales with their items (for Qty, Rate, Bill)
        $sales = Sale::where('customer_id', $customerId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$start, $endDate])
            ->with(['saleItems.product'])
            ->select('id', 'invoice_no', 'bill_number', 'total_net', 'created_at')
            ->get();

        $salesMap = [];
        foreach ($sales as $sale) {
            $key = $sale->created_at->format('Y-m-d H:i:s');
            $salesMap[$key] = [
                'invoice_no' => $sale->invoice_no,
                'bill_number' => $sale->bill_number ?? '-',
                'total_net' => floatval($sale->total_net),
                'items' => $sale->saleItems ? $sale->saleItems->map(function($item) {
                    return [
                        'product_name' => $item->product->item_name ?? '-',
                        'quantity' => (float) ($item->sales_qty ?? 0),
                        'rate' => (float) ($item->sales_price ?? 0),
                    ];
                })->toArray() : [],
            ];
        }

        // ✅ Fetch receipts (payments) with account details
        $paymentsMap = [];
        $receipts = ReceiptsVoucher::where('party_id', $customerId)
            ->whereBetween(DB::raw('DATE(receipt_date)'), [$start, $endDate])
            ->orderBy('receipt_date', 'asc')
            ->get();

        foreach ($receipts as $receipt) {
            $dateKey = $receipt->receipt_date instanceof \Carbon\Carbon 
                ? $receipt->receipt_date->format('Y-m-d H:i:s')
                : \Carbon\Carbon::parse($receipt->receipt_date)->format('Y-m-d H:i:s');

            $paymentMode = "Cash";
            $accountName = "-";

            if ($receipt->row_account_id) {
                $account = Account::find($receipt->row_account_id);
                if ($account) {
                    $accountHead = $account->head;
                    if ($accountHead && strtolower($accountHead->name) === 'bank') {
                        $paymentMode = "Bank";
                        $accountName = $account->title ?? "Bank A/c";
                    }
                }
            }

            $paymentsMap[$dateKey] = [
                'amount' => floatval($receipt->total_amount ?? 0),
                'reference' => $receipt->reference_no ?? "-",
                'voucher_no' => $receipt->receipt_no ?? "REC-" . $receipt->id,
                'payment_mode' => $paymentMode,
                'account_name' => $accountName,
            ];
        }

        // ✅ Fetch ledger entries
        $ledgerEntries = CustomerLedger::where('customer_id', $customerId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$start, $endDate])
            ->orderBy('created_at', 'asc')
            ->get();

        // ✅ Transform ledger entries with enhanced details
        $transactions = $ledgerEntries->map(function ($entry) use ($salesMap, $paymentsMap) {
            $difference = floatval($entry->closing_balance) - floatval($entry->previous_balance);
            $debit = $difference > 0 ? $difference : 0;
            $credit = $difference < 0 ? abs($difference) : 0;

            $dateKey = $entry->created_at->format('Y-m-d H:i:s');
            
            $voucherNo = "-";
            $billNumber = "-";
            $description = "Ledger Entry";
            $qty = null;
            $rate = null;
            $itemName = "-";

            // Check if it matches a sale
            if (isset($salesMap[$dateKey])) {
                $saleData = $salesMap[$dateKey];
                $voucherNo = $saleData['invoice_no'] ?? "-";
                $billNumber = $saleData['bill_number'] ?? "-";
                $description = "SALE";
                
                // Get first item's details (or combine if multiple items)
                if (!empty($saleData['items'])) {
                    $firstItem = $saleData['items'][0];
                    $itemName = $firstItem['product_name'];
                    $qty = $firstItem['quantity'];
                    $rate = $firstItem['rate'];
                }
            }
            // Check if it matches a payment
            elseif (isset($paymentsMap[$dateKey])) {
                $paymentData = $paymentsMap[$dateKey];
                $voucherNo = $paymentData['voucher_no'];
                $billNumber = $paymentData['reference'];
                if ($paymentData['payment_mode'] === 'Bank') {
                    $description = "PAYMENT - BANK (" . $paymentData['account_name'] . ")";
                } else {
                    $description = "PAYMENT - CASH";
                }
            }

            return [
                'date' => $entry->created_at->format('d-m-y'),
                'vno' => $voucherNo,
                'bill' => $billNumber,
                'description' => $description,
                'item_name' => $itemName,
                'qty' => $qty,
                'rate' => $rate,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => floatval($entry->closing_balance),
            ];
        });

        // ✅ Return comprehensive response
        return response()->json([
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->customer_name,
                'type' => $customer->customer_type,
                'address' => $customer->address ?? '-',
                'mobile' => $customer->mobile ?? '-',
                'email' => $customer->email_address ?? '-',
                'credit_limit' => floatval($customer->credit_limit ?? 0),
                'opening_balance' => $openingBalance,
            ],
            'period' => [
                'start' => $start,
                'end' => $endDate,
            ],
            'opening_balance' => $openingBalance,
            'transactions' => $transactions->toArray(),
        ]);
    }

    /**
     * ═══════════════════════════════════════════════════════════════════════════
     * 📊 STOCK HOLD AUDIT REPORT
     * ═══════════════════════════════════════════════════════════════════════════
     * 
     * Shows complete audit trail for draft_posted sales:
     * - How much stock was available when DC was created
     * - How much was delivered
     * - How much remained in inventory
     * 
     * Perfect for ERP audit and compliance tracking
     */
    public function stockHoldAudit(Request $request)
    {
        try {
            // ✅ Fetch all stock holds (with filters)
            // ✅ INTERNATIONAL ERP STANDARD: Eager load product relationships for data integrity
            // ✅ LEFT JOIN with outward_gatepasses to show if Gate Pass created
            $query = \App\Models\StockHold::with([
                'sale.customer',   // ✅ Load sale's customer for regular customer names
                'sale.booking',    // Load booking to get walking customer names (sub_customer)
                'sale.saleItems',  // ✅ Load sale items to calculate total qty for invoice
                'product.brand',   // Fetch brand relationship to avoid N+1
                'product.unit',    // Fetch unit relationship to avoid N+1
                'warehouse',
                'customer',
                'creator'
            ])
            ->leftJoin('outward_gatepasses', 'stock_holds.warehouse_order_id', '=', 'outward_gatepasses.order_id')
            ->select(
                'stock_holds.*',
                \DB::raw('COALESCE(outward_gatepasses.id, 0) as has_gatepass')  // ✅ 1 if GP exists, 0 if not
            )
            ->orderBy('stock_holds.created_at', 'desc');

            // Filter by invoice if provided
            if ($request->has('invoice_no') && !empty($request->invoice_no)) {
                $query->where('invoice_no', 'like', '%' . $request->invoice_no . '%');
            }

            // Filter by DC if provided
            if ($request->has('dc_no') && !empty($request->dc_no)) {
                $query->where('dc_no', $request->dc_no);
            }

            // Filter by customer
            if ($request->has('customer_id') && !empty($request->customer_id)) {
                $query->where('customer_id', $request->customer_id);
            }

            // Filter by warehouse
            if ($request->has('warehouse_id') && !empty($request->warehouse_id)) {
                $query->where('warehouse_id', $request->warehouse_id);
            }

            // Filter by date range
            if ($request->has('date_from') && !empty($request->date_from)) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->has('date_to') && !empty($request->date_to)) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $stockHolds = $query->paginate(50);

            // ✅ INTERNATIONAL ERP STANDARD: Eager loaded relationships
            // Formatting will be applied in blade template to preserve pagination

            // ✅ Summary statistics
            $totalAvailableQty = \App\Models\StockHold::sum('available_qty');
            $totalDeliverQty = \App\Models\StockHold::sum('deliver_qty');
            $totalRemainingQty = \App\Models\StockHold::sum('remaining_qty');
            $totalValue = \App\Models\StockHold::selectRaw('SUM(deliver_qty * unit_price) as total')
                ->first()->total ?? 0;

            // ✅ Get filters for dropdowns
            $customers = Customer::orderBy('customer_name')->get();
            $warehouses = Warehouse::orderBy('warehouse_name')->get();

            return view('admin_panel.reporting.stock_hold_audit', [
                'stockHolds' => $stockHolds,
                'customers' => $customers,
                'warehouses' => $warehouses,
                'totalAvailableQty' => $totalAvailableQty,
                'totalDeliverQty' => $totalDeliverQty,
                'totalRemainingQty' => $totalRemainingQty,
                'totalValue' => $totalValue,
                'filters' => $request->all(),
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Error loading stock hold audit: ' . $e->getMessage());
        }
    }

    /**
     * Export stock hold audit as Excel
     */
    public function stockHoldAuditExport(Request $request)
    {
        try {
            $query = \App\Models\StockHold::with(['product', 'warehouse', 'customer', 'sale.booking']);

            // Apply same filters
            if ($request->has('invoice_no') && !empty($request->invoice_no)) {
                $query->where('invoice_no', 'like', '%' . $request->invoice_no . '%');
            }
            if ($request->has('dc_no') && !empty($request->dc_no)) {
                $query->where('dc_no', $request->dc_no);
            }
            if ($request->has('customer_id') && !empty($request->customer_id)) {
                $query->where('customer_id', $request->customer_id);
            }
            if ($request->has('warehouse_id') && !empty($request->warehouse_id)) {
                $query->where('warehouse_id', $request->warehouse_id);
            }
            if ($request->has('date_from') && !empty($request->date_from)) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->has('date_to') && !empty($request->date_to)) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $stockHolds = $query->get();

            // Create CSV
            $csv = "Invoice,DC No,Product,Warehouse,Customer,Available Qty,Deliver Qty,Remaining Qty,Unit Price,Total Value,Created Date\n";
            foreach ($stockHolds as $hold) {
                $totalValue = ($hold->deliver_qty ?? 0) * ($hold->unit_price ?? 0);
                $csv .= implode(',', [
                    $hold->invoice_no,
                    $hold->dc_no,
                    $hold->product_name,
                    $hold->warehouse?->warehouse_name ?? 'N/A',
                    $hold->customer?->customer_name ?? 'N/A',
                    $hold->available_qty,
                    $hold->deliver_qty,
                    $hold->remaining_qty,
                    $hold->unit_price,
                    number_format($totalValue, 2),
                    $hold->created_at->format('Y-m-d H:i'),
                ]) . "\n";
            }

            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="stock_hold_audit_' . now()->format('Y-m-d_H-i') . '.csv"',
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Error exporting stock hold audit: ' . $e->getMessage());
        }
    }

    /**
     * ✅ Salesman Performance Report View
     */
    public function salesman_performance_report()
    {
        $user = Auth::user();
        $isSuper = $user->hasRole('super admin');
        $userBranchId = $user->branch_id ?? null;

        // ✅ ERP Standard: Branch List for Super Admin
        $branches = $isSuper ? Branch::all() : Branch::where('id', $userBranchId)->get();

        // ✅ If NOT super-admin, fetch only salesmen for their branch
        if (!$isSuper && $userBranchId) {
            $salesmanIds = Sale::where('branch_id', $userBranchId)
                ->whereNotNull('salesman_id')
                ->distinct()
                ->pluck('salesman_id');
                
            $salesmen = \App\Models\SalesOfficer::whereIn('id', $salesmanIds)
                ->orderBy('name')
                ->get();
        } else {
            $salesmen = \App\Models\SalesOfficer::orderBy('name')->get();
        }

        return view('admin_panel.reporting.salesman_performance', compact('branches', 'isSuper', 'salesmen', 'userBranchId'));
    }

    /**
     * ✅ Fetch Salesmen by Branch (AJAX)
     */
    public function salesmenByBranch(Request $request)
    {
        $branchId = $request->branch_id;
        
        if (!$branchId) {
            $salesmen = \App\Models\SalesOfficer::orderBy('name')->get();
        } else {
            // ✅ ERP Standard: Show salesmen who have actually made sales in this branch
            $salesmanIds = Sale::where('branch_id', $branchId)
                ->whereNotNull('salesman_id')
                ->distinct()
                ->pluck('salesman_id');
                
            $salesmen = \App\Models\SalesOfficer::whereIn('id', $salesmanIds)
                ->orderBy('name')
                ->get();
        }

        return response()->json($salesmen);
    }

    /**
     * ✅ Fetch Salesman Performance Data
     */
    public function fetch_salesman_performance(Request $request)
    {
        $user = Auth::user();
        $isSuper = $user->hasRole('super admin');
        $branchId = $isSuper ? $request->branch_id : $user->branch_id;

        $startDate = $request->start_date ? $request->start_date . ' 00:00:00' : now()->startOfMonth();
        $endDate = $request->end_date ? $request->end_date . ' 23:59:59' : now()->endOfMonth();

        try {
            $query = DB::table('sales')
                ->leftJoin('sales_officers', 'sales.salesman_id', '=', 'sales_officers.id')
                ->leftJoin('branches', 'sales.branch_id', '=', 'branches.id')
                ->select(
                    DB::raw('IFNULL(sales_officers.name, "Direct Sale (No Salesman)") as salesman_name'),
                    'branches.name as branch_name',
                    DB::raw('COUNT(sales.id) as total_invoices'),
                    DB::raw('SUM(sales.total_net) as total_amount'),
                    DB::raw('DATE_FORMAT(sales.created_at, "%M %Y") as month_year')
                )
                ->whereBetween('sales.created_at', [$startDate, $endDate]);

            if ($branchId) {
                $query->where('sales.branch_id', $branchId);
            }

            $results = $query->groupBy('salesman_name', 'branches.name', 'sales.branch_id', 'month_year')
                ->orderBy('total_amount', 'DESC')
                ->get();

            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /**
     * ✅ Fetch Detailed Salesman Ledger (Transaction List)
     */
    public function fetch_salesman_ledger(Request $request)
    {
        $user = Auth::user();
        $isSuper = $user->hasRole('super admin');
        $branchId = $isSuper ? $request->branch_id : $user->branch_id;

        $salesmanId = $request->salesman_id;
        $start = $request->start_date ? $request->start_date . ' 00:00:00' : now()->startOfMonth();
        $end = $request->end_date ? $request->end_date . ' 23:59:59' : now()->endOfMonth();

        try {
            $query = Sale::with(['customer', 'branch'])
                ->whereBetween('created_at', [$start, $end]);

            if ($branchId) {
                $query->where('branch_id', $branchId);
            }

            if ($salesmanId === 'direct') {
                $query->whereNull('salesman_id');
            } elseif ($salesmanId) {
                $query->where('salesman_id', $salesmanId);
            }

            $sales = $query->orderBy('created_at', 'ASC')->get();

            $formatted = $sales->map(function ($s) {
                return [
                    'date' => $s->created_at->format('d-M-Y'),
                    'invoice_no' => $s->invoice_no,
                    'customer' => $s->customer ? $s->customer->customer_name : ($s->sub_customer ?? 'Walking Customer'),
                    'total_amount' => (float)$s->total_net,
                    'branch' => $s->branch ? $s->branch->name : '-',
                ];
            });

            return response()->json($formatted);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * ✅ NEW: Local Purchase Report View
     */
    public function local_purchase_report()
    {
        $user = auth()->user();
        if ($user->hasRole('super admin')) {
            $branches = Branch::orderBy('name')->get();
            $shops = DB::table('purchases')
                ->where(function($q) {
                    $q->where('purchase_type', 'local')
                      ->orWhereNull('vendor_id');
                })
                ->whereNotNull('vendor_name')
                ->where('vendor_name', '!=', '')
                ->distinct()
                ->orderBy('vendor_name')
                ->pluck('vendor_name');
        } else {
            $branches = Branch::where('id', $user->branch_id)->get();
            $shops = DB::table('purchases')
                ->where(function($q) {
                    $q->where('purchase_type', 'local')
                      ->orWhereNull('vendor_id');
                })
                ->where('branch_id', $user->branch_id)
                ->whereNotNull('vendor_name')
                ->where('vendor_name', '!=', '')
                ->distinct()
                ->orderBy('vendor_name')
                ->pluck('vendor_name');
        }

        $startDate = date('Y-m-01');
        $endDate   = date('Y-m-d');

        // ✅ Get account heads and accounts for payment modal
        $accountHeads = \App\Models\AccountHead::whereIn('status', [1, '1', 'active'])->orderBy('name')->get();
        $accountQuery = \App\Models\Account::with('head')->whereIn('status', [1, '1', 'active']);
        if (!$user->hasRole('super admin')) {
            $accountQuery->where('branch_id', $user->branch_id);
        }
        $bankAccounts = $accountQuery->orderBy('title')->get();

        return view('admin_panel.reporting.local_purchase_report', compact('branches', 'shops', 'startDate', 'endDate', 'bankAccounts', 'accountHeads'));
    }

    /**
     * ✅ NEW: Fetch Local Shops by Branch (AJAX)
     */
    public function shopsByBranch(Request $request)
    {
        $user = auth()->user();
        $branchId = $request->branch_id;

        $query = DB::table('purchases')
            ->where(function($q) {
                $q->where('purchase_type', 'local')
                  ->orWhereNull('vendor_id');
            })
            ->whereNotNull('vendor_name')
            ->where('vendor_name', '!=', '');

        if (!$user->hasRole('super admin')) {
            $query->where('branch_id', $user->branch_id);
        } elseif (!empty($branchId)) {
            $query->where('branch_id', $branchId);
        }

        $shops = $query->distinct()->orderBy('vendor_name')->pluck('vendor_name');

        return response()->json($shops);
    }

    /**
     * ✅ NEW: Fetch Local Purchase Report Data (AJAX)
     */
    public function fetch_local_purchase_report(Request $request)
    {
        $user      = auth()->user();
        $start     = $request->start_date;
        $end       = $request->end_date;
        $branchId  = $request->branch_id;
        $shopName  = $request->shop_name;

        $query = DB::table('purchases')
            ->where(function($q) {
                $q->where('purchase_type', 'local')
                  ->orWhereNull('vendor_id');
            });

        if (!empty($shopName)) {
            $query->where('vendor_name', $shopName);
        }

        if ($start && $end) {
            $query->whereBetween(DB::raw('DATE(purchase_date)'), [$start, $end]);
        }

        if (!$user->hasRole('super admin')) {
            $query->where('branch_id', $user->branch_id);
        } elseif (!empty($branchId)) {
            $query->where('branch_id', $branchId);
        }

        $purchases = $query->select(
                'id',
                'invoice_no',
                'vendor_name',
                'purchase_date',
                'net_amount',
                'paid_amount',
                'due_amount',
                'branch_id'
            )
            ->orderBy('purchase_date', 'asc')
            ->get();

        $branches = Branch::pluck('name', 'id');
        
        $results = $purchases->map(function($p) use ($branches) {
            return [
                'id'            => $p->id,
                'invoice_no'    => $p->invoice_no,
                'shop_name'     => $p->vendor_name ?? 'Local Market',
                'date'          => date('d-m-Y', strtotime($p->purchase_date)),
                'branch'        => $branches[$p->branch_id] ?? '-',
                'net_amount'    => (float)$p->net_amount,
                'paid_amount'   => (float)$p->paid_amount,
                'due_amount'    => (float)$p->due_amount,
                'status'        => $p->due_amount <= 0 ? 'Paid' : ($p->paid_amount > 0 ? 'Partial' : 'Due'),
            ];
        });

        return response()->json([
            'data' => $results,
            'summary' => [
                'total_net'  => (float)$results->sum('net_amount'),
                'total_paid' => (float)$results->sum('paid_amount'),
                'total_due'  => (float)$results->sum('due_amount'),
            ]
        ]);
    }
}
