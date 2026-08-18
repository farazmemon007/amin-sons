<?php
namespace App\Http\Controllers;

use App\Models\WarehouseOrder;
use App\Models\Product;
use App\Models\StockOnhand;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\Notification;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Concerns\BranchScope;

class WarehouseStockController extends Controller
{
    use BranchScope;
    public function changeStatus($id)
{
    $order = WarehouseOrder::findOrFail($id);
    $order->status = 'delivered';
    $order->save();

    return redirect()->back()->with('success', 'Order marked as delivered');
}
    public function warehouseOrders()

    {   

        $orders = WarehouseOrder::with(['warehouse', 'customer'])->orderByDesc('id')->get();
        // return response()->json([ 'orders' => $orders]);
        return view('admin_panel.warehouses.warehouse_order.warehouse_order_booking', compact('orders'));
    }

    public function getByWarehouse(Request $request, $warehouseId)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super admin');
        $allowedBranchIds = $this->allowedBranches('warehouse.stock.view');

        $branchId = $request->get('branch_id');

        $query = WarehouseStock::with('product')
            ->where('warehouse_id', $warehouseId);

        if ($branchId && ($isSuperAdmin || in_array((int)$branchId, $allowedBranchIds))) {
            $query->where('branch_id', $branchId);
        } else {
            $query->whereIn('branch_id', $allowedBranchIds);
        }

        $products = $query->get()
            ->map(function ($row) {
                return [
                    'id'   => optional($row->product)->id,
                    'name' => optional($row->product)->item_name ?? 'N/A',
                    'qty'  => $row->quantity,
                ];
            })->filter(fn($r) => !empty($r['id']))->values();

