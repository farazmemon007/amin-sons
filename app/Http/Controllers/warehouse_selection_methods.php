<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════════════════
 * 🎯 WAREHOUSE SELECTION METHODS FOR SALE
 * ═══════════════════════════════════════════════════════════════════════════════════════
 * 
 * Two new methods for handling warehouse selection flow:
 * 1. showWarehouseSelection() - Display warehouse selection form
 * 2. processWarehouseSelection() - Process warehouse selection and create DC + stock_hold
 * 
 * These methods support the "draft_posted" sale workflow where:
 * - Sale is created without warehouse and stock deduction
 * - User then selects warehouse for each product
 * - Upon selection, DC is created and stock_hold entries recorded
 * - Stock can then be deducted or kept on hold for later processing
 */

// Add these two methods to the SaleController class:

/**
 * ✅ SHOW WAREHOUSE SELECTION FORM
 * 
 * Display a form where user can select warehouses for each product in a draft_posted sale
 * Before a DC can be generated, user must select the warehouse for delivery
 */
public function showWarehouseSelection($saleId)
{
    try {
        $sale = Sale::with(['customer', 'saleItems.product'])
            ->findOrFail($saleId);

        // Only allow warehouse selection for draft_posted sales
        if ($sale->status !== 'draft_posted') {
            return back()->with('error', 'This sale is not in draft_posted status. Cannot select warehouse.');
        }

        // Check if WarehouseOrder already exists (means warehouse already selected)
        $existingOrders = \App\Models\WarehouseOrder::where('sale_id', $sale->id)->exists();
        if ($existingOrders) {
            return redirect()->route('sale.dc', $sale->id)
                ->with('info', 'Warehouse already selected. Generating DC...');
        }

        // Fetch all warehouses that have products from this sale with available stock
        $saleProductIds = $sale->saleItems->pluck('product_id')->toArray();
        
        $warehouseStocks = WarehouseStock::whereIn('product_id', $saleProductIds)
            ->where('quantity', '>', 0)
            ->with('warehouse', 'product')
            ->get()
            ->groupBy('product_id');

        Log::info('Showing warehouse selection form', [
            'sale_id' => $sale->id,
            'invoice' => $sale->invoice_no,
            'items_count' => $sale->saleItems->count(),
            'warehouses_with_stock' => $warehouseStocks->count()
        ]);

        return view('admin_panel.sale.warehouse_select', [
            'sale' => $sale,
            'warehouseStocks' => $warehouseStocks,
        ]);
    } catch (\Exception $e) {
        Log::error('Error showing warehouse selection', [
            'error' => $e->getMessage(),
            'sale_id' => $saleId
        ]);
        return back()->with('error', 'Error: ' . $e->getMessage());
    }
}

/**
 * ✅ PROCESS WAREHOUSE SELECTION
 * 
 * Process the warehouse selection submission:
 * 1. Update sale_items with warehouse_id
 * 2. Create WarehouseOrder (DC)
 * 3. Create StockHold entries for audit trail
 * 4. Redirect to DC view/print
 */
