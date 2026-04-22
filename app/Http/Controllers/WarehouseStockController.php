<?php
namespace App\Http\Controllers;

use App\Models\WarehouseOrder;
use App\Models\Product;
use App\Models\StockOnhand;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\Notification;
use App\Models\Branch;
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

    public function getByWarehouse($warehouseId)
    {
        // ✅ ERP PROPER: Get current user's branch to filter warehouse stock
        $branchId = auth()->user()->branch_id;

        $products = WarehouseStock::with('product')
            ->where('branch_id', $branchId)
            ->where('warehouse_id', $warehouseId)
            ->get()
            ->map(function ($row) {
                return [
                    'id'   => $row->product->id,
                    'name' => $row->product->item_name,
                    'qty'  => $row->quantity,
                ];
            });

        return response()->json($products);
    }

    public function index()
    {
        // ✅ ERP STANDARD: Get user's branch access permissions
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super admin');
        
        // Determine allowed branches for current user
        $allowedBranchIds = $this->allowedBranches('warehouse.stock.view');

        if (empty($allowedBranchIds)) {
            // User not authorized to view any branch
            $products = collect();
            $stats = [
                'totalProducts' => 0,
                'totalQuantity' => 0,
                'warehouses' => 0,
            ];
        } else {
            // ✅ ERP PROPER: Get all warehouse stocks ONLY for user's allowed branches
            $allStocks = WarehouseStock::with(['warehouse', 'product', 'branch'])
                ->whereIn('branch_id', $allowedBranchIds)
                ->orderByDesc('updated_at')
                ->get();

            // Aggregate by product: sum quantities across all warehouses per product
            $productGroups = $allStocks->groupBy('product_id')
                ->map(function ($stocks) use ($isSuperAdmin) {
                    $product = $stocks->first()->product;
                    $totalQty = $stocks->sum('quantity');
                    
                    // Get warehouse distribution
                    $warehouseDistribution = $stocks->map(function ($stock) use ($isSuperAdmin) {
                        // ✅ FOR SUPER ADMIN: Show branch name for clarity
                        // FOR OTHERS: Show only warehouse name (they only see their own branch anyway)
                        if ($stock->warehouse_id === null) {
                            // Location is a branch directly
                            $warehouseDisplay = optional($stock->branch)->name ?? 'Unknown';
                        } else {
                            // Location is a warehouse
                            $warehouseName = optional($stock->warehouse)->warehouse_name ?? 'Unknown';
                            
                            // For super admin: Include branch name
                            if ($isSuperAdmin) {
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
                        'product_id' => $product->id,
                        'product_name' => $product->item_name ?? 'N/A',
                        'product_code' => $product->item_code ?? 'N/A',
                        'category' => optional($product->category_relation)->name ?? 'Uncategorized',
                        'total_quantity' => $totalQty,
                        'warehouse_count' => count($warehouseDistribution),
                        'warehouses' => $warehouseDistribution,
                        'image' => $product->image,
                        'price' => $product->price ?? 0,
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
        }

        return view('admin_panel.warehouses.warehouse_stocks.index', compact('products', 'stats', 'allowedBranchIds', 'isSuperAdmin'));
    }

    public function show($productId)
    {
        // ✅ ERP STANDARD: Get user's branch access permissions
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super admin');
        
        // Determine allowed branches for current user
        $allowedBranchIds = $this->allowedBranches('warehouse.stock.view');

        // Get the product
        $product = Product::findOrFail($productId);

        // ✅ AUTHORIZATION: Check if user can view this product's warehouses
        // - Super admin: can view all products
        // - Regular user: can only view if product has stock in their allowed branches
        $hasAccessToProduct = $isSuperAdmin || 
            WarehouseStock::whereIn('branch_id', $allowedBranchIds)
                ->where('product_id', $productId)
                ->exists();

        if (!$hasAccessToProduct) {
            abort(403, 'Unauthorized access to this product inventory');
        }

        // Import CustomerRemaining model
        $customerRemainingModel = \App\Models\CustomerRemaining::class;

        // Get warehouse distribution for this product - ONLY from allowed branches
        $warehouses = WarehouseStock::with(['warehouse', 'branch'])
            ->where('product_id', $productId)
            ->whereIn('branch_id', $allowedBranchIds)
            ->orderByDesc('quantity')
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
                // ✅ Only include if there are actually reserved items (remaining_qty > 0 AND status pending/partial)
                $customerReservedDetails = $customerRemainingModel::with(['customer', 'sale'])
                    ->where('product_id', $productId)
                    ->where('warehouse_id', $stock->warehouse_id)
                    ->whereIn('status', ['pending', 'partial'])
                    ->where('remaining_qty', '>', 0)  // ✅ Ensure qty > 0
                    ->get()
                    ->map(function ($item) {
                        return [
                            'customer_name' => optional($item->customer)->name ?? 'N/A',
                            'sale_id' => $item->sale_id,
                            'remaining_qty' => $item->remaining_qty,
                            'status' => $item->status,
                        ];
                    })
                    ->values()  // Re-index array
                    ->toArray();  // ✅ Convert to array for proper serialization

                // ✅ BUILD WAREHOUSE NAME FOR DISPLAY
                if ($stock->warehouse_id === null) {
                    // Location is a branch directly
                    $warehouseDisplay = optional($stock->branch)->name ?? 'Unknown';
                } else {
                    // Location is a warehouse
                    $warehouseName = optional($stock->warehouse)->warehouse_name ?? 'Unknown';
                    
                    // For super admin: Add branch name for clarity
                    if ($isSuperAdmin) {
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
                    'branch_name' => optional($stock->branch)->name ?? 'Unknown',
                    'quantity' => $stock->quantity,
                    'reserved_qty' => $stock->reserved_quantity ?? 0,
                    'customer_reserved' => $customerReserved,
                    'available_qty' => ($available >= 0 ? $available : 0),
                    'customer_reserved_details' => $customerReservedDetails,
                    'remarks' => $stock->remarks,
                    'updated_at' => $stock->updated_at,
                ];
            });

        $totalQty = $warehouses->sum('quantity');
        $totalReserved = $warehouses->sum('reserved_qty');
        $totalCustomerReserved = $warehouses->sum('customer_reserved');
        $totalAvailable = $warehouses->sum('available_qty');

        return view('admin_panel.warehouses.warehouse_stocks.show', compact(
            'product',
            'warehouses',
            'totalQty',
            'totalReserved',
            'totalCustomerReserved',
            'totalAvailable',
            'isSuperAdmin'
        ));
    }

    public function create()
    {
        // Determine branches the user can access
        $allowedBranchIds = $this->allowedBranches('warehouse-stocks-product.view');

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
        // return response()->json([
        //     'warehouses' => $warehouses,
        //     'products' => $products,
        //     'remainingByProduct' => $remainingByProduct,
        //     'branches' => $branches
        // ]);

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
        $allowedBranchIds = $this->allowedBranches('warehouse-stocks-product.view');
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
        $allowedBranchIds = $this->allowedBranches('warehouse-stocks-product.view');
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
        $allowedBranchIds = $this->allowedBranches('warehouse-stocks-product.view');
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