        return response()->json($products);
    }

    public function index(Request $request)
    {
        // ✅ ERP STANDARD: Get user's branch access permissions
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super admin');
        
        // Determine allowed branches for current user
        $allowedBranchIds = $this->allowedBranches('warehouse.stock.view');
        $showBranchFilter = $isSuperAdmin || (count($allowedBranchIds) > 1);
        $showBranchName   = $isSuperAdmin || (count($allowedBranchIds) > 1);
        
        // ✅ FILTERS
        $selectedBranchId = $request->get('branch_id');
        $selectedWarehouseId = $request->get('warehouse_id');

        // Branch validation: if specified, ensure user has access to it
        if ($selectedBranchId !== null && $selectedBranchId !== '') {
            if (!$isSuperAdmin && !in_array((int) $selectedBranchId, $allowedBranchIds)) {
                $selectedBranchId = $user->branch_id;
            }
        } else {
            // No branch specifically selected => null means search across all allowed branches
            $selectedBranchId = null;
        }

        // ✅ ERP WAREHOUSE-LEVEL SCOPING:
        $allowedBranchWarehousePairs = null;
        if (!$isSuperAdmin) {
            $assignedWarehouses = $user->warehouses()->withPivot('branch_id')->get();
            if ($assignedWarehouses->isNotEmpty()) {
                $allowedBranchWarehousePairs = $assignedWarehouses->map(function ($w) {
                    return ['branch_id' => (int) $w->pivot->branch_id, 'warehouse_id' => (int) $w->id];
                })->toArray();
            }
        }

        if (empty($allowedBranchIds)) {
            // User not authorized to view any branch
            $products = collect();
            $warehouseGroups = collect();
            $stats = [
                'totalProducts' => 0,
                'totalQuantity' => 0,
                'warehouses' => 0,
            ];
            $branches = collect();
            $warehouses = collect();
            $hasDirectStock = false;
            $damagedStocksList = collect();
        } else {
            // ✅ ERP PROPER: Get all warehouse stocks ONLY for user's allowed branches & assigned warehouses
            $query = WarehouseStock::with(['warehouse', 'product', 'branch'])
                ->whereIn('branch_id', $allowedBranchIds);

            // Apply branch filter
            if ($selectedBranchId) {
                $query->where('branch_id', $selectedBranchId);
            }

            // Apply warehouse filter
            if ($selectedWarehouseId !== null && $selectedWarehouseId !== '') {
                // '0' or null might mean "Shop/Branch Direct" in some contexts, but here warehouse_id is nullable
                if ($selectedWarehouseId === 'shop') {
                    $query->whereNull('warehouse_id');
                } else {
                    $query->where('warehouse_id', $selectedWarehouseId);
                }
            }

            if ($allowedBranchWarehousePairs !== null) {
                $query->where(function ($q) use ($allowedBranchWarehousePairs) {
                    foreach ($allowedBranchWarehousePairs as $pair) {
                        $q->orWhere(function ($q2) use ($pair) {
                            $q2->where('branch_id', $pair['branch_id'])
                               ->where('warehouse_id', $pair['warehouse_id']);
                        });
                    }
                });
            }

            $allStocks = $query->orderByDesc('updated_at')->get();

            // Aggregate by product: sum quantities across all warehouses per product
            $productGroups = $allStocks->groupBy('product_id')
                ->map(function ($stocks) use ($showBranchName) {
                    $product = $stocks->first()->product;
                    $totalQty = $stocks->sum('quantity');
                    
                    // Get warehouse distribution
                    $warehouseDistribution = $stocks->map(function ($stock) use ($showBranchName) {
                        if ($stock->warehouse_id === null) {
                            $warehouseDisplay = optional($stock->branch)->name ?? 'Unknown';
                        } else {
                            $warehouseName = optional($stock->warehouse)->warehouse_name ?? 'Unknown';
                            if ($showBranchName) {
                                $branchName = optional($stock->branch)->name ?? 'Unknown';
                                $warehouseDisplay = "$warehouseName - $branchName";
                            } else {
                                $warehouseDisplay = $warehouseName;
                            }
                        }
                        
                        return [
                            'warehouse_id' => $stock->warehouse_id,
                            'warehouse_name' => $warehouseDisplay,
                            'branch_id' => $stock->branch_id,
                            'quantity' => $stock->quantity,
                            'remarks' => $stock->remarks,
                            'updated_at' => $stock->updated_at,
                        ];
                    })->sortByDesc('quantity')->values()->toArray();

                    return [
                        'product_id' => optional($product)->id,
                        'product_name' => optional($product)->item_name ?? 'N/A',
                        'product_code' => optional($product)->item_code ?? 'N/A',
                        'category' => optional(optional($product)->category_relation)->name ?? 'Uncategorized',
                        'total_quantity' => $totalQty,
                        'warehouse_count' => count($warehouseDistribution),
                        'warehouses' => $warehouseDistribution,
                        'image' => optional($product)->image,
                        'price' => optional($product)->price ?? 0,
                    ];
                })
                ->sortByDesc('total_quantity')
                ->values();

            $products = $productGroups;
            
            $stats = [
                'totalProducts' => $products->count(),
                'totalQuantity' => $products->sum('total_quantity'),
                'warehouses' => $allStocks->pluck('warehouse_id')->unique()->count(),
            ];

            // ✅ WAREHOUSE VIEW: Group all stocks by warehouse
            $warehouseGroups = $allStocks->groupBy(function ($stock) {
                return ($stock->warehouse_id ?? 'branch_') . '_' . $stock->branch_id;
            })->map(function ($stocks) use ($showBranchName) {
                $firstStock = $stocks->first();
                $branchName = optional($firstStock->branch)->name ?? 'Unknown';

                if ($firstStock->warehouse_id === null) {
                    $warehouseName = $branchName . ' (Direct)';
                } else {
                    $warehouseName = optional($firstStock->warehouse)->warehouse_name ?? 'Unknown';
                }

                $displayName = $showBranchName ? "$warehouseName — $branchName" : $warehouseName;

                return [
                    'warehouse_id'   => $firstStock->warehouse_id,
                    'warehouse_name' => $displayName,
                    'branch_id'      => $firstStock->branch_id,
                    'branch_name'    => $branchName,
                    'total_quantity' => $stocks->sum('quantity'),
                    'product_count'  => $stocks->count(),
                    'products'       => $stocks->map(function ($s) {
                        return [
                            'product_id'   => $s->product_id,
                            'product_name' => optional($s->product)->item_name ?? 'N/A',
                            'product_code' => optional($s->product)->item_code ?? 'N/A',
                            'quantity'     => $s->quantity,
                            'image'        => optional($s->product)->image,
                        ];
                    })->sortByDesc('quantity')->values()->toArray(),
                ];
            })->sortBy([
                ['branch_id', 'asc'],
                ['warehouse_name', 'asc']
            ])->values();

            // ✅ Data for filters: show allowed branches for any user with multi-branch access
            $branches = Branch::whereIn('id', $allowedBranchIds)->orderBy('name')->get();

            // Warehouses with stock for the selected or allowed branches
            $filterBranchId = $selectedBranchId;
            
            $whFilterQuery = WarehouseStock::whereNotNull('warehouse_id');
            if ($filterBranchId) {
                $whFilterQuery->where('branch_id', $filterBranchId);
            } else {
                $whFilterQuery->whereIn('branch_id', $allowedBranchIds);
            }

            if ($allowedBranchWarehousePairs !== null) {
                $whFilterQuery->where(function ($q) use ($allowedBranchWarehousePairs) {
                    foreach ($allowedBranchWarehousePairs as $pair) {
                        $q->orWhere(function ($q2) use ($pair) {
                            $q2->where('branch_id', $pair['branch_id'])
                               ->where('warehouse_id', $pair['warehouse_id']);
                        });
                    }
                });
            }

            $warehouseIdsWithStock = $whFilterQuery->pluck('warehouse_id')->unique();
            $warehouses = Warehouse::whereIn('id', $warehouseIdsWithStock)->orderBy('warehouse_name')->get();

            $directStockQuery = WarehouseStock::whereNull('warehouse_id');
            if ($filterBranchId) {
                $directStockQuery->where('branch_id', $filterBranchId);
            } else {
                $directStockQuery->whereIn('branch_id', $allowedBranchIds);
            }
            $hasDirectStock = $directStockQuery->exists();

            // ✅ DAMAGED STOCKS VIEW DATA (ERP STANDARD)
            $damagedStocksQuery = \App\Models\DamagedStock::with(['branch', 'warehouse', 'product'])
                ->whereIn('branch_id', $allowedBranchIds);

            if ($selectedBranchId) {
                $damagedStocksQuery->where('branch_id', $selectedBranchId);
            }
            if ($selectedWarehouseId !== null && $selectedWarehouseId !== '') {
                if ($selectedWarehouseId === 'shop') {
                    $damagedStocksQuery->whereNull('warehouse_id');
                } else {
                    $damagedStocksQuery->where('warehouse_id', $selectedWarehouseId);
                }
            }
            $damagedStocksList = $damagedStocksQuery->orderByDesc('updated_at')->get();
        }

        return view('admin_panel.warehouses.warehouse_stocks.index', compact(
            'products', 'stats', 'allowedBranchIds', 'isSuperAdmin', 'warehouseGroups', 
            'branches', 'warehouses', 'selectedBranchId', 'selectedWarehouseId', 'hasDirectStock',
            'damagedStocksList', 'showBranchFilter'
        ));
    }

    public function getWarehousesForFilter(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super admin');
        $allowedBranchIds = $this->allowedBranches('warehouse.stock.view');

        $branchId = $request->get('branch_id');
        
        $query = WarehouseStock::query();
        if ($branchId) {
            if (!$isSuperAdmin && !in_array((int)$branchId, $allowedBranchIds)) {
                return response()->json(['warehouses' => [], 'hasDirectStock' => false]);
            }
            $query->where('branch_id', $branchId);
        } else {
            $query->whereIn('branch_id', $allowedBranchIds);
        }

        if (!$isSuperAdmin) {
            $assignedWarehouses = $user->warehouses()->withPivot('branch_id')->get();
            if ($assignedWarehouses->isNotEmpty()) {
                $pairs = $assignedWarehouses->map(fn($w) => [
                    'branch_id' => (int) $w->pivot->branch_id,
                    'warehouse_id' => (int) $w->id
                ])->toArray();
                $query->where(function ($q) use ($pairs) {
                    foreach ($pairs as $p) {
                        $q->orWhere(function ($q2) use ($p) {
                            $q2->where('branch_id', $p['branch_id'])
                               ->where('warehouse_id', $p['warehouse_id']);
                        });
                    }
                });
            }
        }

        $warehouseIdsWithStock = (clone $query)->whereNotNull('warehouse_id')->pluck('warehouse_id')->unique();
        $warehouses = Warehouse::whereIn('id', $warehouseIdsWithStock)->orderBy('warehouse_name')->get(['id', 'warehouse_name']);
        $hasDirectStock = (clone $query)->whereNull('warehouse_id')->exists();

        return response()->json([
            'warehouses' => $warehouses,
            'hasDirectStock' => $hasDirectStock
        ]);
    }

    public function show(Request $request, $productId)
    {
        // ✅ ERP STANDARD: Get user's branch access permissions
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super admin');

        // Determine allowed branches for current user
        $allowedBranchIds = $this->allowedBranches('warehouse.stock.view');

        // Get the product
        $product = Product::findOrFail($productId);

        // ✅ ERP WAREHOUSE-LEVEL SCOPING:
        // If a non-admin user has explicit warehouse assignments (via user_warehouses pivot),
        // restrict them to ONLY those specific branch+warehouse combinations.
        // Users with NO explicit assignments (branch-level users) see all warehouses in their branch.
        $allowedWarehouseIds = null; // null = no extra restriction (branch-level access)
        $allowedBranchWarehousePairs = null; // null = no extra restriction
        if (!$isSuperAdmin) {
            $assignedWarehouses = $user->warehouses()->withPivot('branch_id')->get();
            if ($assignedWarehouses->isNotEmpty()) {
                $allowedWarehouseIds = $assignedWarehouses->pluck('id')->toArray();
                // Build branch+warehouse pairs for precise filtering
                $allowedBranchWarehousePairs = $assignedWarehouses->map(function ($w) {
                    return ['branch_id' => (int) $w->pivot->branch_id, 'warehouse_id' => (int) $w->id];
                })->toArray();
            }
        }

        // ✅ AUTHORIZATION: Check if user can view this product's warehouses
        $authQuery = WarehouseStock::whereIn('branch_id', $allowedBranchIds)
            ->where('product_id', $productId);
        if ($allowedBranchWarehousePairs !== null) {
            $authQuery->where(function ($q) use ($allowedBranchWarehousePairs) {
                foreach ($allowedBranchWarehousePairs as $pair) {
                    $q->orWhere(function ($q2) use ($pair) {
                        $q2->where('branch_id', $pair['branch_id'])
                           ->where('warehouse_id', $pair['warehouse_id']);
                    });
                }
            });
        }
        $hasAccessToProduct = $isSuperAdmin || $authQuery->exists();

        if (!$hasAccessToProduct) {
            abort(403, 'Unauthorized access to this product inventory');
        }

        // ✅ Build the list of branches that HAVE stock for this product AND user can access
        $branchStockQuery = WarehouseStock::where('product_id', $productId)
            ->whereIn('branch_id', $allowedBranchIds);
        if ($allowedBranchWarehousePairs !== null) {
            $branchStockQuery->where(function ($q) use ($allowedBranchWarehousePairs) {
                foreach ($allowedBranchWarehousePairs as $pair) {
                    $q->orWhere(function ($q2) use ($pair) {
                        $q2->where('branch_id', $pair['branch_id'])
                           ->where('warehouse_id', $pair['warehouse_id']);
                    });
                }
            });
        }
        $branchIdsWithStock = $branchStockQuery->pluck('branch_id')->unique()->values();

        $availableBranches = Branch::whereIn('id', $branchIdsWithStock)
            ->orderBy('name')
            ->get();

        // ✅ Determine selected branch (from ?branch_id or 0 = All Branches)
        $selectedBranchId = (int) $request->get('branch_id', 0);

        // Validate: if a specific branch was chosen it must be in the allowed+has-stock list
        // 0 is always valid — it means "All Branches"
        if ($selectedBranchId !== 0 && !$branchIdsWithStock->contains($selectedBranchId)) {
            $selectedBranchId = 0; // fallback to All Branches
        }

        // Show selector only when user can see more than one branch for this product
        $showBranchFilter = $availableBranches->count() > 1;

        // Import CustomerRemaining model
        $customerRemainingModel = \App\Models\CustomerRemaining::class;

        // ✅ Build the main warehouses query with all applicable filters
        $query = WarehouseStock::with(['warehouse', 'branch'])
            ->where('product_id', $productId);

        if ($selectedBranchId) {
            $query->where('branch_id', $selectedBranchId);
        } else {
            $query->whereIn('branch_id', $allowedBranchIds);
        }

        // Apply precise branch+warehouse pair restriction when user has specific assignments
        if ($allowedBranchWarehousePairs !== null) {
            $query->where(function ($q) use ($allowedBranchWarehousePairs) {
                foreach ($allowedBranchWarehousePairs as $pair) {
                    $q->orWhere(function ($q2) use ($pair) {
                        $q2->where('branch_id', $pair['branch_id'])
                           ->where('warehouse_id', $pair['warehouse_id']);
                    });
                }
            });
        }

        $warehouses = $query->orderByDesc('quantity')
            ->get()
            ->map(function ($stock) use ($productId, $customerRemainingModel, $isSuperAdmin) {
                // Get customer reserved quantity from customer_remaining table
                $customerReserved = $customerRemainingModel::where('product_id', $productId)
                    ->where('warehouse_id', $stock->warehouse_id)
                    ->whereIn('status', ['pending', 'partial'])
                    ->sum('remaining_qty');

                // Calculate available quantity
                $available = $stock->quantity - $customerReserved;

                // Get customer reserved details for display
                $customerReservedDetails = $customerRemainingModel::with(['customer', 'sale'])
                    ->where('product_id', $productId)
                    ->where('warehouse_id', $stock->warehouse_id)
                    ->whereIn('status', ['pending', 'partial'])
                    ->where('remaining_qty', '>', 0)
                    ->get()
                    ->map(function ($item) {
                        return [
                            'customer_name' => optional($item->customer)->name ?? 'N/A',
                            'sale_id'       => $item->sale_id,
                            'remaining_qty' => $item->remaining_qty,
                            'status'        => $item->status,
                        ];
                    })
                    ->values()
                    ->toArray();

                // ✅ BUILD WAREHOUSE NAME FOR DISPLAY
                // Branch name is always passed separately so view can show it as a badge
                $branchName = optional($stock->branch)->name ?? 'Unknown';
                if ($stock->warehouse_id === null) {
                    $warehouseDisplay = $branchName;
                } else {
                    $warehouseDisplay = optional($stock->warehouse)->warehouse_name ?? 'Unknown';
                }

                return [
                    'warehouse_id'             => $stock->warehouse_id,
                    'warehouse_name'           => $warehouseDisplay,
                    'branch_id'                => $stock->branch_id,
                    'branch_name'              => $branchName,  // always populated; view decides visibility
                    'quantity'                 => $stock->quantity,
                    'reserved_qty'             => $stock->reserved_quantity ?? 0,
                    'customer_reserved'        => $customerReserved,
                    'available_qty'            => ($available >= 0 ? $available : 0),
                    'customer_reserved_details'=> $customerReservedDetails,
                    'remarks'                  => $stock->remarks,
                    'updated_at'               => $stock->updated_at,
                ];
            });

        $totalQty             = $warehouses->sum('quantity');
        $totalReserved        = $warehouses->sum('reserved_qty');
        $totalCustomerReserved= $warehouses->sum('customer_reserved');
        $totalAvailable       = $warehouses->sum('available_qty');

        // ✅ Check if this product has EVER had a warehouse stock record
        // (even if current quantity is 0 due to sales/transfers)
        $hasEverHadStock = WarehouseStock::where('product_id', $productId)
            ->whereIn('branch_id', $allowedBranchIds)
            ->exists();

        return view('admin_panel.warehouses.warehouse_stocks.show', compact(
            'product',
            'warehouses',
            'totalQty',
            'totalReserved',
            'totalCustomerReserved',
            'totalAvailable',
            'isSuperAdmin',
            'availableBranches',
            'selectedBranchId',
            'showBranchFilter',
            'hasEverHadStock'
        ));
    }

    public function create()
    {
        // Determine branches the user can access
        $allowedBranchIds = $this->allowedBranches('warehouse.stock.view');

        // Branch list for view: super admin sees all branches, others only allowed ones
        if (Auth::check() && Auth::user()->hasRole('super admin')) {
            $branches = Branch::all();
        } else {
            $branches = Branch::whereIn('id', $allowedBranchIds)->get();
        }

        // Warehouses: super admin sees all, others see warehouses belonging to allowed branches
        if (Auth::check() && Auth::user()->hasRole('super admin')) {
            $warehouses = Warehouse::all();
        } else {
            $warehouses = Warehouse::whereHas('branches', function ($q) use ($allowedBranchIds) {
                $q->whereIn('branches.id', $allowedBranchIds);
            })->get();
        }

        // Products restricted to branches current user can access
        $products = Product::when(!empty($allowedBranchIds), function ($q) use ($allowedBranchIds) {
            $q->whereIn('branch_id', $allowedBranchIds);
        })->get();

        $onhand = StockOnhand::pluck('onhand_qty', 'product_id')->toArray();

        // ✅ ERP PROPER: Calculate allocated stock only for branches user has access to
        $allocated = WarehouseStock::when(!empty($allowedBranchIds), function ($q) use ($allowedBranchIds) {
                $q->whereIn('branch_id', $allowedBranchIds);
            })
            ->groupBy('product_id')
            ->selectRaw('product_id, SUM(quantity) as total_alloc')
            ->pluck('total_alloc', 'product_id')
            ->toArray();

        $remainingByProduct = [];
        foreach ($products as $p) {
            $total = $onhand[$p->id] ?? (optional($p->stock)->qty ?? 0);
            $used = $allocated[$p->id] ?? 0;
            $remainingByProduct[$p->id] = max(0, $total - $used);
        }

        return view('admin_panel.warehouses.warehouse_stocks.create', compact('warehouses', 'products', 'remainingByProduct', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $data = $request->only(['branch_id', 'warehouse_id', 'product_id', 'quantity', 'price', 'remarks']);

        // ✅ ERP PROPER: Security check - user can only add stock to branches they have access to
        $allowedBranchIds = $this->allowedBranches('warehouse.stock.view');
        if (!in_array($data['branch_id'], $allowedBranchIds)) {
            return redirect()->back()->with('error', 'Unauthorized: You cannot add stock to this branch.');
        }

        // ✅ ERP PROPER: Validate that quantity doesn't exceed available stock
        $onhand = StockOnhand::where('product_id', $data['product_id'])->first();
        $totalAvailable = $onhand->onhand_qty ?? 0;

        // Calculate already allocated stock for this branch
        $allocated = WarehouseStock::where('branch_id', $data['branch_id'])
            ->where('product_id', $data['product_id'])
            ->sum('quantity');

        $remainingStock = max(0, $totalAvailable - $allocated);

        // Reject if quantity exceeds remaining available stock
        if ((int)$data['quantity'] > $remainingStock) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Cannot allocate {$data['quantity']} units. Only {$remainingStock} units available for this product (Total: {$totalAvailable}, Already allocated: {$allocated}).");
        }

        // If branch_only is checked or warehouse_id is empty, set warehouse_id to null
        if ($request->has('branch_only') || empty($data['warehouse_id'])) {
            $data['warehouse_id'] = null;
        }

        // PROPER ERP LOGIC: Check if stock entry already exists for this branch+warehouse+product
        // If yes → INCREMENT quantity (add stock)
        // If no → CREATE new entry
        
        $existingStock = WarehouseStock::where('branch_id', $data['branch_id'])
            ->where('warehouse_id', $data['warehouse_id'])
            ->where('product_id', $data['product_id'])
            ->first();

        if ($existingStock) {
            // Entry exists → INCREMENT the quantity (add stock to existing)
            $existingStock->quantity += $data['quantity'];
            
            // Update price and remarks if provided (only overwrite if new values given)
            if ($data['price'] ?? null) {
                $existingStock->price = $data['price'];
            }
            if ($data['remarks'] ?? null) {
                $existingStock->remarks = $data['remarks'];
            }
            
            $existingStock->save();
            
            \Log::info('Updated existing warehouse stock', [
                'id' => $existingStock->id,
                'product_id' => $data['product_id'],
                'branch_id' => $data['branch_id'],
                'warehouse_id' => $data['warehouse_id'],
                'quantity_added' => $data['quantity'],
                'new_total' => $existingStock->quantity,
            ]);
        } else {
            // No entry exists → CREATE new record
            WarehouseStock::create($data);
            
            \Log::info('Created new warehouse stock entry', [
                'product_id' => $data['product_id'],
                'branch_id' => $data['branch_id'],
                'warehouse_id' => $data['warehouse_id'],
                'quantity' => $data['quantity'],
            ]);
        }

        return redirect()->route('warehouse_stocks.index')->with('success', 'Stock added successfully.');
    }

    public function edit(WarehouseStock $warehouseStock)
    {
        // ✅ ERP PROPER: Security check - user can only edit stock from branches they have access to
        $allowedBranchIds = $this->allowedBranches('warehouse.stock.view');
        if (!in_array($warehouseStock->branch_id, $allowedBranchIds)) {
            return redirect()->route('warehouse_stocks.index')->with('error', 'Unauthorized: You cannot edit stock from this branch.');
        }

        $warehouses = Warehouse::all();

        // also restrict products on edit page
        $products = Product::when(!empty($allowedBranchIds), function ($q) use ($allowedBranchIds) {
            $q->whereIn('branch_id', $allowedBranchIds);
        })->get();

        return view('admin_panel.warehouses.warehouse_stocks.edit', compact('warehouseStock', 'warehouses', 'products'));
    }

    public function update(Request $request, WarehouseStock $warehouseStock)
    {
        // ✅ ERP PROPER: Security check - user can only update stock from branches they have access to
        $allowedBranchIds = $this->allowedBranches('warehouse.stock.view');
        if (!in_array($warehouseStock->branch_id, $allowedBranchIds)) {
            return redirect()->route('warehouse_stocks.index')->with('error', 'Unauthorized: You cannot update stock from this branch.');
        }

        $request->validate([
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0'
        ]);

        $newWarehouseId = $request->input('warehouse_id') ?? null;
        $newQuantity = (int) $request->input('quantity');
        $newPrice = $request->input('price');
        $newRemarks = $request->input('remarks');

        // Check if warehouse location changed (moving stock between warehouses)
        $warehouseChanged = $warehouseStock->warehouse_id != $newWarehouseId;

        if ($warehouseChanged) {
            // STOCK MOVEMENT: Remove from old location, add to new
            // Step 1: Decrease from current warehouse
            $warehouseStock->quantity = 0;  // Set to 0 (stock removed)
            $warehouseStock->save();
            
            \Log::info('Removed stock from old warehouse location', [
                'id' => $warehouseStock->id,
                'product_id' => $warehouseStock->product_id,
                'from_warehouse_id' => $warehouseStock->warehouse_id,
                'quantity_removed' => $warehouseStock->quantity,
            ]);

            // Step 2: Add to new warehouse (or create if doesn't exist)
            $newStockLocation = WarehouseStock::where('branch_id', $warehouseStock->branch_id)
                ->where('warehouse_id', $newWarehouseId)
                ->where('product_id', $warehouseStock->product_id)
                ->first();

            if ($newStockLocation) {
                // Already exists at new location → INCREMENT
                $newStockLocation->quantity += $newQuantity;
                $newStockLocation->price = $newPrice ?? $newStockLocation->price;
                $newStockLocation->remarks = $newRemarks ?? $newStockLocation->remarks;
                $newStockLocation->save();
                
                \Log::info('Added stock to existing warehouse location', [
                    'id' => $newStockLocation->id,
                    'product_id' => $warehouseStock->product_id,
                    'to_warehouse_id' => $newWarehouseId,
                    'quantity_added' => $newQuantity,
                    'new_total' => $newStockLocation->quantity,
                ]);
            } else {
                // Create new entry at new location
                WarehouseStock::create([
                    'branch_id' => $warehouseStock->branch_id,
                    'warehouse_id' => $newWarehouseId,
                    'product_id' => $warehouseStock->product_id,
                    'quantity' => $newQuantity,
                    'price' => $newPrice,
                    'remarks' => $newRemarks,
                ]);
                
                \Log::info('Created new stock entry at new warehouse location', [
                    'product_id' => $warehouseStock->product_id,
                    'to_warehouse_id' => $newWarehouseId,
                    'quantity' => $newQuantity,
                ]);
            }
        } else {
            // SAME WAREHOUSE: Just update the quantity and other fields
            $warehouseStock->quantity = $newQuantity;
            $warehouseStock->price = $newPrice ?? $warehouseStock->price;
            $warehouseStock->remarks = $newRemarks ?? $warehouseStock->remarks;
            $warehouseStock->save();
            
            \Log::info('Updated stock quantity in same warehouse', [
                'id' => $warehouseStock->id,
                'product_id' => $warehouseStock->product_id,
                'warehouse_id' => $warehouseStock->warehouse_id,
                'new_quantity' => $newQuantity,
            ]);
        }

        return redirect()->route('warehouse_stocks.index')->with('success', 'Stock updated successfully.');
    }
}
