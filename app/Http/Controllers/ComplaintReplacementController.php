<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Complaint;
use App\Models\ComplaintReplacement;
use App\Models\DamagedStock;
use App\Models\DamagedStockTransfer;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Concerns\BranchScope;

class ComplaintReplacementController extends Controller
{
    use BranchScope;

    /**
     * Fetch stock locations for a product inside a branch
     */
    public function getProductLocationsStock(Request $request)
    {
        $productId = $request->input('product_id');
        $branchId  = $request->input('branch_id');

        if (!$productId || !$branchId) {
            return response()->json([]);
        }

        $branch = Branch::with('warehouses')->find($branchId);
        if (!$branch) {
            return response()->json([]);
        }

        $locations = [];

        // 1. Branch shop direct stock (warehouse_id IS NULL)
        $shopStock = WarehouseStock::where('branch_id', $branchId)
            ->whereNull('warehouse_id')
            ->where('product_id', $productId)
            ->first();

        $locations[] = [
            'type'     => 'shop',
            'id'       => null,
            'label'    => '🏪 Shop Direct — ' . ($branch->name ?? 'Shop'),
            'quantity' => $shopStock ? (float) $shopStock->quantity : 0.0,
        ];

        // 2. Each warehouse assigned to this branch
        foreach ($branch->warehouses as $warehouse) {
            $whStock = WarehouseStock::where('branch_id', $branchId)
                ->where('warehouse_id', $warehouse->id)
                ->where('product_id', $productId)
                ->first();

            $locations[] = [
                'type'     => 'warehouse',
                'id'       => $warehouse->id,
                'label'    => '📦 ' . ($warehouse->warehouse_name ?? 'Warehouse'),
                'quantity' => $whStock ? (float) $whStock->quantity : 0.0,
            ];
        }

        return response()->json($locations);
    }

    /**
     * Store replacement transaction
     */
    public function store(Request $request)
    {
        $request->validate([
            'complaint_id'                 => 'required|exists:complaints,id',
            'issued_product_id'            => 'required|exists:products,id',
            'quantity'                     => 'required|numeric|min:0.001',
            'source_location'              => 'required|string', // Format: "shop_0" or "warehouse_ID"
            'collect_damaged'              => 'nullable|boolean',
            'collected_damaged_product_id' => 'required_if:collect_damaged,1|nullable|exists:products,id',
            'damaged_qty'                  => 'required_if:collect_damaged,1|nullable|numeric|min:0',
        ]);

        $complaint = Complaint::findOrFail($request->complaint_id);
        $branchId  = $complaint->branch_id;
        $productId = $request->issued_product_id;
        $qty       = (float) $request->quantity;

        // Parse location source
        $parts        = explode('_', $request->source_location, 2);
        $sourceType   = $parts[0]; // 'shop' or 'warehouse'
        $sourceLocId  = isset($parts[1]) ? (int) $parts[1] : null;

        try {
            DB::transaction(function () use ($request, $complaint, $branchId, $productId, $qty, $sourceType, $sourceLocId) {
                
                // 1. Fetch and Lock source stock
                $sourceStock = null;
                if ($sourceType === 'warehouse') {
                    $sourceStock = WarehouseStock::lockForUpdate()
                        ->where('branch_id', $branchId)
                        ->where('warehouse_id', $sourceLocId)
                        ->where('product_id', $productId)
                        ->first();
                } else {
                    $sourceStock = WarehouseStock::lockForUpdate()
                        ->where('branch_id', $branchId)
                        ->whereNull('warehouse_id')
                        ->where('product_id', $productId)
                        ->first();
                }

                if (!$sourceStock || $sourceStock->quantity < $qty) {
                    $prodName = Product::find($productId)?->item_name ?? "Product #{$productId}";
                    throw new \Exception("Insufficient stock for product: {$prodName} (Requested: {$qty}, Available: " . ($sourceStock?->quantity ?? 0) . ")");
                }

                // 2. Decrement source stock
                $sourceStock->quantity -= $qty;
                $sourceStock->save();

                // 3. Create Complaint Replacement Log
                $replacement = ComplaintReplacement::create([
                    'complaint_id'                 => $complaint->id,
                    'issued_product_id'            => $productId,
                    'quantity'                     => $qty,
                    'source_location_type'         => $sourceType,
                    'source_warehouse_id'          => $sourceType === 'warehouse' ? $sourceLocId : null,
                    'collected_damaged_product_id' => $request->collect_damaged ? $request->collected_damaged_product_id : null,
                    'damaged_qty'                  => $request->collect_damaged ? (float) $request->damaged_qty : 0.0,
                    'damaged_status'               => $request->collect_damaged ? 'retained_at_shop' : 'none',
                    'created_by'                   => Auth::id(),
                ]);

                // 4. Record stock movement audit trail for issued clean stock
                $locLabel = $sourceType === 'warehouse' ? "Warehouse #{$sourceLocId}" : "Shop Direct";
                StockMovement::create([
                    'product_id' => $productId,
                    'type'       => 'out',
                    'qty'        => $qty,
                    'ref_type'   => 'COMPLAINT_REPLACEMENT',
                    'ref_id'     => $replacement->id,
                    'note'       => "Replacement part/product issued for Complaint #{$complaint->complaint_no} from {$locLabel}",
                ]);

                // 5. If damaged part is collected, increment shop-retained damaged stock
                if ($request->collect_damaged && $request->collected_damaged_product_id) {
                    $damagedStock = DamagedStock::firstOrCreate([
                        'branch_id'    => $branchId,
                        'warehouse_id' => null, // Shop level
                        'product_id'   => $request->collected_damaged_product_id,
                        'is_part'      => $request->is_collected_part ? 1 : 0,
                        'part_name'    => $request->is_collected_part ? $request->collected_part_name : null,
                    ], [
                        'quantity' => 0.0
                    ]);

                    $damagedStock->quantity += (float) $request->damaged_qty;
                    $damagedStock->save();
                }
            });

            return redirect()->back()->with('success', '✅ Replacement item successfully issued from stock!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', '❌ ' . $e->getMessage())->withInput();
        }
    }

