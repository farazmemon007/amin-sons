<?php

namespace App\Http\Controllers;

use App\Models\CustomerRemaining;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerRemainingController extends Controller
{
    /**
     * Display all pending deliveries - with branch filtering
     * Super Admin OR permission 'customerremainingproducts.view.all': دیکھ سکتا ہے تمام branches کے
     * Permission 'customerremainingproducts.view' صرف: اپنی branch کے دیکھ سکتا ہے
     */
    public function index()
    {
        $query = CustomerRemaining::with(['customer', 'product', 'warehouse', 'sale'])
            ->whereIn('status', ['pending', 'partial']);

        // اگر all branches permission نہیں ہے تو صرف اپنی branch کے دیکھیں (super admin بھی)
        if (!auth()->user()->hasPermissionTo('customerremainingproducts.view.all')) {
            $branchId = auth()->user()->branch_id;
            // Sale table سے join کرکے branch filter کریں
            $query->whereHas('sale', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        $pendingDeliveries = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        // Statistics - یہی logic
        $statsQuery = CustomerRemaining::whereIn('status', ['pending', 'partial']);
        
        if (!auth()->user()->hasPermissionTo('customerremainingproducts.view.all')) {
            $branchId = auth()->user()->branch_id;
            $statsQuery->whereHas('sale', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        // ✅ NEW: Fetch all DCs with their product IDs and gatepass status for efficient checking in blade
        // Get all warehouse_orders (DCs) with their items for the pending sales
        $saleIds = $pendingDeliveries->pluck('sale_id')->unique()->values();
        
        // Fetch DCs with gatepass status
        $dcsWithGatepass = DB::table('warehouse_orders as wo')
            ->leftJoin('outward_gatepasses as og', 'wo.id', '=', 'og.order_id')
            ->select('wo.id', 'wo.sale_id', 'wo.items', 'og.id as gatepass_id')
            ->whereIn('wo.sale_id', $saleIds)
            ->get();
        
        $dcsByProduct = [];
        $gatepassByDC = []; // Map DC ID to gatepass ID (if exists)
        
        foreach ($dcsWithGatepass as $dc) {
            $gatepassByDC[$dc->id] = $dc->gatepass_id; // Store gatepass status
            
            // Parse JSON items and extract product IDs
            $items = json_decode($dc->items, true) ?? [];
            foreach ($items as $item) {
                $productId = $item['product_id'] ?? null;
                if ($productId) {
                    $key = "{$dc->sale_id}_{$productId}";
                    $dcsByProduct[$key] = $dc->id; // Store DC ID instead of just true
                }
            }
        }

        $stats = [
            'totalPending' => $statsQuery->count(),
            // Count distinct customers: if customer_id is not NULL, count customer_id; else count sale_id for walking customers
            'totalCustomers' => DB::table('customer_remaining')
                ->whereIn('status', ['pending', 'partial'])
                ->when(!auth()->user()->hasPermissionTo('customerremainingproducts.view.all'), function ($q) {
                    $branchId = auth()->user()->branch_id;
                    return $q->whereIn('sale_id', function ($subQ) use ($branchId) {
                        $subQ->select('id')
                            ->from('sales')
                            ->where('branch_id', $branchId);
                    });
                })
                ->selectRaw('COUNT(DISTINCT CASE WHEN customer_id IS NOT NULL THEN customer_id ELSE sale_id END) as cnt')
                ->first()
                ->cnt ?? 0,
            'totalSales' => $statsQuery->clone()
                ->distinct('sale_id')
                ->count('sale_id'),
            'totalQtyPending' => DB::table('customer_remaining')
                ->whereIn('status', ['pending', 'partial'])
                ->when(!auth()->user()->hasPermissionTo('customerremainingproducts.view.all'), function ($q) {
                    $branchId = auth()->user()->branch_id;
                    return $q->whereIn('sale_id', function ($subQ) use ($branchId) {
                        $subQ->select('id')
                            ->from('sales')
                            ->where('branch_id', $branchId);
                    });
                })
                ->sum('remaining_qty'),
        ];

        return view('admin_panel.warehouses.customer_remaining.index', compact('pendingDeliveries', 'stats', 'dcsByProduct', 'gatepassByDC'));
    }

    /**
     * Show details of a pending delivery - with branch authorization
     * ✅ UPDATED: Enforces proper ERP workflow - DC must exist BEFORE gate pass creation
     */
    public function show($id)
    {
        $item = CustomerRemaining::with(['customer', 'product', 'warehouse', 'sale'])
            ->findOrFail($id);

        // Branch user authorization: صرف اپنی branch کا دیکھ سکتا ہے
        // all branches permission والے دیکھ سکتے ہیں (super admin کو بھی permission دینی ہوگی)
        if (!auth()->user()->hasPermissionTo('customerremainingproducts.view.all')) {
            $branchId = auth()->user()->branch_id;
            if ($item->sale?->branch_id != $branchId) {
                abort(403, 'آپ کے پاس یہ record دیکھنے کی اجازت نہیں۔');
            }
        }

        // Get sale item details (total qty sold for this product)
        $saleItem = \App\Models\SaleItem::where('sale_id', $item->sale_id)
            ->where('product_id', $item->product_id)
            ->first();

        // Get delivery history from outward gatepasses for this sale
        $deliveries = DB::table('outward_gatepasses as og')
            ->join('warehouse_orders as wo', 'og.order_id', '=', 'wo.id')
            ->where('wo.sale_id', $item->sale_id)
            ->select(
                'og.id',
                'og.dc_no',
                'og.created_at',
                'og.items' // JSON column containing delivered items
            )
            ->orderBy('og.created_at', 'desc')
            ->get()
            ->map(function ($delivery) use ($item) {
                // Parse items JSON and calculate qty for this product
                $itemsData = json_decode($delivery->items, true) ?? [];
                $productQty = 0;
                
                foreach ($itemsData as $deliveryItem) {
                    if ($deliveryItem['product_id'] == $item->product_id) {
                        $productQty += (float)($deliveryItem['qty'] ?? 0);
                    }
                }
                
                return [
                    'gatepass_no' => $delivery->dc_no ?? 'GP-' . $delivery->id,
                    'product_qty' => $productQty,
                    'delivered_date' => \Carbon\Carbon::parse($delivery->created_at),
                ];
            });

        // Get related pending items for the same sale
        $relatedItems = CustomerRemaining::where('sale_id', $item->sale_id)
            ->where('id', '!=', $id)
            ->with(['product', 'warehouse'])
            ->get();

        // ✅ INTERNATIONAL ERP STANDARDS WORKFLOW CHECK:
        // Step 1: Check if item is already fully delivered
        // Step 2: Check if DC (Delivery Challan) exists for this product
        // Step 3: Based on status, determine next action (Create DC or Create Gate Pass)

        $allSaleItems = CustomerRemaining::where('sale_id', $item->sale_id)->get();
        $totalItems = $allSaleItems->count();
        $completedItems = $allSaleItems->filter(fn($i) => $i->status == 'completed')->count();

        // ✅ CHECK 1: Is this item already fully delivered?
        $isCompleted = $item->status == 'completed' || $item->remaining_qty <= 0;
        
        // ✅ CHECK 2: Does DC (WarehouseOrder) exist for this product?
        // Use raw query for reliable JSON search
        $dcExists = DB::table('warehouse_orders')
            ->where('sale_id', $item->sale_id)
            ->where(function ($q) use ($item) {
                // Use raw SQL for JSON search - more reliable than whereJsonContains
                $q->whereRaw("JSON_SEARCH(items, 'one', ?, NULL, '$[*].product_id') IS NOT NULL", [$item->product_id]);
            })
            ->exists();

        // ✅ ERP WORKFLOW LOGIC (International Standards - SAP/Oracle pattern):
        // Supports PARTIAL DELIVERIES:
        // IF item completed THEN → No further actions needed
        // ELSE IF remaining_qty <= 0 THEN → No further actions needed
        // ELSE IF remaining_qty > 0 THEN → Can create DC (supports multiple DCs for same product)

        $canCreateDC = false;
        $canCreateGatePass = false;
        $gatePassRestriction = null;
        $actionStep = null;

        if ($isCompleted) {
            // Item has been fully delivered
            $gatePassRestriction = 'This item has been fully delivered. No further gate passes can be created.';
            $actionStep = 'completed';
        } elseif ($completedItems == $totalItems && $totalItems > 0) {
            // All items from sale are completed
            $gatePassRestriction = 'All items from this sale have been fully delivered.';
            $actionStep = 'all_completed';
        } elseif ($item->remaining_qty > 0) {
            // ✅ ALWAYS ALLOW DC CREATION: As long as pending qty exists (supports partial deliveries)
            // User can create multiple DCs for same product across deliveries
            $canCreateDC = true;
            $actionStep = 'create_dc';
        } else {
            // Should not reach here, but safeguard
            $gatePassRestriction = 'No pending quantity for this item.';
            $actionStep = 'no_pending';
        }

        return view('admin_panel.warehouses.customer_remaining.show', compact(
            'item', 
            'relatedItems', 
            'saleItem', 
            'deliveries',
            'canCreateDC',           // ✅ NEW: Can create DC?
            'canCreateGatePass',     // Gate pass creation allowed?
            'gatePassRestriction',   // Reason if restricted
            'dcExists',              // ✅ NEW: Does DC exist?
            'actionStep',            // ✅ NEW: Current workflow step
            'totalItems',
            'completedItems'
        ));
    }

    /**
     * Mark item as completed - with branch authorization
     */
    public function markCompleted($id)
    {
        $item = CustomerRemaining::with(['sale'])->findOrFail($id);
        
        // Branch user authorization: صرف اپنی branch کا update کر سکتا ہے (super admin کو بھی permission دینی ہوگی)
        if (!auth()->user()->hasPermissionTo('customerremainingproducts.view.all')) {
            $branchId = auth()->user()->branch_id;
            if ($item->sale?->branch_id != $branchId) {
                return redirect()->back()->with('error', 'آپ کے پاس اس کام کی اجازت نہیں۔');
            }
        }
        
        $item->update([
            'status' => 'completed',
            'updated_at' => now(),
        ]);

        // Check if all items for this sale are completed
        $pendingCount = CustomerRemaining::where('sale_id', $item->sale_id)
            ->whereIn('status', ['pending', 'partial'])
            ->count();

        $message = 'Item marked as completed';
        if ($pendingCount === 0) {
            $message = 'Order fully completed! All items delivered.';
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Get pending items for a customer to create new gatepass
     */
    public function getPendingForCustomer($customerId)
    {
        $pending = CustomerRemaining::where('customer_id', $customerId)
            ->whereIn('status', ['pending', 'partial'])
            ->with(['product', 'warehouse'])
            ->get();

        return response()->json($pending);
    }

    /**
     * Get pending items for a specific sale
     */
    public function getPendingForSale($saleId)
    {
        $pending = CustomerRemaining::where('sale_id', $saleId)
            ->whereIn('status', ['pending', 'partial'])
            ->with(['product', 'warehouse'])
            ->get();

        return response()->json($pending);
    }

    /**
     * Delete a pending item (if delivery won't happen) - with branch authorization
     */
    public function delete($id)
    {
        $item = CustomerRemaining::with(['sale'])->findOrFail($id);
        
        // Branch user authorization: صرف اپنی branch کا ڈیلیٹ کر سکتا ہے (super admin کو بھی permission دینی ہوگی)
        if (!auth()->user()->hasPermissionTo('customerremainingproducts.view.all')) {
            $branchId = auth()->user()->branch_id;
            if ($item->sale?->branch_id != $branchId) {
                return redirect()->back()->with('error', 'آپ کے پاس اس کام کی اجازت نہیں۔');
            }
        }
        
        $item->delete();

        return redirect()->back()->with('success', 'Pending item removed');
    }

    /**
     * ✅ NEW: Check if DC (WarehouseOrder) exists for a product in a sale
     * Returns: DC exists status for tracking DC → GatePass workflow
     */
    public function hasDC($saleId, $productId = null)
    {
        $query = DB::table('warehouse_orders')->where('sale_id', $saleId);
        
        if ($productId) {
            // Check if product has any DC in this sale using reliable JSON_SEARCH
            $query->whereRaw("JSON_SEARCH(items, 'one', ?, NULL, '$[*].product_id') IS NOT NULL", [$productId]);
        }
        
        return $query->exists();
    }

    /**
     * ✅ NEW: Show form to create DC from customer_remaining
     * This is called BEFORE creating gate pass
     * Similar to warehouse_select but specifically for remaining items
     */
    public function showCreateDCForm($remainingId)
    {
        $remaining = CustomerRemaining::with(['sale', 'product', 'warehouse'])
            ->findOrFail($remainingId);

        // Branch authorization
        if (!auth()->user()->hasPermissionTo('customerremainingproducts.view.all')) {
            $branchId = auth()->user()->branch_id;
            if ($remaining->sale?->branch_id != $branchId) {
                abort(403, 'Unauthorized');
            }
        }

        // ✅ Support multiple DCs: Allow DC creation as long as remaining_qty > 0
        // (supports partial deliveries across multiple batches)
        if ($remaining->remaining_qty <= 0 || $remaining->status == 'completed') {
            return redirect()->route('customer-remaining.show', $remaining->id)
                ->with('error', 'No pending quantity for this item. It has been fully delivered.');
        }

        // Get all pending items from same sale (for reference)
        $pendingItems = CustomerRemaining::where('sale_id', $remaining->sale_id)
            ->whereIn('status', ['pending', 'partial'])
            ->where('remaining_qty', '>', 0)
            ->with('product')
            ->get();

        // Get available warehouses (only those with this product in stock)
        // Query warehouse_stocks where product exists with qty > 0
        $warehousesWithStock = DB::table('warehouse_stocks')
            ->select('warehouse_stocks.warehouse_id', 'warehouse_stocks.quantity', 'warehouses.warehouse_name')
            ->join('warehouses', 'warehouse_stocks.warehouse_id', '=', 'warehouses.id')
            ->where('warehouse_stocks.product_id', $remaining->product_id)
            ->where('warehouse_stocks.quantity', '>', 0)
            ->where('warehouses.deleted_at', null)
            ->orderBy('warehouses.warehouse_name')
            ->get();

        return view('admin_panel.warehouses.customer_remaining.dc_form', compact(
            'remaining',
            'pendingItems',
            'warehousesWithStock'
        ));
    }

    /**
     * ✅ NEW: Process DC creation from customer_remaining
     * Creates: New WarehouseOrder with user-selected delivery qty
     * Updates: customer_remaining with new remainder
     */
    public function storeDCFromRemaining(Request $request, $remainingId)
    {
        try {
            $remaining = CustomerRemaining::with(['sale', 'product'])
                ->lockForUpdate()
                ->findOrFail($remainingId);

            // Branch authorization
            if (!auth()->user()->hasPermissionTo('customerremainingproducts.view.all')) {
                $branchId = auth()->user()->branch_id;
                if ($remaining->sale?->branch_id != $branchId) {
                    abort(403, 'Unauthorized');
                }
            }

            // Validate form input
            $request->validate([
                'warehouse_id' => 'required|integer|exists:warehouses,id',
                'delivery_qty' => 'required|numeric|min:0.01',
            ]);

            $warehouseId = (int)$request->warehouse_id;
            $deliveryQty = (float)$request->delivery_qty;
            $remainingQty = (float)$remaining->remaining_qty;

            // Validate delivery qty
            if ($deliveryQty > $remainingQty) {
                return redirect()->back()->with('error', "Delivery quantity ($deliveryQty) cannot exceed remaining quantity ($remainingQty)");
            }

            if ($deliveryQty <= 0) {
                return redirect()->back()->with('error', 'Delivery quantity must be greater than 0');
            }

            // ✅ CREATE NEW DC FOR THIS REMAINING ITEM
            return DB::transaction(function () use ($remaining, $warehouseId, $deliveryQty, $remainingQty) {
                $sale = $remaining->sale;
                
                // Generate DC number
                $branch = \App\Models\Branch::lockForUpdate()->find($sale->branch_id ?? 1) 
                    ?? \App\Models\Branch::lockForUpdate()->first();
                
                $branch->dc_counter = ($branch->dc_counter ?? 0) + 1;
                $branch->save();
                
                $dcNo = 'DC-' . str_pad($branch->dc_counter, 4, '0', STR_PAD_LEFT);

                // Build items array for this specific product
                $itemsArray = [[
                    'sale_item_id' => null,  // From remaining, not original sale_item
                    'product_id' => $remaining->product_id,
                    'product_name' => $remaining->product_name,
                    'item_code' => $remaining->item_code,
                    'qty' => $deliveryQty,
                    'warehouse_id' => $warehouseId,
                    'retail_price' => optional($remaining->product)->retail_price ?? 0,
                    'amount' => $deliveryQty * (optional($remaining->product)->retail_price ?? 0),
                ]];

                // Create WarehouseOrder (DC)
                $warehouseOrder = \App\Models\WarehouseOrder::create([
                    'dc_no' => $dcNo,
                    'warehouse_id' => $warehouseId,
                    'customer_id' => $sale->customer_id,
                    'sale_id' => $sale->id,
                    'status' => 'pending',
                    'remarks' => "DC from remaining delivery. Original remaining: {$remainingQty}, Delivering now: {$deliveryQty}",
                    'prepared_by' => auth()->user()->name ?? null,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                    'items' => $itemsArray,
                ]);

                \Log::info('Created DC from customer remaining', [
                    'dc_no' => $dcNo,
                    'warehouse_id' => $warehouseId,
                    'remaining_id' => $remaining->id,
                    'delivery_qty' => $deliveryQty,
                ]);

                // ✅ CREATE STOCK HOLD RECORD
                \App\Models\StockHold::create([
                    'sale_id' => $sale->id,
                    'warehouse_order_id' => $warehouseOrder->id,
                    'product_id' => $remaining->product_id,
                    'warehouse_id' => $warehouseId,
                    'customer_id' => $sale->customer_id,
                    'invoice_no' => $sale->invoice_no,
                    'dc_no' => $dcNo,
                    'available_qty' => $remainingQty,
                    'deliver_qty' => $deliveryQty,
                    'remaining_qty' => max(0, $remainingQty - $deliveryQty),
                    'product_name' => $remaining->product_name,
                    'product_code' => $remaining->item_code,
                    'unit_price' => optional($remaining->product)->retail_price ?? 0,
                    'remarks' => "From customer_remaining partial delivery",
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);

                // ✅ UPDATE CUSTOMER_REMAINING WITH NEW REMAINDER
                $newRemainder = $remainingQty - $deliveryQty;
                
                if ($newRemainder > 0) {
                    // Still items remaining
                    $remaining->update([
                        'remaining_qty' => $newRemainder,
                        'status' => 'pending',
                        'remarks' => "Partial DC created: {$dcNo}. Delivered: {$deliveryQty}, Remaining: {$newRemainder}",
                        'updated_by' => auth()->id(),
                    ]);
                    
                    \Log::info('Updated customer_remaining after DC creation', [
                        'remaining_id' => $remaining->id,
                        'new_remainder' => $newRemainder,
                    ]);
                } else {
                    // All delivered
                    $remaining->update([
                        'remaining_qty' => 0,
                        'status' => 'completed',
                        'remarks' => "Fully delivered via DC: {$dcNo}",
                        'updated_by' => auth()->id(),
                    ]);
                    
                    \Log::info('Marked customer_remaining as completed', [
                        'remaining_id' => $remaining->id,
                        'dc_no' => $dcNo,
                    ]);
                }

                return redirect()->route('customer-remaining.show', $remaining->id)
                    ->with('success', "DC $dcNo created successfully! Delivered: {$deliveryQty}, Remaining: {$newRemainder}");
            });

        } catch (\Exception $e) {
            \Log::error('DC creation from customer_remaining failed', [
                'error' => $e->getMessage(),
                'remaining_id' => $remainingId,
            ]);

            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
