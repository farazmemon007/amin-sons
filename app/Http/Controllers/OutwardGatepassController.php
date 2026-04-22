<?php

namespace App\Http\Controllers;

use \Notification;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OutwardGatepassController extends Controller
{
    public function index()
    {
        // ✅ Show all DCs (warehouse_orders) instead of gate passes
        // User can click on DC to create gate pass for it
        $deliveryChallans = DB::table('warehouse_orders')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Enrich with related data
        $deliveryChallans->getCollection()->transform(function ($dc) {
            // Get customer info and sub_customer (walking type)
            $customer = DB::table('customers')->where('id', $dc->customer_id)->first();
            $dc->customer_name = $customer->customer_name ?? 'N/A';
            $dc->contact_person = $customer->contact_person ?? '';
            
            // Get sub_customer from sale (for walking type customers)
            $sale = DB::table('sales')->where('id', $dc->sale_id)->first();
            $dc->sub_customer = $sale->sub_customer ?? null;
            
            // ✅ Display logic: Show sub_customer if it exists (walking type), otherwise show registered customer
            if ($dc->sub_customer) {
                $dc->display_customer_name = $dc->sub_customer;
                $dc->is_walking_customer = true;
            } else {
                $dc->display_customer_name = $dc->customer_name;
                $dc->is_walking_customer = false;
            }
            
            // ✅ Get location name (warehouse or branch)
            if ($dc->warehouse_id) {
                // Warehouse delivery
                $warehouse = DB::table('warehouses')->where('id', $dc->warehouse_id)->first();
                $dc->location_name = $warehouse->warehouse_name ?? 'N/A';
                $dc->location_type = 'Warehouse';
            } else {
                // Branch delivery
                $branch = DB::table('branches')->where('id', $dc->branch_id)->first();
                $dc->location_name = $branch->name ?? 'N/A';
                $dc->location_type = 'Branch';
            }
            
            // Check if gate pass already created for this DC
            $gatepass = DB::table('outward_gatepasses')->where('order_id', $dc->id)->first();
            $dc->gatepass_id = $gatepass->id ?? null;
            $dc->has_gatepass = $gatepass ? true : false;
            
            // Get sale items count
            $dc->items_count = DB::table('sale_items')->where('sale_id', $dc->sale_id)->count();
            
            return $dc;
        });

        return view('admin_panel.warehouses.outward_gatepass.index', compact('deliveryChallans'));
    }

    /**
     * Show list of DCs available for gate pass creation
     */
    public function selectDC()
    {
        // Get all warehouse orders without gate passes
        $deliveryChallans = DB::table('warehouse_orders')
            ->leftJoin('outward_gatepasses', 'warehouse_orders.id', '=', 'outward_gatepasses.order_id')
            ->where('outward_gatepasses.id', '=', null) // No gate pass created yet
            ->select('warehouse_orders.*')
            ->orderBy('warehouse_orders.created_at', 'desc')
            ->paginate(20);

        // Enrich with customer and warehouse info
        $deliveryChallans->getCollection()->transform(function ($dc) {
            $dc->customer = DB::table('customers')->where('id', $dc->customer_id)->first();
            $dc->warehouse = DB::table('warehouses')->where('id', $dc->warehouse_id)->first();
            
            // Get count of items from sale
            $sale = optional($dc->sale_id) ? DB::table('sale_items')->where('sale_id', $dc->sale_id)->count() : 0;
            $dc->item_count = $sale;
            
            return $dc;
        });

        // Calculate statistics
        $stats = [
            'totalDCs' => DB::table('warehouse_orders')
                ->leftJoin('outward_gatepasses', 'warehouse_orders.id', '=', 'outward_gatepasses.order_id')
                ->where('outward_gatepasses.id', '=', null)
                ->count(),
            'totalCustomers' => DB::table('warehouse_orders')
                ->leftJoin('outward_gatepasses', 'warehouse_orders.id', '=', 'outward_gatepasses.order_id')
                ->where('outward_gatepasses.id', '=', null)
                ->distinct('customer_id')
                ->count('customer_id'),
            'totalItems' => DB::table('sale_items')
                ->join('warehouse_orders', 'sale_items.sale_id', '=', 'warehouse_orders.sale_id')
                ->leftJoin('outward_gatepasses', 'warehouse_orders.id', '=', 'outward_gatepasses.order_id')
                ->where('outward_gatepasses.id', '=', null)
                ->count(),
        ];

        return view('admin_panel.warehouses.outward_gatepass.select_dc', compact('deliveryChallans', 'stats'));
    }

    public function create($orderId)
    {
        $order = DB::table('warehouse_orders')->where('id', $orderId)->first();
        if (! $order) {
            return redirect()->back()->with('error', 'Order not found');
        }

        // Decode items (may be JSON string or already array)
        $order->items = $order->items ? (is_string($order->items) ? json_decode($order->items, true) : $order->items) : [];

        // Enrich items with product brand/unit where possible
        $productsMap = [];
        $productIds = collect($order->items)->pluck('product_id')->filter()->unique()->values()->all();
        if (!empty($productIds)) {
            // Use DB query to properly join products with units and brands
            $prods = DB::table('products')
                ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->whereIn('products.id', $productIds)
                ->select('products.id', 'products.unit_id', 'units.name as unit_name', 'brands.name as brand_name')
                ->get();
            
            foreach ($prods as $p) {
                $productsMap[$p->id] = [
                    'brand' => $p->brand_name ?? '',
                    'unit' => $p->unit_name ?? '',
                ];
            }
        }

        // ✅ FETCH SALE DATA: Use $order->sale_id to get the Sale record
        $sale = null;
        $prefill = []; // Items to prefill form with
        $prefillData = [
            'invoice_no' => null,
            'customer_name' => null,
            'delivery_city' => null,
            'warehouse_name' => null,
            'delivery_location_type' => 'warehouse', // Default
            'is_walking_customer' => false,
        ];
        
        // ✅ NEW: Fetch location name (warehouse or branch)
        if ($order->warehouse_id) {
            $warehouse = DB::table('warehouses')->where('id', $order->warehouse_id)->first();
            $prefillData['warehouse_name'] = $warehouse->warehouse_name ?? 'N/A';
            $prefillData['delivery_location_type'] = 'Warehouse';
        } else if ($order->branch_id) {
            $branch = DB::table('branches')->where('id', $order->branch_id)->first();
            $prefillData['warehouse_name'] = $branch->name ?? 'N/A';
            $prefillData['delivery_location_type'] = 'Branch';
        }
        
        if (!empty($order->sale_id)) {
            $sale = Sale::with(['customer', 'saleItems.product.brand', 'saleItems.product.unit'])
                ->where('id', $order->sale_id)
                ->first();
            
            // ✅ EXTRACT PREFILL DATA from Sale
            if ($sale) {
                $prefillData['invoice_no'] = $sale->invoice_no ?? null;
                if ($sale->customer) {
                    $prefillData['customer_name'] = $sale->customer->customer_name ?? null;
                    $prefillData['delivery_city'] = $sale->customer->city ?? null;
                }
                // ✅ Also try to get sub_customer if it's a walking type customer
                if ($sale->sub_customer) {
                    $prefillData['customer_name'] = $sale->sub_customer;
                    $prefillData['is_walking_customer'] = true;
                }
            }
            
            // ✅ NEW: Build prefill from DC items, NOT sale items
            // This ensures gatepass shows DC quantities, not original sale quantities
            if (!empty($order->items) && is_array($order->items)) {
                foreach ($order->items as $dcItem) {
                    $productId = $dcItem['product_id'] ?? null;
                    $product = $sale->saleItems->where('product_id', $productId)->first()?->product;
                    $saleItem = $sale->saleItems->where('product_id', $productId)->first();
                    
                    // ✅ FIXED: Calculate remaining accounting for PREVIOUS deliveries
                    $totalSaleQty = $saleItem?->sales_qty ?? 0;
                    $dcQty = (float)($dcItem['qty'] ?? 0);
                    
                    // Get total quantity already delivered via previous gatepasses for this sale + product
                    $previouslyDelivered = DB::table('outward_gatepasses')
                        ->join('warehouse_orders', 'outward_gatepasses.order_id', '=', 'warehouse_orders.id')
                        ->where('warehouse_orders.sale_id', $order->sale_id)
                        ->whereNotNull('outward_gatepasses.items')
                        ->get()
                        ->sum(function($gatepass) use ($productId) {
                            $items = json_decode($gatepass->items, true);
                            return collect($items)->where('product_id', $productId)->sum('qty') ?? 0;
                        });
                    
                    // Remaining = Total - Previous Deliveries - Current Delivery
                    $remainingQty = max(0, $totalSaleQty - $previouslyDelivered - $dcQty);
                    
                    // Log the calculation for audit
                    \Log::info('Outward Gatepass remaining calculation', [
                        'sale_id' => $order->sale_id,
                        'order_id' => $order->id,
                        'product_id' => $productId,
                        'total_sale_qty' => $totalSaleQty,
                        'previously_delivered' => $previouslyDelivered,
                        'current_dc_qty' => $dcQty,
                        'remaining_after_this_gatepass' => $remainingQty,
                    ]);
                    
                    $prefill[] = [
                        'product_id' => $productId,
                        'product_name' => $product?->item_name ?? $dcItem['product_name'] ?? null,
                        'item_code' => $product?->item_code ?? $dcItem['item_code'] ?? null,
                        'brand' => optional($product?->brand)->name ?? $productsMap[$productId]['brand'] ?? '',
                        'unit' => optional($product?->unit)->name ?? $product?->unit_id ?? $productsMap[$productId]['unit'] ?? '',
                        'qty' => $dcQty,  // ← DC qty to deliver
                        'available_qty' => $dcQty,  // ← Available from DC
                        'total_sale_qty' => $totalSaleQty,  // ← For remaining calculation
                        'previously_delivered' => $previouslyDelivered,  // ← For audit trail
                        'remaining_qty' => $remainingQty,  // ← Remaining for future deliveries (after this gatepass)
                    ];
                }
            }
        }
        // return response()->json(['order' => $order, 'sale' => $sale, 'productsMap' => $productsMap, 'prefill' => $prefill, 'prefillData' => $prefillData]);

        
        return view('admin_panel.warehouses.outward_gatepass.create', compact('order', 'sale', 'productsMap', 'prefill', 'prefillData'));
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'order_id' => 'required|integer',
                'remarks' => 'nullable|string',
                'driver_name' => 'nullable|string|max:255',
                'vehicle_number' => 'nullable|string|max:255',
                'vehicle_type' => 'nullable|string|max:255',
                'items_text' => 'nullable|string',
                'issued_by' => 'nullable|string|max:255',
                'warehouse_id' => 'nullable|integer',
                'billty_no' => 'nullable|string|max:255',
                'billty_date' => 'nullable|date',
                'transporter' => 'nullable|string|max:255',
                'billty_amount' => 'nullable|numeric',
                'transport_rent' => 'nullable|numeric',
                'invoice_no' => 'nullable|string|max:255',
                'customer_name' => 'nullable|string|max:255',
                'delivery_city' => 'nullable|string|max:255',
                'gatepass_date' => 'nullable|date',
                'note' => 'nullable|string',
                'product_id' => 'required_without:items_text|array',
                'product_id.*' => 'nullable|integer',
                'qty' => 'required_without:items_text|array',
                'qty.*' => 'nullable|numeric',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Gatepass validation failed:', [
                'errors' => $e->errors(),
                'order_id' => $request->input('order_id'),
            ]);
            throw $e;
        }

        $items = null;
        $remainingItems = []; // Track items with remaining qty

        // Prefer structured rows (product_id[], item_code[], qty[], available_qty[], remaining_qty[], etc.)
        $productIds = $request->input('product_id', []);
        $qtys = $request->input('qty', []);
        
        // Validate that at least one product with qty is provided
        $validProducts = 0;
        foreach ($productIds as $i => $pid) {
            if (!empty($pid) && !empty($qtys[$i])) {
                $validProducts++;
                break;
            }
        }
        
        if ($validProducts === 0 && empty($data['items_text'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', '❌ Please add at least one product with quantity to create a gate pass');
        }

        $itemCodes = $request->input('item_code', []);
        $brands = $request->input('brand', []);
        $units = $request->input('unit', []);
        $remainingQtys = $request->input('remaining_qty', []); // Used qty vs available

        $structured = [];
        // Normalize lengths
        $rows = max(count((array)$productIds), count((array)$itemCodes), count((array)$qtys));
        if ($rows > 0) {
            // Load product names/prices for ids
            $ids = array_values(array_filter((array)$productIds));
            $products = [];
            $productsWithUnits = [];
            if (!empty($ids)) {
                // Fetch products with their unit information from units table
                $productsWithUnits = DB::table('products')
                    ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                    ->whereIn('products.id', $ids)
                    ->select('products.id', 'products.item_name', 'products.price', 'units.name as unit_name', 'products.unit_id')
                    ->get()
                    ->keyBy('id');
            }

            for ($i = 0; $i < $rows; $i++) {
                $pid = $productIds[$i] ?? null;
                $code = $itemCodes[$i] ?? null;
                $brand = $brands[$i] ?? null;
                $unit = $units[$i] ?? null;
                $deliveredQty = isset($qtys[$i]) ? (float) $qtys[$i] : 0;
                $remainingQty = isset($remainingQtys[$i]) ? (float) $remainingQtys[$i] : 0;
                
                if (empty($pid) && empty($code)) continue; // skip empty rows

                $pname = null; 
                $retail = null;
                $dbUnit = null; // Get unit from database
                
                if ($pid && isset($productsWithUnits[$pid])) {
                    $p = $productsWithUnits[$pid];
                    $pname = $p->item_name ?? null;
                    $retail = $p->price ?? null;
                    $dbUnit = $p->unit_name ?? ''; // Get unit name from units table
                }

                // Use unit from DB if not provided from form, otherwise use form value
                $finalUnit = !empty($unit) ? $unit : $dbUnit;

                $structured[] = [
                    'product_id' => $pid ?: null,
                    'product_name' => $pname ?: ($code ?: null),
                    'item_code' => $code ?: null,
                    'brand' => $brand ?: null,
                    'unit' => $finalUnit ?: null,
                    'qty' => $deliveredQty, // Delivered quantity
                    'retail_price' => $retail !== null ? (float)$retail : null,
                    'amount' => $retail !== null ? (float)$retail * $deliveredQty : null,
                ];

                // Track remaining items for later delivery
                if ($remainingQty > 0) {
                    $remainingItems[] = [
                        'product_id' => $pid,
                        'product_name' => $pname ?: ($code ?: null),
                        'item_code' => $code,
                        'brand' => $brand,
                        'unit' => $unit,
                        'remaining_qty' => $remainingQty,
                    ];
                }
            }
        }

        if (!empty($structured)) {
            $items = json_encode($structured);
        } else if (! empty($data['items_text'])) {
            // fallback to previous plain-text lines
            $lines = preg_split('/\r?\n/', $data['items_text']);
            $clean = array_values(array_filter(array_map('trim', $lines), function ($v) { return $v !== ''; }));
            if (! empty($clean)) {
                $items = json_encode($clean);
            }
        }

        // Ensure issued_by defaults to current user if not provided
        if (empty($data['issued_by'])) {
            $data['issued_by'] = auth()->user() ? (auth()->user()->name ?? auth()->user()->email ?? null) : null;
        }

        // Fetch order prepared_by if available
        $orderPreparedBy = null;
        $orderWarehouseId = $data['warehouse_id'] ?? null;
        $orderRow = DB::table('warehouse_orders')->where('id', $data['order_id'])->first();
        
        // Fetch Sale record to get correct customer_id and sub_customer info
        // ✅ IMPORTANT: For walking customers, sale.customer_id is NULL and sub_customer has the name
        $saleRecord = null;
        $saleCustomerId = null;
        $saleSubCustomer = null;
        if ($orderRow && $orderRow->sale_id) {
            $saleRecord = \App\Models\Sale::find($orderRow->sale_id);
            if ($saleRecord) {
                $saleCustomerId = $saleRecord->customer_id; // NULL for walking customers
                $saleSubCustomer = $saleRecord->sub_customer; // Name for walking customers
            }
        }
        
        if ($orderRow) {
            $orderPreparedBy = $orderRow->prepared_by ?? null;
            $orderWarehouseId = $orderWarehouseId ?? $orderRow->warehouse_id ?? null;
        }

        // Get branch_id from warehouse_stocks based on warehouse
        $branchId = DB::table('warehouse_stocks')
            ->where('warehouse_id', $orderWarehouseId)
            ->value('branch_id') ?? 1; // Default to branch 1 if not found

        try {
            // ✅ ATOMIC TRANSACTION - Gatepass creation with stock deduction + remaining tracking
            // Production safety: All or nothing operation
            $id = DB::transaction(function () use ($data, $items, $orderRow, $orderPreparedBy, $orderWarehouseId, $remainingItems, $saleCustomerId, $saleSubCustomer, $branchId) {
                
                // 1️⃣ Create outward gatepass with "delivered" status (NEW)
                $id = DB::table('outward_gatepasses')->insertGetId([
                    'order_id' => $data['order_id'],
                    'branch_id' => $branchId,
                    'dc_no' => $orderRow ? ($orderRow->dc_no ?? null) : null,
                    'remarks' => $data['remarks'] ?? null,
                    'driver_name' => $data['driver_name'] ?? null,
                    'vehicle_number' => $data['vehicle_number'] ?? null,
                    'vehicle_type' => $data['vehicle_type'] ?? null,
                    'items' => $items,
                    'issued_by' => $data['issued_by'] ?? null,
                    'warehouse_id' => $orderWarehouseId,
                    'billty_no' => $data['billty_no'] ?? null,
                    'billty_date' => $data['billty_date'] ?? null,
                    'transporter' => $data['transporter'] ?? null,
                    'billty_amount' => isset($data['billty_amount']) ? (float) $data['billty_amount'] : null,
                    'transport_rent' => isset($data['transport_rent']) ? (float) $data['transport_rent'] : null,
                    'invoice_no' => $data['invoice_no'] ?? null,
                    'customer_name' => $data['customer_name'] ?? null,
                    'delivery_city' => $data['delivery_city'] ?? null,
                    'packing_notes' => $data['note'] ?? null,
                    'prepared_by' => $orderPreparedBy ?? null,
                    'status' => 'delivered', // ✅ NEW: Set status to delivered
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // ✅ Generate branch-specific gatepass number using actual gatepass count
                // Count existing gatepasses for this branch (by gatepass_number, not ID)
                $sequenceNum = DB::table('outward_gatepasses')
                    ->where('branch_id', $branchId)
                    ->whereNotNull('gatepass_number') // Only count records that have been assigned a number
                    ->count() + 1;
                
                $gatepassNumber = sprintf(
                    'GP-%s-%s',
                    str_pad($branchId, 3, '0', STR_PAD_LEFT),
                    str_pad($sequenceNum, 4, '0', STR_PAD_LEFT)
                );
                
                // ✅ Check if this gatepass_number already exists (safety check)
                $exists = DB::table('outward_gatepasses')
                    ->where('gatepass_number', $gatepassNumber)
                    ->where('id', '!=', $id)
                    ->exists();
                
                if ($exists) {
                    // If it already exists, get a new unique number
                    $maxSeq = DB::table('outward_gatepasses')
                        ->where('branch_id', $branchId)
                        ->selectRaw("CAST(SUBSTRING(gatepass_number, -4) AS UNSIGNED) as seq")
                        ->orderBy('seq', 'desc')
                        ->value('seq') ?? 0;
                    
                    $sequenceNum = $maxSeq + 1;
                    $gatepassNumber = sprintf(
                        'GP-%s-%s',
                        str_pad($branchId, 3, '0', STR_PAD_LEFT),
                        str_pad($sequenceNum, 4, '0', STR_PAD_LEFT)
                    );
                }
                
                DB::table('outward_gatepasses')
                    ->where('id', $id)
                    ->update(['gatepass_number' => $gatepassNumber]);

                // 2️⃣ Deduct stock from global stocks table
                if (!empty($items)) {
                    $decodedItems = json_decode($items, true);
                    foreach ($decodedItems as $item) {
                        if (!empty($item['product_id']) && !empty($item['qty'])) {
                            DB::table('stocks')
                                ->where('product_id', $item['product_id'])
                                ->decrement('qty', (float)$item['qty']);
                        }
                    }
                }

                // 3️⃣ Deduct stock from warehouse_stocks table
                if (!empty($items) && !empty($orderWarehouseId)) {
                    $decodedItems = json_decode($items, true);
                    foreach ($decodedItems as $item) {
                        if (!empty($item['product_id']) && !empty($item['qty'])) {
                            DB::table('warehouse_stocks')
                                ->where('warehouse_id', $orderWarehouseId)
                                ->where('product_id', $item['product_id'])
                                ->decrement('quantity', (float)$item['qty']);
                        }
                    }
                }

                // 4️⃣ Delete stock_hold entries for this sale (Cleanup audit trail)
                if (!empty($orderRow) && !empty($orderRow->sale_id)) {
                    DB::table('stock_holds')
                        ->where('sale_id', $orderRow->sale_id)
                        ->delete();
                }

                // 5️⃣ Store/Update remaining items for future delivery (if any) - UPSERT pattern
                // ✅ International ERP Standard: Update existing records instead of creating duplicates
                if (!empty($remainingItems) && !empty($orderRow)) {
                    foreach ($remainingItems as $remainingItem) {
                        // Check if customer_remaining record already exists for this sale+product
                        $existingRecord = DB::table('customer_remaining')
                            ->where('sale_id', $orderRow->sale_id)
                            ->where('product_id', $remainingItem['product_id'])
                            ->first();
                        
                        // Determine new status based on remaining_qty
                        $newStatus = $remainingItem['remaining_qty'] <= 0 ? 'completed' : 'partial';
                        
                        if ($existingRecord) {
                            // ✅ UPDATE: This is a subsequent delivery (partial delivery already created)
                            // Change status from "pending" to "partial" or "completed" based on remaining qty
                            DB::table('customer_remaining')
                                ->where('id', $existingRecord->id)
                                ->update([
                                    'remaining_qty' => max(0, $remainingItem['remaining_qty']), // Ensure non-negative
                                    'status' => $newStatus, // "partial" or "completed"
                                    'last_gatepass_id' => $id, // Track which gate pass updated this
                                    'updated_at' => now(),
                                ]);
                        } else {
                            // ✅ INSERT: First partial delivery - create new customer_remaining record
                            // Status should be "completed" only if all qty was delivered in first gate pass
                            $initialStatus = $remainingItem['remaining_qty'] <= 0 ? 'completed' : 'pending';
                            
                            DB::table('customer_remaining')->insert([
                                'sale_id' => $orderRow->sale_id,
                                'customer_id' => $saleCustomerId, // NULL for walking customers (now nullable)
                                'sub_customer_name' => $saleSubCustomer, // Store walking customer name if present
                                'product_id' => $remainingItem['product_id'],
                                'warehouse_id' => $orderWarehouseId,
                                'remaining_qty' => max(0, $remainingItem['remaining_qty']), // Ensure non-negative
                                'unit' => $remainingItem['unit'],
                                'item_code' => $remainingItem['item_code'],
                                'product_name' => $remainingItem['product_name'],
                                'status' => $initialStatus, // "pending" or "completed"
                                'last_gatepass_id' => $id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }

                // 6️⃣ Mark items as "completed" if they were fully delivered in this gatepass
                // ✅ IMPORTANT: Find items that existed in customer_remaining but remaining_qty became 0
                // These items won't be in remainingItems array (because qty=0 is filtered out)
                if (!empty($orderRow) && !empty($orderRow->sale_id)) {
                    // Get all product IDs that were in this gatepass
                    $deliveredProductIds = [];
                    if (!empty($items)) {
                        $decodedItems = json_decode($items, true);
                        $deliveredProductIds = array_filter(array_column($decodedItems, 'product_id'));
                    }
                    
                    // Find existing pending items from this sale that are in this gatepass
                    // but NOT in remainingItems (meaning they were fully delivered)
                    if (!empty($deliveredProductIds)) {
                        $fullyDeliveredProductIds = array_diff(
                            $deliveredProductIds,
                            array_column($remainingItems, 'product_id')
                        );
                        
                        // Mark these as "completed" 
                        if (!empty($fullyDeliveredProductIds)) {
                            DB::table('customer_remaining')
                                ->where('sale_id', $orderRow->sale_id)
                                ->whereIn('product_id', $fullyDeliveredProductIds)
                                ->whereIn('status', ['pending', 'partial'])
                                ->update([
                                    'remaining_qty' => 0,
                                    'status' => 'completed',
                                    'last_gatepass_id' => $id,
                                    'updated_at' => now(),
                                ]);
                        }
                    }
                }

                // 7️⃣ ✅ UPDATE DC (warehouse_orders) with delivered qty and remaining qty
                // ERP Standard: Track how much has been delivered vs what's pending
                // Scenario: DC has 5 pieces, only 2 undamaged shipped → delivered=2, remaining=3
                if (!empty($orderRow)) {
                    $totalDeliveredInThisGatepass = 0;
                    $totalRemainingInThisGatepass = 0;
                    
                    // Calculate totals from this gatepass
                    if (!empty($items)) {
                        $decodedItems = json_decode($items, true);
                        foreach ($decodedItems as $item) {
                            if (!empty($item['qty'])) {
                                $totalDeliveredInThisGatepass += (float)$item['qty'];
                            }
                        }
                    }
                    
                    // Add remaining items (not delivered in this gatepass)
                    foreach ($remainingItems as $remainingItem) {
                        $totalRemainingInThisGatepass += (float)($remainingItem['remaining_qty'] ?? 0);
                    }
                    
                    // ✅ Get current DC values (if this is a subsequent delivery)
                    $currentDC = DB::table('warehouse_orders')
                        ->where('id', $orderRow->id)
                        ->first();
                    
                    $currentDelivered = (float)($currentDC->delivered_qty ?? 0);
                    $newDeliveredTotal = $currentDelivered + $totalDeliveredInThisGatepass;
                    $newRemainingTotal = $totalRemainingInThisGatepass;
                    
                    // ✅ UPDATE DC with cumulative delivered qty and remaining qty
                    DB::table('warehouse_orders')
                        ->where('id', $orderRow->id)
                        ->update([
                            'delivered_qty' => $newDeliveredTotal,
                            'remaining_qty' => $newRemainingTotal,
                            'updated_at' => now(),
                        ]);
                }

                return $id;
            });
        } catch (\Throwable $e) {
            \Log::error('Gatepass creation failed:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $data['order_id'] ?? null,
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', '❌ Error creating gate pass: ' . $e->getMessage());
        }

        // Notify users who can view outward gatepasses
        try {
            $users = \App\Models\User::permission('outward.gatepass.view')->get();
            if ($users->count()) {
                Notification::send($users, new \App\Notifications\OutwardGatepassCreated($id, $data['order_id']));
            }
        } catch (\Throwable $e) {
            // ignore notification failures
        }

        return redirect()->route('OutwardGatepass.show', $id)->with('success', 'Outward gate pass created');
    }

    /**
     * List all created outward gate passes - ERP Standard
     */
    public function listGatepasses()
    {
        $gatepasses = DB::table('outward_gatepasses')
            ->leftJoin('warehouse_orders', 'outward_gatepasses.order_id', '=', 'warehouse_orders.id')
            ->leftJoin('sales', 'warehouse_orders.sale_id', '=', 'sales.id')
            ->leftJoin('customers', 'warehouse_orders.customer_id', '=', 'customers.id')
            ->leftJoin('warehouses', 'warehouse_orders.warehouse_id', '=', 'warehouses.id')
            ->leftJoin('branches', 'warehouse_orders.branch_id', '=', 'branches.id')
            ->select(
                'outward_gatepasses.id',
                'outward_gatepasses.created_at',
                'outward_gatepasses.driver_name',
                'outward_gatepasses.vehicle_number',
                'outward_gatepasses.issued_by',
                'outward_gatepasses.remarks',
                'outward_gatepasses.transport_receipt_path',
                'warehouse_orders.dc_no',
                'warehouse_orders.warehouse_id',
                'warehouse_orders.created_at as dc_date',
                'customers.customer_name',
                'customers.contact_person',
                'sales.sub_customer',
                'warehouses.warehouse_name',
                'branches.name as branch_name'
            )
            ->orderBy('outward_gatepasses.created_at', 'desc')
            ->paginate(50);

        // Enrich with item counts and display logic
        $gatepasses->getCollection()->transform(function ($gp) {
            $items = DB::table('outward_gatepasses')->where('id', $gp->id)->first();
            $gp->items_array = $items && $items->items ? json_decode($items->items, true) : [];
            $gp->items_count = count($gp->items_array);
            
            // ✅ Display logic: Show sub_customer if it exists (walking type), otherwise show registered customer
            if ($gp->sub_customer) {
                $gp->display_customer_name = $gp->sub_customer;
                $gp->is_walking_customer = true;
            } else {
                $gp->display_customer_name = $gp->customer_name ?? 'N/A';
                $gp->is_walking_customer = false;
            }
            
            return $gp;
        });

        $stats = [
            'total' => DB::table('outward_gatepasses')->count(),
            'thisMonth' => DB::table('outward_gatepasses')
                ->whereYear('created_at', date('Y'))
                ->whereMonth('created_at', date('m'))
                ->count(),
            'totalItems' => DB::table('outward_gatepasses')
                ->where('items', '!=', null)
                ->get()
                ->sum(function ($gp) {
                    $items = json_decode($gp->items, true);
                    return count($items ?? []);
                }),
        ];

        return view('admin_panel.warehouses.outward_gatepass.list', compact('gatepasses', 'stats'));
    }

    public function show($id)
    {
        $gp = DB::table('outward_gatepasses')->where('id', $id)->first();
        if (! $gp) {
            return redirect()->back()->with('error', 'Gate pass not found');
        }
        // decode items JSON for display
        $gp->items = $gp->items ? json_decode($gp->items, true) : [];
        // Convert created_at and updated_at to Carbon instances for proper formatting
        if ($gp->created_at) {
            $gp->created_at = \Carbon\Carbon::parse($gp->created_at);
        }
        if ($gp->updated_at) {
            $gp->updated_at = \Carbon\Carbon::parse($gp->updated_at);
        }
        // load related order to show fallback DC number
        $order = DB::table('warehouse_orders')->where('id', $gp->order_id)->first();
        return view('admin_panel.warehouses.outward_gatepass.show', compact('gp', 'order'));
    }

    public function pdf($id)
    {
        $gp = DB::table('outward_gatepasses')->where('id', $id)->first();
        if (! $gp) {
            return redirect()->back()->with('error', 'Gate pass not found');
        }

        $order = DB::table('warehouse_orders')->where('id', $gp->order_id)->first();

        $gp->items = $gp->items ? json_decode($gp->items, true) : [];

        $pdf = Pdf::loadView('admin_panel.warehouses.outward_gatepass.pdf', compact('gp', 'order'));
        $filename = 'outward_gatepass_'.$gp->id.'.pdf';
        return $pdf->download($filename);
    }

    public function thermal($id)
    {
        $gp = DB::table('outward_gatepasses')->where('id', $id)->first();
        if (! $gp) {
            return redirect()->back()->with('error', 'Gate pass not found');
        }
        $gp->items = $gp->items ? json_decode($gp->items, true) : [];
        $order = DB::table('warehouse_orders')->where('id', $gp->order_id)->first();
        // return response()->json(['gp' => $gp, 'order' => $order]);
        return view('admin_panel.warehouses.outward_gatepass.thermal', compact('gp', 'order'));
    }

    public function updatePackingNotes(Request $request, $id)
    {
        $data = $request->validate([
            'packing_notes' => 'nullable|string|max:2000',
        ]);

        try {
            \DB::table('outward_gatepasses')->where('id', $id)->update([
                'packing_notes' => $data['packing_notes'] ?? null,
                'updated_at' => now(),
            ]);
            return response()->json(['status' => 'ok']);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * ✅ Update delivery status (pending → in_transit → delivered)
     * For proper ERP workflow
     */
    public function updateDeliveryStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,in_transit,delivered',
        ]);

        try {
            \DB::table('outward_gatepasses')->where('id', $id)->update([
                'status' => $data['status'],
                'updated_at' => now(),
            ]);

            // ✅ If marked as delivered, update related warehouse_order status too
            if ($data['status'] === 'delivered') {
                $gp = \DB::table('outward_gatepasses')->where('id', $id)->first();
                if ($gp) {
                    \DB::table('warehouse_orders')->where('id', $gp->order_id)->update([
                        'status' => 'delivered',
                        'updated_at' => now(),
                    ]);
                }
            }

            if ($request->wantsJson()) {
                return response()->json(['status' => 'ok', 'message' => 'Delivery status updated']);
            }

            return redirect()->back()->with('success', 'Delivery status updated to ' . $data['status']);
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Could not update delivery status: ' . $e->getMessage());
        }
    }

    /**
     * Get available stock for a product in a warehouse (for AJAX)
     */
    public function getWarehouseStock(Request $request)
    {
        $productId = $request->input('product_id');
        $warehouseId = $request->input('warehouse_id');

        if (!$productId || !$warehouseId) {
            return response()->json(['quantity' => 0], 200);
        }

        $stock = DB::table('warehouse_stocks')
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        return response()->json([
            'quantity' => $stock ? (float)$stock->quantity : 0,
            'product_id' => $productId,
            'warehouse_id' => $warehouseId
        ], 200);
    }

    /**
     * Create gate pass from remaining items
     */
    public function createFromRemaining($remainingId)
    {
        $remaining = \App\Models\CustomerRemaining::findOrFail($remainingId);
        
        // ✅ ERP RESTRICTION: Check if gatepass creation is allowed
        // Rule 1: This item must not be fully delivered
        if ($remaining->status == 'completed' || $remaining->remaining_qty <= 0) {
            return redirect()->back()->with('error', 'Cannot create gate pass: This item has already been fully delivered.');
        }
        
        // Rule 2: Not all items from this sale should be completed
        $allSaleItems = \App\Models\CustomerRemaining::where('sale_id', $remaining->sale_id)->get();
        $completedCount = $allSaleItems->filter(fn($i) => $i->status == 'completed')->count();
        $totalCount = $allSaleItems->count();
        
        if ($completedCount == $totalCount && $totalCount > 0) {
            return redirect()->back()->with('error', 'Cannot create gate pass: All items from this sale have been delivered.');
        }

        // ✅ Get Sale with full details (for invoice_no, customer_name, etc)
        $sale = \App\Models\Sale::with(['customer', 'saleItems.product.brand', 'saleItems.product.unit'])
            ->where('id', $remaining->sale_id)
            ->first();
        
        if (!$sale) {
            return redirect()->back()->with('error', 'Associated sale not found');
        }

        // Get all pending items for this sale
        $pendingItems = \App\Models\CustomerRemaining::where('sale_id', $remaining->sale_id)
            ->whereIn('status', ['pending', 'partial'])
            ->where('remaining_qty', '>', 0)  // ✅ Only items with remaining qty
            ->with(['product.brand', 'product.unit', 'warehouse'])  // ✅ Eager load product relationships
            ->get();

        // Get the latest warehouse order (DC) for this sale
        $order = DB::table('warehouse_orders')
            ->where('sale_id', $remaining->sale_id)
            ->orderBy('created_at', 'desc')
            ->first();
        
        if (!$order) {
            return redirect()->back()->with('error', 'Associated warehouse order not found');
        }

        // ✅ BUILD PREFILL DATA (header info: invoice_no, customer_name, delivery_city)
        $prefillData = [
            'invoice_no' => $sale->invoice_no ?? null,
            'customer_name' => $sale->customer?->customer_name ?? null,
            'delivery_city' => $sale->customer?->city ?? null,
        ];

        // Build productsMap for template (brand, unit, item_name, item_code)
        $productsMap = [];
        $productIds = $pendingItems->pluck('product_id')->unique()->all();
        if (!empty($productIds)) {
            $prods = \App\Models\Product::with(['brand', 'unit'])->whereIn('id', $productIds)->get();
            foreach ($prods as $p) {
                $productsMap[$p->id] = [
                    'product_name' => $p->item_name ?? '',        // ✅ Product name from Product model
                    'item_code' => $p->item_code ?? '',            // ✅ Item code from Product model
                    'brand' => $p->brand->name ?? ($p->brand_name ?? ''),
                    'unit' => isset($p->unit) && is_object($p->unit) ? ($p->unit->name ?? ($p->unit->unit ?? null)) : ($p->unit ?? ($p->unit_id ?? '')),
                ];
            }
        }

        // ✅ FORMAT ITEMS for prefill (from customer_remaining, not original sale items)
        // This ensures we're only delivering the remaining qty, not the original qty
        // Use product relationship for denormalized fields if empty
        $prefill = $pendingItems->map(function ($item) use ($productsMap) {
            return [
                'product_id' => $item->product_id,
                // ✅ Use from DB first, fallback to Product model lookup
                'product_name' => $item->product_name ?: ($productsMap[$item->product_id]['product_name'] ?? ''),
                'item_code' => $item->item_code ?: ($productsMap[$item->product_id]['item_code'] ?? ''),
                'brand' => $productsMap[$item->product_id]['brand'] ?? '',
                'unit' => $item->unit ?: ($productsMap[$item->product_id]['unit'] ?? ''),
                'qty' => $item->remaining_qty, // Pre-fill with remaining qty only
                'available_qty' => $item->remaining_qty,
            ];
        })->toArray();

        return view('admin_panel.warehouses.outward_gatepass.create', [
            'order' => $order,
            'sale' => $sale,
            'prefillData' => $prefillData,  // ✅ NEW: Pass prefill data with invoice_no, customer_name
            'prefill' => $prefill,           // ✅ Pass items
            'from_remaining' => true,
        ]);
    }

    /**
     * Get gate pass data as JSON for delivery receipt modal
     * ✅ ERP STANDARD: API endpoint for delivery receipt modal
     */
    public function getDeliveryReceipt($id)
    {
        // Fetch gate pass with customer details via JOIN
        $gp = DB::table('outward_gatepasses')
            ->leftJoin('warehouse_orders', 'outward_gatepasses.order_id', '=', 'warehouse_orders.id')
            ->leftJoin('customers', 'warehouse_orders.customer_id', '=', 'customers.id')
            ->leftJoin('warehouses', 'outward_gatepasses.warehouse_id', '=', 'warehouses.id')
            ->select(
                'outward_gatepasses.*',
                'warehouse_orders.dc_no as warehouse_order_dc_no',
                'customers.customer_name',
                'customers.contact_person',
                'warehouses.warehouse_name'
            )
            ->where('outward_gatepasses.id', $id)
            ->first();
            
        if (! $gp) {
            return response()->json(['error' => 'Gate pass not found'], 404);
        }

        // Decode items from JSON
        $items = $gp->items ? json_decode($gp->items, true) : [];

        return response()->json([
            'id' => $gp->id,
            'order_id' => $gp->order_id,
            'warehouse_id' => $gp->warehouse_id,
            'warehouse_name' => $gp->warehouse_name,
            'dc_no' => $gp->dc_no ?? $gp->warehouse_order_dc_no ?? null,
            'invoice_no' => $gp->invoice_no,
            'customer_name' => $gp->customer_name,
            'delivery_city' => $gp->delivery_city,
            'contact_person' => $gp->contact_person,
            'driver_name' => $gp->driver_name,
            'vehicle_number' => $gp->vehicle_number,
            'vehicle_type' => $gp->vehicle_type,
            'transporter' => $gp->transporter,
            'billty_no' => $gp->billty_no,
            'billty_date' => $gp->billty_date,
            'billty_amount' => $gp->billty_amount,
            'transport_rent' => $gp->transport_rent,
            'issued_by' => $gp->issued_by,
            'packing_notes' => $gp->packing_notes,
            'remarks' => $gp->remarks,
            'items' => $items,
            'created_at' => $gp->created_at,
            'updated_at' => $gp->updated_at,
        ]);
    }

    /**
     * Upload handwritten transport receipt image
     * ✅ ERP STANDARD: API endpoint for transport receipt upload
     */
    public function uploadTransportReceipt(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'id' => 'required|exists:outward_gatepasses,id',
                'receipt_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120' // 5MB max
            ]);

            // Get the gate pass
            $id = $validated['id'];
            $gp = DB::table('outward_gatepasses')->where('id', $id)->first();
            
            if (!$gp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gate pass not found'
                ], 404);
            }

            // Delete old receipt if exists
            if ($gp->transport_receipt_path && \Storage::exists('public/' . $gp->transport_receipt_path)) {
                \Storage::delete('public/' . $gp->transport_receipt_path);
            }

            // Store the new receipt
            $file = $request->file('receipt_image');
            $fileName = 'receipt_' . $id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('transport_receipts', $fileName, 'public');

            // Update gate pass with receipt path
            DB::table('outward_gatepasses')
                ->where('id', $id)
                ->update([
                    'transport_receipt_path' => $filePath
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Transport receipt uploaded successfully',
                'receipt_path' => $filePath,
                'receipt_url' => \Storage::url($filePath)
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Transport receipt upload error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error uploading receipt: ' . $e->getMessage()
            ], 500);
        }
    }
}