    /**
     * List all damaged stock inventory
     */
    public function damagedIndex(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super admin');
        $allowedBranchIds = $this->allowedBranches('warehouse.stock.view');

        $query = DamagedStock::with(['branch', 'warehouse', 'product'])
            ->whereIn('branch_id', $allowedBranchIds);

        // Filters
        if ($request->filled('branch_id') && $isSuperAdmin) {
            $query->where('branch_id', $request->branch_id);
        } elseif (!$isSuperAdmin) {
            $query->where('branch_id', $user->branch_id);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $damagedStocks = $query->orderByDesc('updated_at')->paginate(20);

        // Branches and Warehouses for transfers
        $branches = $isSuperAdmin ? Branch::all() : collect();
        
        // Show warehouses assigned or accessible to branches
        $warehouses = Warehouse::all();
        $products   = Product::orderBy('item_name')->get();

        return view('admin_panel.complaints.damaged_stock.index', compact('damagedStocks', 'branches', 'warehouses', 'products', 'isSuperAdmin'));
    }

    /**
     * Transfer damaged stock to warehouse
     */
    public function transferDamaged(Request $request)
    {
        $request->validate([
            'damaged_stock_id' => 'required|exists:damaged_stocks,id',
            'to_warehouse_id'  => 'required|exists:warehouses,id',
            'transfer_qty'     => 'required|numeric|min:0.001',
            'remarks'          => 'nullable|string',
        ]);

        $damagedStockId = $request->damaged_stock_id;
        $toWarehouseId  = $request->to_warehouse_id;
        $transferQty    = (float) $request->transfer_qty;

        try {
            DB::transaction(function () use ($damagedStockId, $toWarehouseId, $transferQty, $request) {
                
                // 1. Lock and fetch source shop damaged stock (warehouse_id must be NULL)
                $sourceDamaged = DamagedStock::lockForUpdate()->findOrFail($damagedStockId);

                if ($sourceDamaged->warehouse_id !== null) {
                    throw new \Exception("Can only transfer damaged items that are currently held at a branch shop.");
                }

                if ($sourceDamaged->quantity < $transferQty) {
                    throw new \Exception("Insufficient damaged pieces available to transfer. (Available: {$sourceDamaged->quantity}, Requested: {$transferQty})");
                }

                // 2. Decrement source damaged stock
                $sourceDamaged->quantity -= $transferQty;
                $sourceDamaged->save();

                // 3. Increment destination warehouse damaged stock
                $destDamaged = DamagedStock::firstOrCreate([
                    'branch_id'    => $sourceDamaged->branch_id,
                    'warehouse_id' => $toWarehouseId,
                    'product_id'   => $sourceDamaged->product_id,
                    'is_part'      => $sourceDamaged->is_part,
                    'part_name'    => $sourceDamaged->part_name,
                ], [
                    'quantity' => 0.0
                ]);

                $destDamaged->quantity += $transferQty;
                $destDamaged->save();

                // 4. Create Damaged Stock Transfer Audit log
                $transfer = DamagedStockTransfer::create([
                    'branch_id'       => $sourceDamaged->branch_id,
                    'product_id'      => $sourceDamaged->product_id,
                    'is_part'         => $sourceDamaged->is_part,
                    'part_name'       => $sourceDamaged->part_name,
                    'quantity'        => $transferQty,
                    'to_warehouse_id' => $toWarehouseId,
                    'remarks'         => $request->remarks,
                    'created_by'      => Auth::id(),
                ]);

                // 5. Update parent complaint replacements status to transferred_to_warehouse where appropriate
                ComplaintReplacement::where('collected_damaged_product_id', $sourceDamaged->product_id)
                    ->where('is_collected_part', $sourceDamaged->is_part)
                    ->where('collected_part_name', $sourceDamaged->part_name)
                    ->where('damaged_status', 'retained_at_shop')
                    ->whereHas('complaint', function ($q) use ($sourceDamaged) {
                        $q->where('branch_id', $sourceDamaged->branch_id);
                    })
                    ->limit((int) ceil($transferQty)) // approximate matching
                    ->update([
                        'damaged_status'             => 'transferred_to_warehouse',
                        'transferred_warehouse_id' => $toWarehouseId
                    ]);
            });

            return redirect()->back()->with('success', '✅ Damaged stock successfully transferred to warehouse!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', '❌ ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Claim replacement and release clean stock from shop/warehouse inventory
     */
    public function claimReplacement(Request $request, $id)
    {
        $request->validate([
            'issued_product_id' => 'required|exists:products,id',
            'quantity'          => 'required|numeric|min:0.001',
            'source_location'   => 'required|string', // Format: "shop_0" or "warehouse_ID"
        ]);

        $replacement = ComplaintReplacement::findOrFail($id);
        $complaint   = Complaint::findOrFail($replacement->complaint_id);
        $branchId    = $complaint->branch_id;
        $productId   = $request->issued_product_id;
        $qty         = (float) $request->quantity;

        // Parse location source
        $parts        = explode('_', $request->source_location, 2);
        $sourceType   = $parts[0]; // 'shop' or 'warehouse'
        $sourceLocId  = isset($parts[1]) ? (int) $parts[1] : null;

        try {
            DB::transaction(function () use ($replacement, $complaint, $branchId, $productId, $qty, $sourceType, $sourceLocId) {
                if ($replacement->claim_status !== 'pending') {
                    throw new \Exception("This replacement slip has already been claimed.");
                }

                // 1. Fetch and Lock source stock
                $sourceStock = null;
                if ($sourceType === 'warehouse') {
                    $sourceStock = WarehouseStock::lockForUpdate()
                        ->where('branch_id', $branchId)
                        ->where('warehouse_id', $sourceLocId)
                        ->where('product_id', $productId)
                        ->first();
                } else {
                    $sourceStock = WarehouseStock::lockForUpdate()
                        ->where('branch_id', $branchId)
                        ->whereNull('warehouse_id')
                        ->where('product_id', $productId)
                        ->first();
                }

                if (!$sourceStock || $sourceStock->quantity < $qty) {
                    $prodName = Product::find($productId)?->item_name ?? "Product #{$productId}";
                    throw new \Exception("Insufficient stock for product: {$prodName} (Requested: {$qty}, Available: " . ($sourceStock?->quantity ?? 0) . ")");
                }

                // 2. Decrement source stock
                $sourceStock->quantity -= $qty;
                $sourceStock->save();

                // 3. Update Replacement Slip record
                $replacement->update([
                    'issued_product_id'    => $productId,
                    'quantity'             => $qty,
                    'source_location_type' => $sourceType,
                    'source_warehouse_id'  => $sourceType === 'warehouse' ? $sourceLocId : null,
                    'claim_status'         => 'claimed',
                    'claimed_at'           => now(),
                    'claimed_by'           => Auth::id(),
                ]);

                // 4. Record stock movement audit trail for issued clean stock
                $locLabel = $sourceType === 'warehouse' ? "Warehouse #{$sourceLocId}" : "Shop Direct";
                StockMovement::create([
                    'product_id' => $productId,
                    'type'       => 'out',
                    'qty'        => $qty,
                    'ref_type'   => 'COMPLAINT_REPLACEMENT',
                    'ref_id'     => $replacement->id,
                    'note'       => "Replacement part/product claimed & issued for Complaint #{$complaint->complaint_no} via Slip {$replacement->replacement_slip_no} from {$locLabel}",
                ]);
            });

            return redirect()->back()->with('success', '✅ Replacement slip successfully claimed and clean item issued from stock!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', '❌ ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Print the replacement claim slip for the customer
     */
    public function printReplacementSlip($id)
    {
        $replacement = ComplaintReplacement::with([
            'complaint.branch',
            'issuedProduct',
            'collectedDamagedProduct',
            'createdByUser',
            'claimedByUser',
        ])->findOrFail($id);

        return view('admin_panel.complaints.print_replacement_slip', compact('replacement'));
    }
}

