<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\InwardGatepass;
use App\Models\InwardGatepassItem;
use App\Models\Stock;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Warehouse;
use App\Models\Vendor;
use App\Models\Purchase;
use App\Models\VendorRemaining;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class InwardgatepassController extends Controller
{
    public function pdf($id)
    {
        $gatepass = InwardGatepass::with(['branch','warehouse','vendor','items.product'])->findOrFail($id);
        $pdf = Pdf::loadView('admin_panel.inward.pdf', compact('gatepass'));
        return $pdf->download('gatepass_'.$gatepass->id.'.pdf');
    }

    // LIST - Show pending items for each gatepass (ERP standard)
    public function index()
    {
        $gatepasses = InwardGatepass::with('items.product','branch','warehouse','vendor')->latest()->get();
        
        // For each gatepass, calculate pending items from related purchase and determine display status
        $gatepasses = $gatepasses->map(function ($gp) {
            if ($gp->purchase_id) {
                $gp->pending_count = VendorRemaining::where('purchase_id', $gp->purchase_id)
                    ->pending()
                    ->sum('remaining_qty');
                
                // ✅ ERP Standard: Determine display status based on completion
                // If all items received (no pending), show "Completed" regardless of DB status
                if ($gp->pending_count == 0) {
                    $gp->display_status = 'completed';
                } else {
                    // Otherwise show "Pending" for active purchases
                    $gp->display_status = $gp->status == 'cancelled' ? 'cancelled' : 'pending';
                }
            } else {
                $gp->pending_count = 0;
                // Non-purchase gatepass - use DB status
                $gp->display_status = $gp->status;
            }
            return $gp;
        });
        
        return view('admin_panel.inward.index', compact('gatepasses'));
    }

    // CREATE FORM
    public function create()
    {
        $branches   = Branch::orderBy('name')->get();
        $isSuperAdmin = Auth::user()->hasRole('super admin');
        
        // ✅ ERP STANDARD: Filter warehouses by user's branch
        $userBranchId = Auth::user()->branch_id ?? 1;
        $warehouses = Warehouse::whereHas('branches', function($q) use ($userBranchId) {
            $q->where('branch_id', $userBranchId);
        })->orderBy('warehouse_name')->get();
        
        $vendors    = Vendor::orderBy('name')->get();
        $purchase   = null;
        $vendorRemaining = collect();  // Empty collection for non-purchase gatepass
        return view('admin_panel.inward.create', compact('branches','warehouses','vendors','purchase','vendorRemaining','isSuperAdmin'));
    }

    // CREATE FROM PURCHASE - International ERP Standard Workflow
    // After creating a Purchase, user creates corresponding Inward Gatepass to receive goods
    public function createFromPurchase($purchaseId)
    {
        // ✅ Fetch purchase with all relationships
        $purchase = Purchase::with([
            'items.product.brand',
            'items.product.unit',
            'vendor',
            'branch',
            'warehouse'
        ])->findOrFail($purchaseId);
        
        // ✅ CRITICAL FIX: Fetch vendor_remaining for partial delivery tracking
        // This tells us what's already been received vs what's still pending
        $vendorRemaining = VendorRemaining::where('purchase_id', $purchaseId)
            ->get()
            ->keyBy('product_id');  // Index by product_id for easy lookup
        
        // Debug: Log what we're getting
        \Log::info('InwardGatepass - createFromPurchase:', [
            'purchase_id' => $purchase->id,
            'items_count' => $purchase->items?->count() ?? 0,
            'remaining_items' => $vendorRemaining->count(),
        ]);
        
        $branches   = Branch::orderBy('name')->get();
        $vendors    = Vendor::orderBy('name')->get();
        $isSuperAdmin = Auth::user()->hasRole('super admin');
        
        // ✅ ERP STANDARD: Filter warehouses by purchase's branch
        $warehouses = Warehouse::whereHas('branches', function($q) use ($purchase) {
            $q->where('branch_id', $purchase->branch_id);
        })->orderBy('warehouse_name')->get();

        return view('admin_panel.inward.create', compact('branches','warehouses','vendors','purchase','vendorRemaining','isSuperAdmin'));
    }

    // STORE (movements + stocks)
    public function store(Request $request)
    {
        $request->validate([
            'branch_id'      => 'required|exists:branches,id',
            'warehouse_id'   => 'required|exists:warehouses,id',
            'vendor_id'      => 'required|exists:vendors,id',
            'purchase_id'    => 'nullable|exists:purchases,id',
            'gatepass_date'  => 'required|date',
            'product_id'     => 'required|array|min:1',
            'product_id.*'   => 'required|exists:products,id',
            'received_qty'   => 'required|array',
            'received_qty.*' => 'required|numeric|min:1',
            'note'           => 'nullable|string|max:200',
            'transport_name' => 'nullable|string|max:200',
            'bilty_no'       => 'nullable|string|max:100',
        ]);

        // ✅ ERP VALIDATION: Prevent receiving more than ordered (for purchases)
        $purchaseId = $request->purchase_id;
        if ($purchaseId) {
            $purchase = \App\Models\Purchase::findOrFail($purchaseId);
            $pids = $request->input('product_id', []);
            $receivedQtys = $request->input('received_qty', []);

            for ($i = 0; $i < count($pids); $i++) {
                $pid = (int)($pids[$i] ?? 0);
                $receivedQty = (float)($receivedQtys[$i] ?? 0);
                if (!$pid) continue;

                // Get ordered qty from purchase items
                $purchaseItem = \App\Models\PurchaseItem::where('purchase_id', $purchaseId)
                    ->where('product_id', $pid)
                    ->first();

                if (!$purchaseItem) {
                    return back()->with('error', "Product {$pid} not found in this purchase.");
                }

                // Get already received qty from vendor_remaining
                $vendorRemaining = \App\Models\VendorRemaining::where('purchase_id', $purchaseId)
                    ->where('product_id', $pid)
                    ->first();

                $alreadyReceivedQty = $vendorRemaining->received_qty ?? 0;
                $orderedQty = $purchaseItem->qty;
                $totalReceivedWithThis = $alreadyReceivedQty + $receivedQty;

                // ❌ Cannot receive more than ordered
                if ($totalReceivedWithThis > $orderedQty) {
                    $product = \App\Models\Product::find($pid);
                    $productName = $product->name ?? "Product {$pid}";
                    $maxCanReceive = $orderedQty - $alreadyReceivedQty;
                    return back()->with('error', 
                        "❌ {$productName}: Already received {$alreadyReceivedQty} units. " .
                        "Ordered total: {$orderedQty}. " .
                        "Can receive max {$maxCanReceive} more units."
                    );
                }
            }
        }

        DB::transaction(function () use ($request) {
            $gatepass = InwardGatepass::create([
                'branch_id'      => $request->branch_id,
                'warehouse_id'   => $request->warehouse_id,
                'vendor_id'      => $request->vendor_id,
                'purchase_id'    => $request->purchase_id,
                'gatepass_date'  => $request->gatepass_date,
                'note'           => $request->note,
                'transport_name' => $request->transport_name,
                'bilty_no'       => $request->bilty_no,
                'created_by'     => auth()->id(),
                'status'         => 'pending',
            ]);

            $pids = $request->input('product_id', []);
            $receivedQtys = $request->input('received_qty', []);
            $purchaseId = $request->purchase_id;

            $now = now();
            $movementRows = [];

            for ($i=0; $i<count($pids); $i++) {
                $pid = (int)($pids[$i] ?? 0);
                $receivedQty = (float)($receivedQtys[$i] ?? 0);
                if (!$pid || $receivedQty <= 0) continue;

                // Create inward gatepass item with received qty
                InwardGatepassItem::create([
                    'inward_gatepass_id' => $gatepass->id,
                    'product_id'         => $pid,
                    'qty'                => $receivedQty,
                ]);

                // movement (+) - only for received qty
                $movementRows[] = [
                    'product_id' => $pid,
                    'branch_id'  => (int)$request->branch_id,  // ✅ ERP STANDARD: Track branch
                    'type'       => 'in',
                    'qty'        => $receivedQty,
                    'ref_type'   => 'INWARD',
                    'ref_id'     => $gatepass->id,
                    'note'       => 'Inward gatepass',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                // stocks upsert - only for received qty
                $this->upsertStocks($pid, +$receivedQty, (int)$request->branch_id, (int)$request->warehouse_id);

                // Handle partial delivery tracking if from purchase
                if ($purchaseId) {
                    $this->handleVendorRemaining(
                        $purchaseId,
                        $pid,
                        $receivedQty,
                        (int)$request->vendor_id,
                        (int)$request->warehouse_id
                    );
                }
            }

            if (!empty($movementRows)) {
                DB::table('stock_movements')->insert($movementRows);
            }
        });

        return redirect()->route('InwardGatepass.home')
                         ->with('success','Inward Gatepass Created Successfully');
    }

    /**
     * Handle vendor remaining tracking for partial deliveries
     */
    private function handleVendorRemaining(
        int $purchaseId,
        int $productId,
        float $receivedQty,
        int $vendorId,
        int $warehouseId
    ): void {
        // Get the original ordered qty from purchase_items
        $purchaseItem = DB::table('purchase_items')
            ->where('purchase_id', $purchaseId)
            ->where('product_id', $productId)
            ->first();

        if (!$purchaseItem) return;

        $orderedQty = $purchaseItem->qty;

        // Check if vendor_remaining record exists
        $existing = VendorRemaining::where('purchase_id', $purchaseId)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            // Update existing record
            $newReceivedQty = $existing->received_qty + $receivedQty;
            $newRemainingQty = $orderedQty - $newReceivedQty;
            $newStatus = $newRemainingQty <= 0 ? 'completed' : ($newReceivedQty > 0 ? 'partial' : 'pending');

            $existing->update([
                'received_qty'  => $newReceivedQty,
                'remaining_qty' => max(0, $newRemainingQty),
                'status'        => $newStatus,
            ]);
        } else {
            // Create new record
            $remainingQty = $orderedQty - $receivedQty;
            $status = $remainingQty <= 0 ? 'completed' : 'pending';

            VendorRemaining::create([
                'purchase_id'   => $purchaseId,
                'vendor_id'     => $vendorId,
                'product_id'    => $productId,
                'warehouse_id'  => $warehouseId,
                'ordered_qty'   => $orderedQty,
                'received_qty'  => $receivedQty,
                'remaining_qty' => max(0, $remainingQty),
                'status'        => $status,
            ]);
        }
    }

    // SHOW - International ERP Standard with Pending Deliveries
    public function show($id)
    {
        $gatepass = InwardGatepass::with('items.product','branch','warehouse','vendor')->findOrFail($id);
        
        // Get pending items from same purchase (ERP standard: show remaining items)
        $pendingItems = [];
        if ($gatepass->purchase_id) {
            $pendingItems = VendorRemaining::with(['product', 'warehouse'])
                ->where('purchase_id', $gatepass->purchase_id)
                ->pending()
                ->get();
            
            // ✅ ERP Standard: Calculate display status based on completion
            $pendingCount = $pendingItems->sum('remaining_qty');
            if ($pendingCount == 0) {
                $gatepass->display_status = 'completed';
            } else {
                $gatepass->display_status = $gatepass->status == 'cancelled' ? 'cancelled' : 'pending';
            }
        } else {
            $gatepass->display_status = $gatepass->status;
        }
        
        return view('admin_panel.inward.show', compact('gatepass', 'pendingItems'));
    }

    // EDIT FORM
    public function edit($id)
    {
        $gatepass   = InwardGatepass::with('items')->findOrFail($id);
        $branches   = Branch::orderBy('name')->get();
        $isSuperAdmin = Auth::user()->hasRole('super admin');
        
        // ✅ ERP STANDARD: Filter warehouses by gatepass's branch
        $warehouses = Warehouse::whereHas('branches', function($q) use ($gatepass) {
            $q->where('branch_id', $gatepass->branch_id);
        })->orderBy('warehouse_name')->get();
        
        $vendors    = Vendor::orderBy('name')->get();
        return view('admin_panel.inward.edit', compact('gatepass','branches','warehouses','vendors','isSuperAdmin'));
    }

    // UPDATE (delta movements + stocks)
    public function update(Request $request, $id)
    {
        $request->validate([
            'branch_id'      => 'required|exists:branches,id',
            'warehouse_id'   => 'required|exists:warehouses,id',
            'vendor_id'      => 'required|exists:vendors,id',
            'gatepass_date'  => 'required|date',
            'product_id'     => 'required|array|min:1',
            'product_id.*'   => 'required|exists:products,id',
            'qty'            => 'required|array',
            'qty.*'          => 'required|numeric|min:1',
            'note'           => 'nullable|string|max:200',
            'transport_name' => 'nullable|string|max:200',
        ]);

        DB::transaction(function () use ($request, $id) {
            $gatepass = InwardGatepass::with('items')->findOrFail($id);
            $oldBranch = (int)$gatepass->branch_id;
            $oldWh     = (int)$gatepass->warehouse_id;

            // old totals per product
            $oldMap = $gatepass->items->groupBy('product_id')->map(fn($g)=> (float)$g->sum('qty'));

            // new items map
            $pids = $request->input('product_id', []);
            $qtys = $request->input('qty', []);
            $newMap = collect();
            for ($i=0; $i<count($pids); $i++) {
                $pid = (int)($pids[$i] ?? 0);
                $q   = (float)($qtys[$i] ?? 0);
                if (!$pid || $q<=0) continue;
                $newMap[$pid] = ($newMap[$pid] ?? 0) + $q;
            }

            // header update
            $gatepass->update([
                'branch_id'      => $request->branch_id,
                'warehouse_id'   => $request->warehouse_id,
                'vendor_id'      => $request->vendor_id,
                'gatepass_date'  => $request->gatepass_date,
                'note'           => $request->note,
                'transport_name' => $request->transport_name,
            ]);

            // replace items
            InwardGatepassItem::where('inward_gatepass_id', $gatepass->id)->delete();
            foreach ($newMap as $pid => $q) {
                InwardGatepassItem::create([
                    'inward_gatepass_id' => $gatepass->id,
                    'product_id'         => $pid,
                    'qty'                => $q,
                ]);
            }

            // deltas
            $now = now();
            $movs = [];
            $allKeys = $oldMap->keys()->merge($newMap->keys())->unique();

            foreach ($allKeys as $pid) {
                $oldQ = (float)($oldMap[$pid] ?? 0);
                $newQ = (float)($newMap[$pid] ?? 0);
                $delta = $newQ - $oldQ;
                if ($delta == 0) continue;

                $type = $delta > 0 ? 'in' : 'out';
                $qty  = abs($delta);

                $movs[] = [
                    'product_id' => (int)$pid,
                    'branch_id'  => (int)$request->branch_id,  // ✅ ERP STANDARD: Track branch
                    'type'       => $type,
                    'qty'        => $qty,
                    'ref_type'   => 'INWARD_EDIT',
                    'ref_id'     => $gatepass->id,
                    'note'       => 'Inward edit delta',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                // stocks adjust on NEW branch/wh (simple approach).
                // If branch/wh changed, you may also want to reverse old and add to new; for now we apply on new header.
                $this->upsertStocks((int)$pid, ($type==='in' ? +$qty : -$qty), (int)$request->branch_id, (int)$request->warehouse_id);
            }

            if (!empty($movs)) {
                DB::table('stock_movements')->insert($movs);
            }

            // ✅ CRITICAL: Update vendor_remaining for partial delivery tracking
            if ($gatepass->purchase_id) {
                foreach ($allKeys as $pid) {
                    $oldQ = (float)($oldMap[$pid] ?? 0);
                    $newQ = (float)($newMap[$pid] ?? 0);
                    $delta = $newQ - $oldQ;
                    if ($delta == 0) continue;  // Skip if no change

                    // Call handleVendorRemaining with delta
                    // This will ADD the delta to received_qty and recalculate remaining
                    $this->handleVendorRemaining(
                        (int)$gatepass->purchase_id,
                        (int)$pid,
                        $delta,
                        (int)$request->vendor_id,
                        (int)$request->warehouse_id
                    );
                }
            }
        });

        return redirect()->route('InwardGatepass.home')->with('success','Inward Gatepass Updated Successfully');
    }

    // DELETE (reverse movements + stocks)
    public function destroy($id)
{
    DB::transaction(function () use ($id) {
        $gatepass = InwardGatepass::with('items')->findOrFail($id);
        $now = now();
        $movs = [];

        foreach ($gatepass->items as $item) {
            // log reverse movement
            $movs[] = [
                'product_id' => (int)$item->product_id,
                'branch_id'  => (int)$gatepass->branch_id,  // ✅ ERP STANDARD: Track branch
                'type'       => 'out',
                'qty'        => (float)$item->qty,
                'ref_type'   => 'INWARD_DELETE',
                'ref_id'     => $gatepass->id,
                'note'       => 'Delete inward (reverse)',
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Direct stock rollback - update both warehouse_stocks and stocks tables
            
            // STEP 1: Rollback warehouse_stocks
            $warehouseStock = DB::table('warehouse_stocks')
                ->where('product_id', $item->product_id)
                ->where('branch_id', $gatepass->branch_id)
                ->where('warehouse_id', $gatepass->warehouse_id)
                ->first();

            if ($warehouseStock) {
                $newQty = max(0, $warehouseStock->quantity - $item->qty);
                DB::table('warehouse_stocks')
                    ->where('id', $warehouseStock->id)
                    ->update([
                        'quantity'   => $newQty,
                        'updated_at' => $now,
                    ]);
            }

            // STEP 2: Rollback stocks (also deduct - branch total only)
            $stock = DB::table('stocks')
                ->where('product_id', $item->product_id)
                ->where('branch_id', $gatepass->branch_id)
                ->first();

            if ($stock) {
                $newStockQty = max(0, $stock->qty - $item->qty);
                DB::table('stocks')
                    ->where('id', $stock->id)
                    ->update([
                        'qty'        => $newStockQty,
                        'updated_at' => $now,
                    ]);
            }
        }

        if (!empty($movs)) {
            DB::table('stock_movements')->insert($movs);
        }

        InwardGatepassItem::where('inward_gatepass_id', $gatepass->id)->delete();
        $gatepass->delete();
    });

    return redirect()->route('InwardGatepass.home')
                     ->with('success','Inward Gatepass Deleted Successfully');
}

    // PRODUCT SEARCH (grouped where fix)
    public function searchProducts(Request $request)
    {
        $q = $request->get('q','');
        $query = Product::with('brand');
        
        // Apply branch filter for non-super admins
        if (Auth::check() && !Auth::user()->hasRole('super admin')) {
            $branchId = Auth::user()->branch_id ?? 0;
            $query->where('branch_id', $branchId);
        }
        
        $products = $query
            ->where(function($x) use ($q){
                $x->where('item_name','like',"%{$q}%")
                  ->orWhere('item_code','like',"%{$q}%");
            })
            ->limit(10)
            ->get();

        // Add ownership information
        $userBranchId = Auth::check() ? Auth::user()->branch_id : null;
        $productsWithOwnership = $products->map(function($p) use ($userBranchId) {
            return [
                'id' => $p->id,
                'item_name' => $p->item_name,
                'item_code' => $p->item_code,
                'brand' => $p->brand,
                'price' => $p->price,
                'retail_price' => $p->retail_price ?? $p->price,
                'branch_id' => $p->branch_id,
                'is_owner' => ($userBranchId && $p->branch_id == $userBranchId),
            ];
        });

        return response()->json($productsWithOwnership);
    }

    // THERMAL PRINT - Compact receipt format for 80mm thermal printer
    public function thermal($id)
    {
        $gatepass = InwardGatepass::with(['branch','warehouse','vendor','items.product'])->findOrFail($id);
        return view('admin_panel.inward.thermal', compact('gatepass'));
    }

    // --- small helper (same as ProductController) ---
    /**
     * Update both warehouse_stocks and stocks tables (ERP standard - dual sync)
     * 
     * warehouse_stocks = Detail table (warehouse-specific inventory)
     * stocks = Summary table (branch-total inventory)
     * 
     * When qty changes: old + new = total (additive)
     */
    private function upsertStocks(int $productId, float $qtyDelta, int $branchId, int $warehouseId): void
    {
        // ═══════════════════════════════════════════════════════════
        // STEP 1: Update warehouse_stocks (detail table)
        // ═══════════════════════════════════════════════════════════
        $affectedWarehouse = DB::table('warehouse_stocks')
            ->where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->where('warehouse_id', $warehouseId)
            ->update([
                'quantity'   => DB::raw('quantity + '.((int)$qtyDelta)),
                'updated_at' => now(),
            ]);

        // Create warehouse_stocks record if doesn't exist
        if ($affectedWarehouse === 0) {
            DB::table('warehouse_stocks')->insert([
                'branch_id'    => $branchId,
                'warehouse_id' => $warehouseId,
                'product_id'   => $productId,
                'quantity'     => (int)$qtyDelta,
                'price'        => null,
                'remarks'      => 'Inward gatepass stock',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // ═══════════════════════════════════════════════════════════
        // STEP 2: Update stocks (summary table - branch total only)
        // Note: stocks table aggregates ALL warehouses in branch
        // Columns: branch_id, product_id, qty (NO warehouse_id)
        // ═══════════════════════════════════════════════════════════
        $affectedStocks = DB::table('stocks')
            ->where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->update([
                'qty'        => DB::raw('qty + '.((int)$qtyDelta)),
                'updated_at' => now(),
            ]);

        // Create stocks record if doesn't exist
        if ($affectedStocks === 0) {
            DB::table('stocks')->insert([
                'branch_id'    => $branchId,
                'product_id'   => $productId,
                'qty'          => (int)$qtyDelta,
                'reserved_qty' => 0,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }
}
