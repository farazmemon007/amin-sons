<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Branch;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Http\Controllers\Concerns\BranchScope;

class StockTransferController extends Controller
{
    use BranchScope;
    public function index() {
        $transfers = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'fromBranch', 'toBranch', 'product'])->get();
        return view('admin_panel.warehouses.stock_transfers.index', compact('transfers'));
    }

    public function create() {
        $allowedBranchIds = $this->allowedBranches('stock-transfer.view');

        // Warehouses the user can use (by branch access)
        $warehouses = Warehouse::whereHas('branches', function ($q) use ($allowedBranchIds) {
            $q->whereIn('branches.id', $allowedBranchIds);
        })->get();

        // Branch selection
        $branches = Branch::whereIn('id', $allowedBranchIds)->get();
        $currentBranchId = auth()->user()->branch_id ?? null;
        
        // ✅ ERP STANDARD: Track if user is super admin (needs explicit branch selection)
        $isSuperAdmin = auth()->user()->hasRole('super admin');

        // ✅ GET ALL PRODUCTS AND CATEGORIZE BY CURRENT BRANCH AVAILABILITY
        $allProducts = Product::all();
        
        // Get product IDs that have stock in current branch
        $currentBranchProductIds = [];
        if ($currentBranchId) {
            $currentBranchProductIds = DB::table('warehouse_stocks')
                ->where('branch_id', $currentBranchId)
                ->whereNull('warehouse_id')
                ->where('quantity', '>', 0)
                ->pluck('product_id')
                ->toArray();
        }
        
        // Separate products into primary (in current branch) and secondary (not in current branch)
        $primaryProducts = $allProducts->filter(function($product) use ($currentBranchProductIds) {
            return in_array($product->id, $currentBranchProductIds);
        });
        
        $secondaryProducts = $allProducts->filter(function($product) use ($currentBranchProductIds) {
            return !in_array($product->id, $currentBranchProductIds);
        });
        
        $products = $allProducts; // Keep for backward compatibility
        
        return view('admin_panel.warehouses.stock_transfers.create', compact(
            'warehouses', 
            'products', 
            'branches', 
            'currentBranchId', 
            'isSuperAdmin',
            'primaryProducts',
            'secondaryProducts'
        ));
    }

    public function store(Request $request)
    {
        // ✅ ERP STANDARD: Determine branch_id
        if (auth()->user()->hasRole('super admin')) {
            $branchId = (int) $request->input('branch_id');
            if (!$branchId) {
                return back()->with('error', 'Admin must select a branch to perform transfer.')->withInput();
            }
        } else {
            $branchId = auth()->user()->branch_id;
            if (!$branchId) {
                return back()->with('error', 'User must be assigned to a branch for transfers.')->withInput();
            }
        }

        $toShop       = (int) $request->input('to_shop', 0);
        $toWarehouseId = $request->input('to_warehouse_id');
        $products      = $request->input('product_id', []);
        $quantities    = $request->input('quantity', []);
        $fromLocations = $request->input('from_location', []);
        $remarks       = $request->input('remarks', '');

        // Validate destination
        if (!$toShop && !$toWarehouseId) {
            return back()->with('error', '❌ Please select a destination location.')->withInput();
        }

        // Validate destination warehouse exists
        if (!$toShop && $toWarehouseId) {
            if (!Warehouse::find($toWarehouseId)) {
                return back()->with('error', '❌ Invalid destination warehouse.')->withInput();
            }
        }

        // At least one valid row
        $hasValidRow = false;
        foreach ($products as $i => $pid) {
            if ($pid && isset($quantities[$i]) && $quantities[$i] > 0 && !empty($fromLocations[$i])) {
                $hasValidRow = true;
                break;
            }
        }
        if (!$hasValidRow) {
            return back()->with('error', '❌ Please add at least one product with a source location and quantity.')->withInput();
        }

        try {
            DB::transaction(function () use (
                $products, $quantities, $fromLocations,
                $toShop, $toWarehouseId, $branchId, $remarks
            ) {
                foreach ($products as $index => $productId) {
                    if (!$productId) continue;

                    $qty = (float) ($quantities[$index] ?? 0);
                    if ($qty <= 0) continue;

                    $fromLocation = $fromLocations[$index] ?? null;
                    if (!$fromLocation) continue;

                    // Parse from_location: "warehouse_5" or "branch_2"
                    $parts        = explode('_', $fromLocation, 2);
                    $fromType     = $parts[0];   // 'warehouse' or 'branch'
                    $fromLocId    = (int) ($parts[1] ?? 0);

                    if (!$fromLocId) {
                        throw new \Exception("Invalid source location for product ID: {$productId}");
                    }

                    // Lock and fetch source stock
                    if ($fromType === 'warehouse') {
                        $sourceStock = WarehouseStock::lockForUpdate()
                            ->where('warehouse_id', $fromLocId)
                            ->where('branch_id', $branchId)
                            ->where('product_id', $productId)
                            ->first();
                    } else {
                        // branch => shop stock (warehouse_id IS NULL)
                        $sourceStock = WarehouseStock::lockForUpdate()
                            ->where('branch_id', $fromLocId)
                            ->whereNull('warehouse_id')
                            ->where('product_id', $productId)
                            ->first();
                    }

                    if (!$sourceStock || $sourceStock->quantity < $qty) {
                        $productName = \App\Models\Product::find($productId)?->item_name ?? "ID:{$productId}";
                        throw new \Exception("Insufficient stock for product: {$productName} (available: " . ($sourceStock?->quantity ?? 0) . ", requested: {$qty})");
                    }

                    // Deduct from source
                    $sourceStock->quantity -= $qty;
                    $sourceStock->save();

                    // Add to destination
                    if ($toShop) {
                        // Transfer to branch shop (warehouse_id NULL)
                        $destStock = WarehouseStock::firstOrCreate(
                            ['branch_id' => $branchId, 'warehouse_id' => null, 'product_id' => $productId],
                            ['quantity' => 0, 'price' => $sourceStock->price ?? 0]
                        );
                    } else {
                        // Transfer to warehouse
                        $destStock = WarehouseStock::firstOrCreate(
                            ['branch_id' => $branchId, 'warehouse_id' => $toWarehouseId, 'product_id' => $productId],
                            ['quantity' => 0, 'price' => $sourceStock->price ?? 0]
                        );
                    }

                    $destStock->quantity += $qty;
                    $destStock->save();

                    // Record transfer
                    $transfer = StockTransfer::create([
                        'from_warehouse_id' => $fromType === 'warehouse' ? $fromLocId : null,
                        'from_branch_id'    => $fromType === 'branch' ? $fromLocId : null,
                        'to_warehouse_id'   => $toShop ? null : $toWarehouseId,
                        'to_branch_id'      => $toShop ? $branchId : null,
                        'to_shop'           => $toShop,
                        'product_id'        => $productId,
                        'quantity'          => $qty,
                        'remarks'           => $remarks,
                    ]);

                    $sourceLabel = $fromType === 'warehouse'
                        ? "Warehouse #{$fromLocId}"
                        : "Branch Shop #{$fromLocId}";
                    $destLabel = $toShop
                        ? "Branch Shop #{$branchId}"
                        : "Warehouse #{$toWarehouseId}";

                    StockMovement::create([
                        'product_id' => $productId,
                        'type'       => 'out',
                        'qty'        => $qty,
                        'ref_type'   => 'STOCK_TRANSFER',
                        'ref_id'     => $transfer->id,
                        'note'       => "Transfer OUT from {$sourceLabel} to {$destLabel}",
                    ]);

                    StockMovement::create([
                        'product_id' => $productId,
                        'type'       => 'in',
                        'qty'        => $qty,
                        'ref_type'   => 'STOCK_TRANSFER',
                        'ref_id'     => $transfer->id,
                        'note'       => "Transfer IN to {$destLabel} from {$sourceLabel}",
                    ]);
                }
            });

            return redirect()->route('stock_transfers.index')->with('success', '✅ Stock transferred successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }


    public function destroy(StockTransfer $stockTransfer) {

        // Optional: reverse the transfer if needed
        return back()->with('error', 'Deleting transfers not allowed.');
    }
    public function getStockQuantity(Request $request)
    {
        // Always read quantities from warehouse_stocks for both warehousing and branch stock.
        // Branch stock is stored as warehouse_stocks where warehouse_id is NULL.
        if ($request->has('branch_id') && $request->branch_id) {
            // Getting stock FROM branch (warehouse_id must be NULL)
            $stock = WarehouseStock::where('branch_id', $request->branch_id)
                ->whereNull('warehouse_id')
                ->where('product_id', $request->product_id)
                ->first();

            return response()->json([
                'quantity' => $stock ? $stock->quantity : 0
            ]);
        }

        if ($request->has('warehouse_id') && $request->warehouse_id) {
            // Getting stock FROM warehouse - also filter by branch_id if user has one
            $userBranchId = auth()->user()->branch_id;
            $query = WarehouseStock::where('warehouse_id', $request->warehouse_id)
                ->where('product_id', $request->product_id);
            
            if ($userBranchId) {
                $query->where('branch_id', $userBranchId);
            }
            
            $stock = $query->first();

            return response()->json([
                'quantity' => $stock ? $stock->quantity : 0
            ]);
        }

        return response()->json(['quantity' => 0]);
    }

    /**
     * Get all source locations (warehouses + branch shop) for a product within a branch,
     * including their current stock quantities. Called via AJAX on product select.
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

        // 1. Branch shop stock (warehouse_id IS NULL)
        $shopStock = WarehouseStock::where('branch_id', $branchId)
            ->whereNull('warehouse_id')
            ->where('product_id', $productId)
            ->first();

        $branchName = $branch->name ?? $branch->branch_name ?? 'Branch #' . $branchId;

        $locations[] = [
            'value'    => 'branch_' . $branchId,
            'label'    => '🏪 Shop — ' . $branchName,
            'quantity' => $shopStock ? (float) $shopStock->quantity : 0.0,
        ];

        // 2. Each warehouse assigned to this branch
        foreach ($branch->warehouses as $warehouse) {
            $whStock = WarehouseStock::where('branch_id', $branchId)
                ->where('warehouse_id', $warehouse->id)
                ->where('product_id', $productId)
                ->first();

            $locations[] = [
                'value'    => 'warehouse_' . $warehouse->id,
                'label'    => '📦 ' . ($warehouse->warehouse_name ?? 'Warehouse #' . $warehouse->id),
                'quantity' => $whStock ? (float) $whStock->quantity : 0.0,
            ];
        }

        // Sort: locations with stock first
        usort($locations, fn($a, $b) => $b['quantity'] <=> $a['quantity']);

        return response()->json($locations);
    }

}



// delvivery challan 
// convet out per  stock ledger maintain