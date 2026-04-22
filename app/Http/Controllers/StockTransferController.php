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
        // Route middleware handles permission check for stock.transfer.create

        // Build validation rules
        $rules = [
            'from_location_type' => 'required|in:warehouse,branch',
            'from_warehouse_id' => 'required|integer|min:1',
            'product_id' => 'required|array|min:1',
            'product_id.*' => 'required|integer|exists:products,id',
            'quantity' => 'required|array|min:1',
            'quantity.*' => 'required|integer|min:1',
            'to_shop' => 'required|in:0,1',
            'to_warehouse_id' => 'nullable|integer|exists:warehouses,id',
            'remarks' => 'nullable|string|max:255',
        ];

        // ✅ ERP STANDARD: If super admin, branch selection is required
        if (auth()->user()->hasRole('super admin')) {
            $rules['branch_id'] = 'required|integer|exists:branches,id';
        }

        $validated = $request->validate($rules);

        // Additional validation: to_warehouse_id is required when to_shop is 0
        $toShop = $request->input('to_shop') ? 1 : 0;
        if (!$toShop && !$request->filled('to_warehouse_id')) {
            return back()->withErrors(['to_warehouse_id' => 'Destination warehouse is required when not transferring to branch.']);
        }

        // ✅ ERP STANDARD: Determine branch_id for this transfer
        if (auth()->user()->hasRole('super admin')) {
            // Super admin must explicitly select branch
            $branchId = (int) $request->input('branch_id');
            if (!$branchId) {
                return back()->with('error', 'Admin must select a branch to perform transfer.');
            }
        } else {
            // Simple user: auto-use their assigned branch
            $branchId = auth()->user()->branch_id;
            if (!$branchId) {
                return back()->with('error', 'User must be assigned to a branch for transfers.');
            }
        }

        $fromLocationId = $request->input('from_warehouse_id');
        $fromLocationType = $request->input('from_location_type');
        $toWarehouseId = $request->input('to_warehouse_id');
        $toShop = (int) $request->input('to_shop');
        $fromType = $fromLocationType;
        $fromBranchId = $branchId;

        // ✅ CRITICAL VALIDATION: Source and destination cannot be the same location
        // This is a fundamental ERP rule - transfers are BETWEEN different locations
        if ($fromType === 'warehouse' && !$toShop) {
            // Both are warehouses
            if ((int)$fromLocationId === (int)$toWarehouseId) {
                return back()->with('error', '❌ Source and destination warehouse cannot be the same. Stock transfer must be from one location to a different location.')->withInput();
            }
        } elseif ($fromType === 'branch' && $toShop) {
            // Both are branches
            if ((int)$fromLocationId === (int)$fromBranchId) {
                return back()->with('error', '❌ Source and destination branch cannot be the same. Stock transfer must be from one location to a different location.')->withInput();
            }
        }
        // Note: warehouse-to-branch and branch-to-warehouse transfers are allowed

        if ($fromType === 'warehouse') {
            $fromWarehouse = Warehouse::find($fromLocationId);
            if (!$fromWarehouse) {
                return back()->with('error', 'Invalid source warehouse selected.');
            }
        } else {
            $fromBranch = Branch::find($fromLocationId);
            if (!$fromBranch) {
                return back()->with('error', 'Invalid source branch selected.');
            }
            $fromBranchId = $fromLocationId;
        }

        if (!$toShop && !$toWarehouseId) {
            return back()->with('error', 'Please select a destination warehouse or choose to transfer to branch.');
        }
        if (!$toShop && $fromLocationId === $toWarehouseId && $fromType === 'warehouse') {
            return back()->with('error', 'Source and destination warehouse cannot be the same.');
        }

        // If transferring to warehouse, validate it exists
        if (!$toShop && $toWarehouseId) {
            $toWarehouse = Warehouse::find($toWarehouseId);
            if (!$toWarehouse) {
                return back()->with('error', 'Invalid destination warehouse selected.');
            }
        }

        $products = $request->input('product_id');
        $quantities = $request->input('quantity');
        $remarks = $request->input('remarks') ?? '';

        try {
            DB::transaction(function () use (
                $products,
                $quantities,
                $fromLocationId,
                $fromType,
                $fromBranchId,
                $toWarehouseId,
                $toShop,
                $branchId,
                $remarks
            ) {
                foreach ($products as $index => $productId) {
                    $qty = (int) ($quantities[$index] ?? 0);
                    if ($qty <= 0) {
                        continue;
                    }

                    if ($fromType === 'warehouse') {
                        $sourceStock = WarehouseStock::lockForUpdate()
                            ->where('warehouse_id', $fromLocationId)
                            ->where('product_id', $productId)
                            ->first();
                    } else {
                        // Branch stock is stored in warehouse_stocks with warehouse_id NULL
                        $sourceStock = WarehouseStock::lockForUpdate()
                            ->where('branch_id', $fromBranchId)
                            ->whereNull('warehouse_id')
                            ->where('product_id', $productId)
                            ->first();
                    }

                    if (!$sourceStock || $sourceStock->quantity < $qty) {
                        throw new \Exception('Insufficient stock for product ID: ' . $productId);
                    }

                    $sourceStock->quantity -= $qty;
                    $sourceStock->save();

                    if ($toShop) {
                        $destStock = WarehouseStock::firstOrCreate([
                            'branch_id' => $branchId,
                            'warehouse_id' => null,
                            'product_id' => $productId,
                        ], [
                            'quantity' => 0,
                            'price' => $sourceStock->price ?? 0,
                        ]);

                        $destStock->quantity += $qty;
                        $destStock->save();
                    } else {
                        // ✅ CRITICAL FIX: Include branch_id when creating warehouse_stocks
                        // warehouse_stocks requires both branch_id and warehouse_id (no default values)
                        $destStock = WarehouseStock::firstOrCreate([
                            'branch_id' => $branchId,           // ✅ REQUIRED - was missing before!
                            'warehouse_id' => $toWarehouseId,   // Destination warehouse
                            'product_id' => $productId,
                        ], [
                            'quantity' => 0,
                            'price' => $sourceStock->price ?? 0,
                        ]);

                        $destStock->quantity += $qty;
                        $destStock->save();
                    }

                    $transfer = StockTransfer::create([
                        'from_warehouse_id' => $fromType === 'warehouse' ? $fromLocationId : null,
                        'to_warehouse_id' => $toShop ? null : $toWarehouseId,
                        'from_branch_id' => $fromType === 'branch' ? $fromBranchId : null,
                        'to_branch_id' => $toShop ? $branchId : null,
                        'to_shop' => $toShop,
                        'product_id' => $productId,
                        'quantity' => $qty,
                        'remarks' => $remarks,
                    ]);

                    $sourceLabel = $fromType === 'warehouse' ? "Warehouse #{$fromLocationId}" : ("Branch #{$fromBranchId}");
                    $destLabel = $toShop ? "Shop (Branch)" : "Warehouse #{$toWarehouseId}";

                    StockMovement::create([
                        'product_id' => $productId,
                        'type' => 'out',
                        'qty' => $qty,
                        'ref_type' => 'STOCK_TRANSFER',
                        'ref_id' => $transfer->id,
                        'note' => "Transfer OUT from {$sourceLabel} to {$destLabel}",
                    ]);

                    StockMovement::create([
                        'product_id' => $productId,
                        'type' => 'in',
                        'qty' => $qty,
                        'ref_type' => 'STOCK_TRANSFER',
                        'ref_id' => $transfer->id,
                        'note' => "Transfer IN to {$destLabel} from {$sourceLabel}",
                    ]);
                }
            });

            return redirect()->route('stock_transfers.index')->with('success', 'Stock transferred successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
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

}



// delvivery challan 
// convet out per  stock ledger maintain