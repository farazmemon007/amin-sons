<?php

namespace App\Http\Controllers;

use App\Models\StockRequest;
use App\Models\StockRequestItem;
use App\Models\StockTransfer;
use App\Models\Stock;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Warehouse;
use App\Models\BranchTransaction;
use App\Models\BranchAccount;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockRequestController extends Controller
{
    // List all requests for the current branch
    public function index()
    {
        // ✅ ERP PROPER: Super admin sees all requests; regular users see only their branch
        if (auth()->user()->hasRole('super admin')) {
            // Super admin: show all requests
            $incomingRequests = StockRequest::with(['fromBranch', 'items.product', 'createdBy'])
                ->orderByDesc('created_at')
                ->get();
            $outgoingRequests = StockRequest::with(['toBranch', 'items.product', 'approvedBy'])
                ->orderByDesc('created_at')
                ->get();
        } else {
            // Regular user: show only their branch's requests
            $branchId = auth()->user()->branch_id;
            if (!$branchId) {
                return back()->with('error', 'User must be assigned to a branch.');
            }

            // Incoming requests (to be approved by this branch)
            $incomingRequests = StockRequest::where('to_branch_id', $branchId)
                ->with(['fromBranch', 'items.product', 'createdBy'])
                ->orderByDesc('created_at')
                ->get();

            // Outgoing requests (created by this branch)
            $outgoingRequests = StockRequest::where('from_branch_id', $branchId)
                ->with(['toBranch', 'items.product', 'approvedBy'])
                ->orderByDesc('created_at')
                ->get();
        }

        return view('admin_panel.inter_branch.stock_requests.index', compact('incomingRequests', 'outgoingRequests'));
    }

    // Create new request form
    public function create()
    {
        // ✅ ERP PROPER: Super admin has no branch, they can create requests for any branch
        if (auth()->user()->hasRole('super admin')) {
            $fromBranchId = null; // Super admin not bound to specific branch
            $branches = Branch::all(); // Show all branches as options
        } else {
            $fromBranchId = auth()->user()->branch_id;
            if (!$fromBranchId) {
                return back()->with('error', 'User must be assigned to a branch.');
            }
            $branches = Branch::where('id', '!=', $fromBranchId)->get();
        }

        $products = Product::all();

        return view('admin_panel.inter_branch.stock_requests.create', compact('branches', 'products', 'fromBranchId'));
    }

    // Store new request
    public function store(Request $request)
    {
        // ✅ ERP PROPER: Super admin can create requests from any branch
        if (auth()->user()->hasRole('super admin')) {
            // For super admin, validate they can select any branch
            $validated = $request->validate([
                'from_branch_id' => 'required|exists:branches,id',
                'to_branch_id' => 'required|exists:branches,id|different:from_branch_id',
                'product_id' => 'required|array|min:1',
                'product_id.*' => 'required|integer|exists:products,id',
                'quantity' => 'required|array|min:1',
                'quantity.*' => 'required|integer|min:1',
                'remarks' => 'nullable|string|max:500',
            ]);
            $fromBranchId = $validated['from_branch_id'];
        } else {
            // Regular users create from their own branch
            $validated = $request->validate([
                'to_branch_id' => 'required|exists:branches,id|different:from_branch_id',
                'product_id' => 'required|array|min:1',
                'product_id.*' => 'required|integer|exists:products,id',
                'quantity' => 'required|array|min:1',
                'quantity.*' => 'required|integer|min:1',
                'remarks' => 'nullable|string|max:500',
            ]);
            $fromBranchId = auth()->user()->branch_id;
            if (!$fromBranchId) {
                return back()->with('error', 'User must be assigned to a branch.');
            }
        }

        try {
            DB::transaction(function () use ($validated, $fromBranchId) {
                $stockRequest = StockRequest::create([
                    'from_branch_id' => $fromBranchId,
                    'to_branch_id' => $validated['to_branch_id'],
                    'created_by' => auth()->id(),
                    'remarks' => $validated['remarks'] ?? null,
                    'status' => 'pending',
                ]);

                foreach ($validated['product_id'] as $index => $productId) {
                    $qty = (int)($validated['quantity'][$index] ?? 0);
                    if ($qty > 0) {
                        StockRequestItem::create([
                            'stock_request_id' => $stockRequest->id,
                            'product_id' => $productId,
                            'requested_qty' => $qty,
                        ]);
                    }
                }
            });

            return redirect()->route('stock_requests.index')
                ->with('success', 'Stock request created successfully. Waiting for approval.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error creating request: ' . $e->getMessage());
        }
    }

    // Show approval form
    public function show(StockRequest $stockRequest)
    {
        // ✅ ERP PROPER: Super admin can approve any request; regular users can only approve for their branch
        if (!auth()->user()->hasRole('super admin')) {
            $branchId = auth()->user()->branch_id;
            if (!$branchId) {
                return back()->with('error', 'User must be assigned to a branch.');
            }
            // Only the receiving branch can approve
            if ($stockRequest->to_branch_id !== $branchId) {
                return back()->with('error', 'Unauthorized to approve this request.');
            }
        }

        if ($stockRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        // Eager load relationships
        $stockRequest->load(['fromBranch', 'toBranch', 'createdBy', 'approvedBy']);

        // ✅ ERP PROPER - SOURCE WAREHOUSES (Deduction Point)
        // Get all warehouses assigned to RECEIVING BRANCH (to_branch_id - the approving branch)
        // These warehouses contain stock that will be DEDUCTED FROM
        // Data fetched from: branch_warehouse junction table
        $approvingBranch = Branch::findOrFail($stockRequest->to_branch_id);
        $sourceWarehouses = $approvingBranch->warehouses()->get();

        // ✅ ERP PROPER - DESTINATION WAREHOUSES (Addition Point)
        // Get all warehouses assigned to REQUESTING BRANCH (from_branch_id)
        // These warehouses will RECEIVE the transferred stock
        // Data fetched from: branch_warehouse junction table
        $requestingBranch = Branch::findOrFail($stockRequest->from_branch_id);
        $destinationWarehouses = $requestingBranch->warehouses()->get();

        // Load warehouse stock availability for each product in source warehouses
        $items = $stockRequest->items()->with(['product'])->get();
        foreach ($items as $item) {
            // Sum total available quantity across all source warehouses (from approving/receiving branch)
            $item->availableStock = WarehouseStock::where('branch_id', $stockRequest->to_branch_id)
                ->where('product_id', $item->product_id)
                ->whereIn('warehouse_id', $sourceWarehouses->pluck('id')->toArray())
                ->sum('quantity');
        }

        return view('admin_panel.inter_branch.stock_requests.approve', compact('stockRequest', 'items', 'sourceWarehouses', 'destinationWarehouses'));
    }

    // Process approval
    public function approve(Request $request, StockRequest $stockRequest)
    {
        \Log::info('=== APPROVAL STARTED ===');
        \Log::info('Stock Request ID: ' . $stockRequest->id);
        \Log::info('Request Data: ', $request->all());
        
        // ✅ ERP PROPER: Super admin can approve; regular users only for their branch
        if (!auth()->user()->hasRole('super admin')) {
            $branchId = auth()->user()->branch_id;
            if (!$branchId) {
                \Log::error('User branch assignment missing');
                return back()->with('error', 'User must be assigned to a branch.');
            }
            // Authorization: only the receiving branch can approve
            if ($stockRequest->to_branch_id !== $branchId) {
                \Log::error('Authorization failed: to_branch_id=' . $stockRequest->to_branch_id . ', branchId=' . $branchId);
                return back()->with('error', 'Unauthorized to approve this request.');
            }
        }

        $approvingBranchId = $stockRequest->to_branch_id;
        \Log::info('Approving Branch ID: ' . $approvingBranchId);

        if ($stockRequest->status !== 'pending') {
            \Log::error('Status error: status=' . $stockRequest->status);
            return back()->with('error', 'This request has already been processed.');
        }

        // Validate form data
        try {
            $validated = $request->validate([
                'item_id' => 'required|array|min:1',
                'item_id.*' => 'required|integer|exists:stock_request_items,id',
                'approved_qty' => 'required|array|min:1',
                'approved_qty.*' => 'required|integer|min:1',
                'warehouse_id' => 'required|array|min:1',
                'warehouse_id.*' => 'required|integer|exists:warehouses,id',
                'destination_warehouse_id' => 'required|array|min:1',
                'destination_warehouse_id.*' => 'required|integer|exists:warehouses,id',
                'delivery_charges' => 'nullable|array',
                'delivery_charges.*' => 'nullable|numeric|min:0',
            ], [
                'approved_qty.*.required' => 'Please enter approval quantity for all items',
                'approved_qty.*.min' => 'Approval quantity must be at least 1',
                'warehouse_id.*.required' => 'Please select a warehouse for all items',
                'destination_warehouse_id.*.required' => 'Please select a destination warehouse for all items',
            ]);
            \Log::info('Validation passed');
        } catch (\Exception $e) {
            \Log::error('Validation error: ' . $e->getMessage());
            throw $e;
        }

        try {
            DB::transaction(function () use ($validated, $stockRequest, $approvingBranchId) {
                \Log::info('=== TRANSACTION STARTED ===');
                $totalAmount = 0;
                $totalDeliveryCharges = 0;
                $receivingBranchId = $stockRequest->from_branch_id;
                $sendingBranchId = $stockRequest->to_branch_id;

                // Get branch names for ledger descriptions
                $sendingBranch = Branch::findOrFail($sendingBranchId);
                $receivingBranch = Branch::findOrFail($receivingBranchId);

                // First loop: Validate and collect data
                foreach ($validated['item_id'] as $index => $itemId) {
                    $approvedQty = (int)($validated['approved_qty'][$index] ?? 0);
                    $warehouseId = (int)($validated['warehouse_id'][$index] ?? 0);
                    $destWarehouseId = (int)($validated['destination_warehouse_id'][$index] ?? 0);
                    $deliveryCharges = (float)($validated['delivery_charges'][$index] ?? 0);

                    if ($approvedQty <= 0) {
                        throw new \Exception("Approval quantity must be greater than 0");
                    }

                    if ($warehouseId <= 0) {
                        throw new \Exception("Please select a warehouse for each item");
                    }

                    if ($destWarehouseId <= 0) {
                        throw new \Exception("Please select a destination warehouse for each item");
                    }

                    $item = StockRequestItem::findOrFail($itemId);

                    // Verify requested quantity
                    if ($approvedQty > $item->requested_qty) {
                        throw new \Exception("Cannot approve {$approvedQty} for {$item->product->item_name}. Requested: {$item->requested_qty}");
                    }

                    // Check available stock in source warehouse (APPROVING/SENDING BRANCH)
                    $availableStock = WarehouseStock::where('branch_id', $approvingBranchId)
                        ->where('warehouse_id', $warehouseId)
                        ->where('product_id', $item->product_id)
                        ->value('quantity') ?? 0;

                    if ($availableStock < $approvedQty) {
                        throw new \Exception("Insufficient stock for {$item->product->item_name}. Available in warehouse: {$availableStock}, Requested: {$approvedQty}");
                    }

                    // Get unit price
                    $warehouseStockPrice = WarehouseStock::where('branch_id', $approvingBranchId)
                        ->where('warehouse_id', $warehouseId)
                        ->where('product_id', $item->product_id)
                        ->value('price');
                    
                    $unitPrice = $warehouseStockPrice ?? ($item->product->price ?? 0);

                    // Update item with approval details
                    $item->update([
                        'approved_qty' => $approvedQty,
                        'from_warehouse_id' => $warehouseId,
                        'to_warehouse_id' => $destWarehouseId,
                        'delivery_charges' => $deliveryCharges,
                        'unit_price' => $unitPrice,
                    ]);

                    $itemTotal = $approvedQty * $unitPrice;
                    $totalAmount += $itemTotal;
                    $totalDeliveryCharges += $deliveryCharges;

                    \Log::info("Item: {$item->product->item_name}, Qty: {$approvedQty}, Price: {$unitPrice}, Total: {$itemTotal}, Delivery: {$deliveryCharges}");
                }

                // Update request status
                $stockRequest->approve(auth()->id());

                // ✅ CRITICAL FIX: Reload items from database after first loop updates them
                // The $stockRequest->items relationship is cached from initial load
                // Without refresh, approved_qty and warehouse_ids won't be available in second loop
                $stockRequest->load('items');
                \Log::info('Items reloaded from database for second loop processing');

                // Second loop: Process stock movements
                foreach ($stockRequest->items as $item) {
                    if ($item->approved_qty && $item->approved_qty > 0) {
                        // Create stock transfer audit record
                        StockTransfer::create([
                            'from_warehouse_id' => $item->from_warehouse_id,
                            'to_warehouse_id' => $item->to_warehouse_id,
                            'from_branch_id' => $sendingBranchId,
                            'to_branch_id' => $receivingBranchId,
                            'product_id' => $item->product_id,
                            'quantity' => $item->approved_qty,
                            'stock_request_id' => $stockRequest->id,
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);

                        // ✅ STEP 1: DEDUCT from source warehouse_stocks table
                        $sourceWarehouseStock = WarehouseStock::lockForUpdate()
                            ->where('branch_id', $sendingBranchId)
                            ->where('warehouse_id', $item->from_warehouse_id)
                            ->where('product_id', $item->product_id)
                            ->first();

                        if ($sourceWarehouseStock) {
                            $sourceWarehouseStock->quantity -= $item->approved_qty;
                            $sourceWarehouseStock->save();
                            \Log::info('Deducted from warehouse_stocks: Branch-' . $sendingBranchId . ', Warehouse-' . $item->from_warehouse_id . ', Product-' . $item->product_id . ', Qty: ' . $item->approved_qty);
                        } else {
                            \Log::warning('Warehouse stock not found for Branch-' . $sendingBranchId . ', Warehouse-' . $item->from_warehouse_id . ', Product-' . $item->product_id);
                        }

                        // ✅ STEP 2: DEDUCT from source branch stocks table (aggregate)
                        // SOURCE BRANCH = Sending Branch (the one approving - current user's branch)
                        $sourceBranchStock = Stock::lockForUpdate()
                            ->where('branch_id', $sendingBranchId)
                            ->where('product_id', $item->product_id)
                            ->first();

                        if ($sourceBranchStock) {
                            $sourceBranchStock->qty -= $item->approved_qty;
                            // Don't let qty go negative
                            if ($sourceBranchStock->qty < 0) {
                                \Log::warning('Stock qty would be negative, setting to 0. Product: ' . $item->product_id);
                                $sourceBranchStock->qty = 0;
                            }
                            $sourceBranchStock->save();
                            \Log::info('Deducted from stocks table: Branch-' . $sendingBranchId . ', Product-' . $item->product_id . ', Qty: ' . $item->approved_qty);
                        } else {
                            \Log::warning('Stock entry not found in stocks table for Branch-' . $sendingBranchId . ', Product-' . $item->product_id);
                        }

                        // ENSURE PRODUCT EXISTS IN RECEIVING BRANCH - Create if needed
                        $product = Product::where('id', $item->product_id)
                            ->where('branch_id', $receivingBranchId)
                            ->first();
                        
                        if (!$product) {
                            \Log::warning('Product not found in branch ' . $receivingBranchId . '. Creating product: ' . $item->product_id);
                            // Create product FOR THIS BRANCH with all details from source product
                            $sourceProduct = Product::find($item->product_id);
                            
                            $product = Product::create([
                                'branch_id' => $receivingBranchId,
                                'item_name' => $sourceProduct->item_name ?? 'Product #' . $item->product_id,
                                'item_code' => $sourceProduct->item_code ?? 'P-' . $item->product_id,
                                'category_id' => $sourceProduct->category_id ?? null,
                                'sub_category_id' => $sourceProduct->sub_category_id ?? null,
                                'brand_id' => $sourceProduct->brand_id ?? null,
                                'unit_id' => $sourceProduct->unit_id ?? null,
                                'price' => $item->unit_price ?? 0,
                                'wholesale_price' => $sourceProduct->wholesale_price ?? 0,
                                'color' => $sourceProduct->color ?? null,
                                'alert_quantity' => $sourceProduct->alert_quantity ?? 10,
                                'initial_stock' => 0,
                                'is_part' => $sourceProduct->is_part ?? 0,
                                'is_assembled' => $sourceProduct->is_assembled ?? 0,
                                'model' => $sourceProduct->model ?? 'N/A',
                                'hs_code' => $sourceProduct->hs_code ?? null,
                                'pack_type' => $sourceProduct->pack_type ?? null,
                                'pack_qty' => $sourceProduct->pack_qty ?? null,
                                'piece_per_pack' => $sourceProduct->piece_per_pack ?? null,
                                'loose_piece' => $sourceProduct->loose_piece ?? null,
                                'type_id' => $sourceProduct->type_id ?? null,
                                'barcode_path' => $sourceProduct->barcode_path ?? null,
                                'image' => $sourceProduct->image ?? null,
                            ]);
                            \Log::info('Product created in branch ' . $receivingBranchId . ': ' . $product->id);
                        }

                        // ✅ STEP 3: ADD to receiving warehouse_stocks table
                        $destWarehouseStock = WarehouseStock::where('branch_id', $receivingBranchId)
                            ->where('warehouse_id', $item->to_warehouse_id)
                            ->where('product_id', $item->product_id)
                            ->first();

                        if ($destWarehouseStock) {
                            // Update existing quantity
                            $destWarehouseStock->quantity += $item->approved_qty;
                            $destWarehouseStock->save();
                            \Log::info('Added to warehouse_stocks: Branch-' . $receivingBranchId . ', Warehouse-' . $item->to_warehouse_id . ', Product-' . $item->product_id . ', Qty: ' . $item->approved_qty);
                        } else {
                            // Create new warehouse stock entry
                            WarehouseStock::create([
                                'branch_id' => $receivingBranchId,
                                'warehouse_id' => $item->to_warehouse_id,
                                'product_id' => $item->product_id,
                                'quantity' => $item->approved_qty,
                                'price' => $item->unit_price ?? 0,
                            ]);
                            \Log::info('Created new warehouse_stocks entry: Branch-' . $receivingBranchId . ', Warehouse-' . $item->to_warehouse_id . ', Product-' . $item->product_id . ', Qty: ' . $item->approved_qty);
                        }

                        // ✅ STEP 4: ADD to receiving branch stocks table (aggregate)
                        // DESTINATION BRANCH = Receiving Branch (from_branch_id)
                        $destBranchStock = Stock::where('branch_id', $receivingBranchId)
                            ->where('product_id', $item->product_id)
                            ->first();

                        if ($destBranchStock) {
                            // Update existing stock quantity
                            $destBranchStock->qty += $item->approved_qty;
                            $destBranchStock->save();
                            \Log::info('Added to stocks table: Branch-' . $receivingBranchId . ', Product-' . $item->product_id . ', Qty: ' . $item->approved_qty);
                        } else {
                            // Create new stock entry for receiving branch
                            Stock::create([
                                'branch_id' => $receivingBranchId,
                                'product_id' => $item->product_id,
                                'qty' => $item->approved_qty,
                                'reserved_qty' => 0,
                            ]);
                            \Log::info('Created new stocks entry: Branch-' . $receivingBranchId . ', Product-' . $item->product_id . ', Qty: ' . $item->approved_qty);
                        }
                    }
                }

                // ✅ ERP PROPER - CREATE DETAILED FINANCIAL LEDGER ENTRIES
                // Stock Value Entry (Without Delivery Charges)
                BranchTransaction::create([
                    'branch_id' => $sendingBranchId,
                    'related_branch_id' => $receivingBranchId,
                    'type' => 'credit',
                    'amount' => $totalAmount,
                    'reference_type' => 'transfer',
                    'reference_id' => $stockRequest->id,
                    'description' => "Stock transfer (Stock Value): {$totalAmount} to {$receivingBranch->name}. Request ID: {$stockRequest->id}",
                    'created_by' => auth()->id(),
                ]);

                // Debit entry for receiving branch (Stock Value)
                BranchTransaction::create([
                    'branch_id' => $receivingBranchId,
                    'related_branch_id' => $sendingBranchId,
                    'type' => 'debit',
                    'amount' => $totalAmount,
                    'reference_type' => 'transfer',
                    'reference_id' => $stockRequest->id,
                    'description' => "Stock transfer (Stock Value): {$totalAmount} from {$sendingBranch->name}. Request ID: {$stockRequest->id}",
                    'created_by' => auth()->id(),
                ]);

                // Delivery Charges Entry (If any)
                if ($totalDeliveryCharges > 0) {
                    // Credit entry for sending branch (Delivery Charges - Additional Receivable)
                    BranchTransaction::create([
                        'branch_id' => $sendingBranchId,
                        'related_branch_id' => $receivingBranchId,
                        'type' => 'credit',
                        'amount' => $totalDeliveryCharges,
                        'reference_type' => 'transfer_delivery_charges',
                        'reference_id' => $stockRequest->id,
                        'description' => "Delivery charges: {$totalDeliveryCharges} for transfer to {$receivingBranch->name}. Request ID: {$stockRequest->id}",
                        'created_by' => auth()->id(),
                    ]);

                    // Debit entry for receiving branch (Delivery Charges - Additional Payable)
                    BranchTransaction::create([
                        'branch_id' => $receivingBranchId,
                        'related_branch_id' => $sendingBranchId,
                        'type' => 'debit',
                        'amount' => $totalDeliveryCharges,
                        'reference_type' => 'transfer_delivery_charges',
                        'reference_id' => $stockRequest->id,
                        'description' => "Delivery charges: {$totalDeliveryCharges} from {$sendingBranch->name}. Request ID: {$stockRequest->id}",
                        'created_by' => auth()->id(),
                    ]);
                }

                // Update account balances for both branches
                BranchAccount::where('branch_id', $sendingBranchId)->first()?->updateBalance();
                BranchAccount::where('branch_id', $receivingBranchId)->first()?->updateBalance();

                \Log::info("=== LEDGER ENTRIES CREATED ===");
                \Log::info("Stock Value: {$totalAmount}, Delivery Charges: {$totalDeliveryCharges}, Total: " . ($totalAmount + $totalDeliveryCharges));
                \Log::info('=== TRANSACTION COMPLETED SUCCESSFULLY ===');
            });

            \Log::info('Redirecting to stock_requests index');
            return redirect()->route('inter_branch_stock_requests.index')
                ->with('success', 'Stock request approved successfully! Stock transferred and ledger updated.');
        } catch (\Exception $e) {
            \Log::error('CRITICAL ERROR in approve(): ' . $e->getMessage());
            \Log::error('Stack Trace: ' . $e->getTraceAsString());
            return back()->withInput()
                ->with('error', 'Error approving request: ' . $e->getMessage());
        }
    }

    // Reject request
    public function reject(Request $request, StockRequest $stockRequest)
    {
        $branchId = auth()->user()->branch_id;

        if ($stockRequest->to_branch_id !== $branchId) {
            return back()->with('error', 'Unauthorized.');
        }

        if ($stockRequest->status !== 'pending') {
            return back()->with('error', 'Request already processed.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $stockRequest->reject(auth()->id(), $validated['rejection_reason']);

        return back()->with('success', 'Request rejected successfully.');
    }

    // Get warehouse stock for a product (JSON response)
    public function getWarehouseStock($warehouseId, $productId)
    {
        $branchId = auth()->user()->branch_id;
        
        $stock = WarehouseStock::where('branch_id', $branchId)
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->first();

        $quantity = $stock ? $stock->quantity : 0;
        
        // Use warehouse stock price if available, otherwise use product's wholesale price
        $price = 0;
        if ($stock && $stock->price) {
            $price = $stock->price;
        } else {
            // Get price from product
            $product = Product::find($productId);
            $price = $product ? $product->price : 0;
        }

        return response()->json([
            'quantity' => $quantity,
            'price' => floatval($price),
        ]);
    }

    // ✅ ERP PROPER - Get products for a specific branch (for dynamic dropdown)
    public function getBranchProducts($branchId)
    {
        // Validate branch exists
        $branch = Branch::findOrFail($branchId);

        // Get all products for the specified branch
        $products = Product::where('branch_id', $branchId)
            ->select('id', 'item_name', 'item_code')
            ->orderBy('item_name')
            ->get();

        return response()->json([
            'success' => true,
            'branch_id' => $branchId,
            'branch_name' => $branch->branch_name,
            'products' => $products,
            'count' => $products->count(),
        ]);
    }
}