public function processWarehouseSelection(Request $request, $saleId)
{
    try {
        return DB::transaction(function () use ($request, $saleId) {

            $sale = Sale::with(['customer', 'saleItems.product'])
                ->lockForUpdate()
                ->findOrFail($saleId);

            // Validate sale status
            if ($sale->status !== 'draft_posted') {
                abort(422, 'Sale must be in draft_posted status');
            }

            // Validate warehouse selection provided
            $warehouseMap = $request->input('warehouse_id');
            if (!$warehouseMap || !is_array($warehouseMap)) {
                abort(422, 'Warehouse selection required for all products');
            }

            Log::info('Processing warehouse selection', [
                'sale_id' => $sale->id,
                'invoice' => $sale->invoice_no,
                'warehouse_count' => count($warehouseMap)
            ]);

            /* ========== STEP 1: UPDATE SALE ITEMS WITH WAREHOUSE IDS ========== */
            foreach ($sale->saleItems as $item) {
                $selectedWarehouse = $warehouseMap[$item->product_id] ?? null;

                if (!$selectedWarehouse) {
                    abort(422, 'Warehouse must be selected for product: ' . optional($item->product)->item_name);
                }

                // Update sale_item with selected warehouse
                $item->update([
                    'warehouse_id' => (int) $selectedWarehouse,
                ]);

                Log::info('Updated sale item warehouse', [
                    'sale_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'warehouse_id' => $selectedWarehouse,
                    'qty' => $item->sales_qty
                ]);
            }

            // Reload to get updated warehouse_ids
            $sale->load('saleItems.product');

            /* ========== STEP 2: CREATE WAREHOUSE ORDER (DC) ========== */
            // Group items by warehouse
            $groupedByWarehouse = $sale->saleItems->groupBy('warehouse_id');

            $dcNumbers = [];

            foreach ($groupedByWarehouse as $warehouseId => $items) {
                // Generate DC number using branch counter
                $branch = Branch::lockForUpdate()->find($sale->branch_id ?? 1) ?? Branch::lockForUpdate()->first();
                
                $branch->dc_counter = ($branch->dc_counter ?? 0) + 1;
                $branch->save();
                
                $dcNo = 'DC-' . str_pad($branch->dc_counter, 4, '0', STR_PAD_LEFT);

                // Build items array for WarehouseOrder
                $itemsArray = $items->map(function($item) {
                    return [
                        'sale_item_id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => optional($item->product)->item_name,
                        'item_code' => optional($item->product)->item_code,
                        'qty' => $item->sales_qty,
                        'warehouse_id' => $item->warehouse_id,
                        'retail_price' => $item->retail_price,
                        'amount' => $item->amount,
                    ];
                })->values()->toArray();

                // Create WarehouseOrder record
                $warehouseOrder = \App\Models\WarehouseOrder::create([
                    'dc_no' => $dcNo,
                    'warehouse_id' => (int) $warehouseId,
                    'customer_id' => $sale->customer_id,
                    'sale_id' => $sale->id,
                    'status' => 'pending',
                    'remarks' => $sale->remarks ?? null,
                    'prepared_by' => auth()->user()->name ?? null,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                    'items' => $itemsArray,
                ]);

                Log::info('Created warehouse order (DC)', [
                    'dc_no' => $dcNo,
                    'warehouse_id' => $warehouseId,
                    'warehouse_order_id' => $warehouseOrder->id,
                    'items_count' => count($itemsArray)
                ]);

                $dcNumbers[] = $dcNo;

                /* ========== STEP 3: CREATE STOCK HOLD ENTRIES ========== */
                // For each item, create a stock_hold record showing current availability
                foreach ($items as $item) {
                    // Get current warehouse stock
                    $warehouseStock = WarehouseStock::where('product_id', $item->product_id)
                        ->where('warehouse_id', $warehouseId)
                        ->first();

                    $availableQty = $warehouseStock ? (float)$warehouseStock->quantity : 0;
                    $deliverQty = (float)$item->sales_qty;
                    $remainingQty = max(0, $availableQty - $deliverQty);

                    \App\Models\StockHold::create([
                        'sale_id' => $sale->id,
                        'warehouse_order_id' => $warehouseOrder->id,
                        'product_id' => $item->product_id,
                        'warehouse_id' => $warehouseId,
                        'customer_id' => $sale->customer_id,
                        'invoice_no' => $sale->invoice_no,
                        'dc_no' => $dcNo,
                        'available_qty' => $availableQty,
                        'deliver_qty' => $deliverQty,
                        'remaining_qty' => $remainingQty,
                        'product_name' => optional($item->product)->item_name,
                        'product_code' => optional($item->product)->item_code,
                        'unit_price' => $item->retail_price ?? 0,
                        'remarks' => $sale->remarks ?? null,
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                    ]);

                    Log::info('Stock hold created', [
                        'product_id' => $item->product_id,
                        'invoice_no' => $sale->invoice_no,
                        'dc_no' => $dcNo,
                        'available' => $availableQty,
                        'deliver' => $deliverQty,
                        'remaining' => $remainingQty
                    ]);
                }
            }

            /* ========== STEP 4: UPDATE SALE STATUS ========== */
            // Mark sale as having warehouse selected
            $sale->update([
                'status' => 'warehouse_selected',
                'updated_at' => now(),
            ]);

            Log::info('Warehouse selection completed', [
                'sale_id' => $sale->id,
                'invoice' => $sale->invoice_no,
                'dc_numbers' => $dcNumbers
            ]);

            /* ========== STEP 5: RESPOND ========== */
            return response()->json([
                'ok' => true,
                'message' => 'Warehouse selected successfully! DC will now be generated.',
                'sale_id' => $sale->id,
                'dc_data' => [
                    'dc_numbers' => $dcNumbers,
                    'redirect_url' => route('sale.dc', $sale->id)
                ]
            ]);

        });
    } catch (\Exception $e) {
        Log::error('Warehouse selection processing failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'sale_id' => $saleId
        ]);

        $status = 422;
        if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
            $status = $e->getStatusCode();
        }

        return response()->json([
            'ok' => false,
            'error' => $e->getMessage()
        ], $status);
    }
}

// End of warehouse selection methods
