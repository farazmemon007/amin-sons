<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\InwardGatepass;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FindController extends Controller
{
    /**
     * Show the Find Document page
     */
    public function index()
    {
        $user         = Auth::user();
        $isSuperAdmin = $user->hasRole('super admin');
        $branches     = $isSuperAdmin ? Branch::orderBy('name')->get() : collect();
        $userBranchId = $user->branch_id;

        return view('admin_panel.find.index', compact(
            'isSuperAdmin', 'branches', 'userBranchId'
        ));
    }

    /**
     * AJAX: Search for a document by type + number
     * Returns JSON with document details + action URLs
     */
    public function search(Request $request)
    {
        $type      = $request->get('type');
        $number    = trim($request->get('number', ''));
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');
        $branchId  = (int) $request->get('branch_id', 0);

        $user         = Auth::user();
        $isSuperAdmin = $user->hasRole('super admin');

        // Non-super-admin always uses their own branch
        if (!$isSuperAdmin) {
            $branchId = (int) $user->branch_id;
        }

        if (!$type || ($number === '' && (!$startDate || !$endDate))) {
            return response()->json(['found' => false, 'message' => 'Please enter a document number or select a date range.']);
        }

        $result = null;

        switch ($type) {
            case 'sale_invoice':
                $result = $this->findSaleInvoice($number, $startDate, $endDate, $branchId, $isSuperAdmin);
                break;
            case 'purchase_invoice':
                $result = $this->findPurchaseInvoice($number, $startDate, $endDate, $branchId, $isSuperAdmin);
                break;
            case 'delivery_challan':
                $result = $this->findDeliveryChallan($number, $startDate, $endDate, $branchId, $isSuperAdmin);
                break;
            case 'outward_gatepass':
                $result = $this->findOutwardGatepass($number, $startDate, $endDate, $branchId, $isSuperAdmin);
                break;
            case 'inward_gatepass':
                $result = $this->findInwardGatepass($number, $startDate, $endDate, $branchId, $isSuperAdmin);
                break;
            default:
                return response()->json(['found' => false, 'message' => 'Invalid document type.']);
        }

        return response()->json($result);
    }

    // ─── Sale Invoice ─────────────────────────────────────────────
    private function findSaleInvoice(string $number, ?string $startDate, ?string $endDate, int $branchId, bool $isSuperAdmin): array
    {
        $query = Sale::with(['customer', 'branch', 'saleItems']);

        if ($number !== '') {
            $query->where(function ($q) use ($number) {
                $q->where('invoice_no', $number)
                  ->orWhere('invoice_no', 'like', "%{$number}%")
                  ->orWhere('manual_invoice', $number)
                  ->orWhere('manual_invoice', 'like', "%{$number}%");
            });
        }

        if ($startDate && $endDate) {
            $query->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $limit = ($number === '') ? 50 : 20;
        $records = $query->orderByDesc('id')->limit($limit)->get();

        if ($records->isEmpty()) {
            return ['found' => false, 'message' => 'No Sale Invoice found with this number.'];
        }

        // Pre-fetch all outward gatepasses for these sale IDs (for Gatepass button)
        $saleIds = $records->pluck('id')->toArray();
        $outwardGatepasses = DB::table('outward_gatepasses')
            ->whereIn('order_id', $saleIds)
            ->get()
            ->keyBy('order_id');

        // Pre-fetch warehouse orders (for DC availability logic)
        $warehouseOrders = DB::table('warehouse_orders')
            ->whereIn('sale_id', $saleIds)
            ->pluck('sale_id')
            ->toArray();

        $items = $records->map(function ($sale) use ($outwardGatepasses, $warehouseOrders) {
            $ogp = $outwardGatepasses->get($sale->id);
            $hasGatepass = $ogp !== null;

            $isDraftPosted = $sale->status === 'draft_posted';
            $dcExists = in_array($sale->id, $warehouseOrders);

            $hasReceipt = $sale->finalized_at || $sale->is_posted;

            // DC URLs
            $dcUrl = $dcExists ? route('sale.dc', $sale->id) : null;
            $dcCreateUrl = (!$dcExists && $isDraftPosted) ? route('sale.warehouse.select', $sale->id) : null;

            // Gatepass URLs
            $gatepassUrl = $hasGatepass ? route('OutwardGatepass.show', $ogp->id) : null;
            $gatepassCreateUrl = (!$hasGatepass && $dcExists) ? route('outward_gatepass.create', $sale->id) : null;

            return [
                'id'            => $sale->id,
                'doc_number'    => $sale->invoice_no ?? 'N/A',
                'manual_number' => $sale->manual_invoice,
                'date'          => $sale->created_at?->format('d-M-Y H:i') ?? '—',
                'party'         => optional($sale->customer)->name ?? $sale->sub_customer ?? 'Walk-in',
                'branch'        => optional($sale->branch)->name ?? '—',
                'amount'        => number_format($sale->total_net ?? 0, 2),
                'status'        => $sale->status ?? '—',
                'items_count'   => $sale->saleItems->count(),
                'urls'          => [
                    'view'            => route('sale.invoice', $sale->id),
                    'delete'          => route('sales.destroy', $sale->id),
                    'dc'              => $dcUrl,
                    'dc_create'       => $dcCreateUrl,
                    'receipt'         => $hasReceipt ? route('sales.recepit', $sale->id) : null,
                    'gatepass'        => $gatepassUrl,
                    'gatepass_create' => $gatepassCreateUrl,
                ],
            ];
        });

        return ['found' => true, 'type' => 'Sale Invoice', 'icon' => 'fas fa-file-invoice', 'color' => '#6366f1', 'records' => $items->toArray()];
    }

    // ─── Delivery Challan ─────────────────────────────────────────
    private function findDeliveryChallan(string $number, ?string $startDate, ?string $endDate, int $branchId, bool $isSuperAdmin): array
    {
        $query = Sale::with(['customer', 'branch', 'saleItems']);

        if ($number !== '') {
            $query->where(function ($q) use ($number) {
                // For DC, they might type the sale ID, invoice_no, or manual_invoice
                $q->where('id', $number)
                  ->orWhere('invoice_no', $number)
                  ->orWhere('invoice_no', 'like', "%{$number}%")
                  ->orWhere('manual_invoice', $number)
                  ->orWhere('manual_invoice', 'like', "%{$number}%");
            });
        }

        if ($startDate && $endDate) {
            $query->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $limit = ($number === '') ? 50 : 20;
        $records = $query->orderByDesc('id')->limit($limit)->get();

        if ($records->isEmpty()) {
            return ['found' => false, 'message' => 'No Delivery Challan found with this number.'];
        }

        $saleIds = $records->pluck('id')->toArray();
        $outwardGatepasses = DB::table('outward_gatepasses')
            ->whereIn('order_id', $saleIds)
            ->get()
            ->keyBy('order_id');

        $warehouseOrders = DB::table('warehouse_orders')
            ->whereIn('sale_id', $saleIds)
            ->pluck('sale_id')
            ->toArray();

        $items = [];
        foreach ($records as $sale) {
            $isDraftPosted = $sale->status === 'draft_posted';
            $dcExists = in_array($sale->id, $warehouseOrders);
            $needsWarehouseSelection = $isDraftPosted && !$dcExists;
            $hasDC = !$needsWarehouseSelection;

            // Only return records that actually have a DC
            if (!$hasDC) {
                continue;
            }

            $ogp = $outwardGatepasses->get($sale->id);
            $hasGatepass = $ogp !== null;

            $items[] = [
                'id'            => $sale->id,
                'doc_number'    => 'DC - ' . ($sale->invoice_no ?? 'N/A'),
                'manual_number' => 'Sale ID: ' . $sale->id,
                'date'          => $sale->created_at?->format('d-M-Y H:i') ?? '—',
                'party'         => optional($sale->customer)->name ?? $sale->sub_customer ?? 'Walk-in',
                'branch'        => optional($sale->branch)->name ?? '—',
                'amount'        => number_format($sale->total_net ?? 0, 2),
                'status'        => 'DC Ready',
                'items_count'   => $sale->saleItems->count(),
                'urls'          => [
                    'view'    => route('sale.dc', $sale->id),
                    'gatepass'=> $hasGatepass ? route('OutwardGatepass.show', $ogp->id) : null,
                ],
            ];
        }

        if (empty($items)) {
            return ['found' => false, 'message' => 'No Delivery Challan found with this number. The invoice might be pending warehouse selection.'];
        }

        return ['found' => true, 'type' => 'Delivery Challan', 'icon' => 'fas fa-truck-loading', 'color' => '#10b981', 'records' => $items];
    }

    // ─── Purchase Invoice ─────────────────────────────────────────
    private function findPurchaseInvoice(string $number, ?string $startDate, ?string $endDate, int $branchId, bool $isSuperAdmin): array
    {
        $query = Purchase::with(['vendor', 'branch', 'items']);

        if ($number !== '') {
            $query->where(function ($q) use ($number) {
                $q->where('invoice_no', $number)
                  ->orWhere('invoice_no', 'like', "%{$number}%");
            });
        }

        if ($startDate && $endDate) {
            $query->whereBetween(DB::raw('DATE(purchase_date)'), [$startDate, $endDate]);
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $limit = ($number === '') ? 50 : 20;
        $records = $query->orderByDesc('id')->limit($limit)->get();

        if ($records->isEmpty()) {
            return ['found' => false, 'message' => 'No Purchase Invoice found with this number.'];
        }

        // Pre-fetch inward gatepasses for these purchase IDs
        $purchaseIds = $records->pluck('id')->toArray();
        $inwardGatepasses = InwardGatepass::whereIn('purchase_id', $purchaseIds)
            ->get()
            ->keyBy('purchase_id');

        $items = $records->map(function ($purchase) use ($inwardGatepasses) {
            $igp = $inwardGatepasses->get($purchase->id);
            $hasGatepass = $igp !== null;

            return [
                'id'            => $purchase->id,
                'doc_number'    => $purchase->invoice_no ?? 'N/A',
                'manual_number' => null,
                'date'          => $purchase->purchase_date?->format('d-M-Y') ?? '—',
                'party'         => optional($purchase->vendor)->name ?? '—',
                'branch'        => optional($purchase->branch)->name ?? '—',
                'amount'        => number_format($purchase->net_amount ?? 0, 2),
                'status'        => $purchase->status_purchase ?? '—',
                'items_count'   => $purchase->items->count(),
                'urls'          => [
                    'view'    => route('purchase.invoice', $purchase->id),
                    'delete'  => route('purchase.destroy', $purchase->id),
                    'gatepass'=> $hasGatepass ? route('InwardGatepass.show', $igp->id) : null,
                ],
            ];
        });

        return ['found' => true, 'type' => 'Purchase Invoice', 'icon' => 'fas fa-shopping-cart', 'color' => '#f59e0b', 'records' => $items->toArray()];
    }

    // ─── Outward Gatepass ─────────────────────────────────────────
    private function findOutwardGatepass(string $number, ?string $startDate, ?string $endDate, int $branchId, bool $isSuperAdmin): array
    {
        $query = DB::table('outward_gatepasses');

        if ($number !== '') {
            $query->where(function ($q) use ($number) {
                $q->where('gatepass_number', $number)
                  ->orWhere('gatepass_number', 'like', "%{$number}%")
                  ->orWhere('dc_no', $number)
                  ->orWhere('dc_no', 'like', "%{$number}%")
                  ->orWhere('invoice_no', $number)
                  ->orWhere('invoice_no', 'like', "%{$number}%");
            });
        }

        if ($startDate && $endDate) {
            $query->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $limit = ($number === '') ? 50 : 20;
        $records = $query->orderByDesc('id')->limit($limit)->get();

        if ($records->isEmpty()) {
            return ['found' => false, 'message' => 'No Outward Gatepass found with this number.'];
        }

        $branchNames = Branch::pluck('name', 'id')->toArray();

        $items = $records->map(function ($gp) use ($branchNames) {
            return [
                'id'            => $gp->id,
                'doc_number'    => $gp->gatepass_number ?? $gp->dc_no ?? 'N/A',
                'manual_number' => $gp->invoice_no,
                'date'          => $gp->created_at ? \Carbon\Carbon::parse($gp->created_at)->format('d-M-Y H:i') : '—',
                'party'         => $gp->customer_name ?? '—',
                'branch'        => $branchNames[$gp->branch_id] ?? '—',
                'amount'        => '—',
                'status'        => $gp->status ?? '—',
                'items_count'   => 0,
                'urls'          => [
                    'view'   => route('OutwardGatepass.show', $gp->id),
                    'pdf'    => route('OutwardGatepass.pdf', $gp->id),
                ],
            ];
        });

        return ['found' => true, 'type' => 'Outward Gatepass', 'icon' => 'fas fa-truck', 'color' => '#22c55e', 'records' => $items->toArray()];
    }

    // ─── Inward Gatepass ──────────────────────────────────────────
    private function findInwardGatepass(string $number, ?string $startDate, ?string $endDate, int $branchId, bool $isSuperAdmin): array
    {
        $query = InwardGatepass::with(['vendor', 'branch', 'purchase']);

        if ($number !== '') {
            $query->where(function ($q) use ($number) {
                $q->where('id', $number)
                  ->orWhere('gatepass_no', $number)
                  ->orWhere('gatepass_no', 'like', "%{$number}%");
            });
        }

        if ($startDate && $endDate) {
            $query->whereBetween(DB::raw('DATE(gatepass_date)'), [$startDate, $endDate]);
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $limit = ($number === '') ? 50 : 20;
        $records = $query->orderByDesc('id')->limit($limit)->get();

        if ($records->isEmpty()) {
            return ['found' => false, 'message' => 'No Inward Gatepass found with this number.'];
        }

        $items = $records->map(function ($gp) {
            return [
                'id'            => $gp->id,
                'doc_number'    => $gp->gatepass_no ? $gp->gatepass_no . ' (#' . str_pad($gp->id, 4, '0', STR_PAD_LEFT) . ')' : '#' . str_pad($gp->id, 4, '0', STR_PAD_LEFT),
                'manual_number' => null,
                'date'          => $gp->gatepass_date ? \Carbon\Carbon::parse($gp->gatepass_date)->format('d-M-Y') : '—',
                'party'         => optional($gp->vendor)->name ?? '—',
                'branch'        => optional($gp->branch)->name ?? '—',
                'amount'        => '—',
                'status'        => $gp->status ?? '—',
                'items_count'   => 0,
                'urls'          => [
                    'view'   => route('InwardGatepass.show', $gp->id),
                    'edit'   => route('InwardGatepass.edit', $gp->id),
                    'delete' => route('InwardGatepass.destroy', $gp->id),
                    'pdf'    => route('InwardGatepass.pdf', $gp->id),
                ],
            ];
        });

        return ['found' => true, 'type' => 'Inward Gatepass', 'icon' => 'fas fa-door-open', 'color' => '#0ea5e9', 'records' => $items->toArray()];
    }
}
