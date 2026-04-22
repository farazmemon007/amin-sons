<?php

namespace App\Services;

use App\Models\CustomerLedger;
use App\Models\ReceiptsVoucher;
use App\Models\SaleItem;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\WarehouseStock;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WarehouseSelectionService
{
    /**
     * Process warehouse selections from qty changes
     * 
     * Handles:
     * - Stock deductions from specific warehouses
     * - Stock In-Hand updates
     * - Customer ledger entries
     * - Receipt voucher creation
     * 
     * @param int $saleId
     * @param int $customerId
     * @param string|array $warehouseSelectionsJSON
     * @param float $totalBalance
     * @return array
     */
    public function processWarehouseSelections($saleId, $customerId, $warehouseSelectionsJSON, $totalBalance = 0)
    {
        return DB::transaction(function () use ($saleId, $customerId, $warehouseSelectionsJSON, $totalBalance) {
            
            // Parse warehouse selections
            $selections = $this->parseSelections($warehouseSelectionsJSON);
            
            if (empty($selections)) {
                return [
                    'success' => false,
                    'message' => 'No warehouse selections provided'
                ];
            }

            $processedItems = [];
            
            /**
             * Loop through each product with qty changes
             */
            foreach ($selections as $productId => $data) {
                $action = $data['action'] ?? null;
                $neededQty = $data['qty_needed'] ?? $data['qty_to_remove'] ?? 0;
                $warehouseList = $data['selections'] ?? [];

                if (!$action || !$neededQty) {
                    continue;
                }

                if ($action === 'increase') {
                    $this->handleQtyIncrease($saleId, $productId, $neededQty, $warehouseList);
                } elseif ($action === 'decrease') {
                    $this->handleQtyDecrease($saleId, $productId, $neededQty, $warehouseList);
                }

                $processedItems[] = [
                    'product_id' => $productId,
                    'action' => $action,
                    'qty' => $neededQty
                ];
            }

            /**
             * Update Customer Ledger with total balance change
             */
            if ($totalBalance != 0) {
                $this->updateCustomerLedger($customerId, $totalBalance);
            }

            return [
                'success' => true,
                'message' => 'Warehouse selections processed successfully',
                'processed_items' => $processedItems
            ];
        });
    }

    /**
     * Handle quantity increase
     * Deduct stock from multiple warehouses
     */
    private function handleQtyIncrease($saleId, $productId, $qtyNeeded, $warehouseList)
    {
        Log::info('Processing QTY INCREASE', [
            'sale_id' => $saleId,
            'product_id' => $productId,
            'qty_needed' => $qtyNeeded,
            'warehouse_count' => count($warehouseList)
        ]);

        $totalDeducted = 0;

        /**
         * Deduct from each warehouse
         */
        foreach ($warehouseList as $warehouseItem) {
            $warehouseId = $warehouseItem['warehouse_id'] ?? null;
            $warehouseName = $warehouseItem['warehouse_name'] ?? 'Unknown';
            $qtyToDeduct = $warehouseItem['quantity'] ?? 0;

            if (!$warehouseId || !$qtyToDeduct) {
                continue;
            }

            /**
             * Update Warehouse Stock
             */
            $warehouseStock = WarehouseStock::lockForUpdate()
                ->where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->first();

            if ($warehouseStock) {
                $oldQty = $warehouseStock->quantity;
                $warehouseStock->quantity -= $qtyToDeduct;
                $warehouseStock->save();

                Log::info('Updated warehouse stock', [
                    'warehouse_id' => $warehouseId,
                    'warehouse_name' => $warehouseName,
                    'product_id' => $productId,
                    'old_qty' => $oldQty,
                    'deducted' => $qtyToDeduct,
                    'new_qty' => $warehouseStock->quantity
                ]);
            }

            /**
             * Update Main Stock (Stock In-Hand)
             */
            $mainStock = Stock::lockForUpdate()
                ->where('product_id', $productId)
                ->first();

            if ($mainStock) {
                $oldStockQty = $mainStock->qty;
                $mainStock->qty -= $qtyToDeduct;
                $mainStock->save();

                Log::info('Updated main stock', [
                    'product_id' => $productId,
                    'old_qty' => $oldStockQty,
                    'deducted' => $qtyToDeduct,
                    'new_qty' => $mainStock->qty
                ]);
            } else {
                // Create new stock record if doesn't exist
                Stock::create([
                    'branch_id' => 1,
                    'warehouse_id' => $warehouseId,
                    'product_id' => $productId,
                    'qty' => -$qtyToDeduct,
                    'reserved_qty' => 0
                ]);
            }

            /**
             * Create Stock Movement Record
             */
            StockMovement::create([
                'product_id' => $productId,
                'type' => 'out',
                'qty' => $qtyToDeduct,
                'ref_type' => 'SALE_QTY_INCREASE',
                'ref_id' => $saleId,
                'ref_uuid' => 'WAREHOUSE-' . $warehouseId . '-' . $productId,
                'warehouse_id' => $warehouseId,
                'note' => "Qty increase from {$warehouseName} for Sale #{$saleId}"
            ]);

            $totalDeducted += $qtyToDeduct;
        }

        Log::info('QTY INCREASE completed', [
            'sale_id' => $saleId,
            'product_id' => $productId,
            'total_deducted' => $totalDeducted
        ]);
    }

    /**
     * Handle quantity decrease
     * Return stock to selected warehouses
     */
    private function handleQtyDecrease($saleId, $productId, $qtyToReturn, $warehouseList)
    {
        Log::info('Processing QTY DECREASE', [
            'sale_id' => $saleId,
            'product_id' => $productId,
            'qty_to_return' => $qtyToReturn,
            'warehouse_count' => count($warehouseList)
        ]);

        /**
         * If no specific warehouses selected, distribute equally
         */
        if (empty($warehouseList)) {
            // Return from all warehouses proportionally
            $warehouseStocks = WarehouseStock::where('product_id', $productId)
                ->get();

            if ($warehouseStocks->isEmpty()) {
                // If no warehouse stock exists, just add to main stock
                $mainStock = Stock::lockForUpdate()
                    ->where('product_id', $productId)
                    ->first();

                if ($mainStock) {
                    $mainStock->qty += $qtyToReturn;
                    $mainStock->save();
                } else {
                    Stock::create([
                        'branch_id' => 1,
                        'product_id' => $productId,
                        'qty' => $qtyToReturn,
                        'reserved_qty' => 0
                    ]);
                }

                StockMovement::create([
                    'product_id' => $productId,
                    'type' => 'in',
                    'qty' => $qtyToReturn,
                    'ref_type' => 'SALE_QTY_DECREASE',
                    'ref_id' => $saleId,
                    'note' => "Qty decrease for Sale #{$saleId} - No warehouse specified"
                ]);

                return;
            }

            // Distribute qty equally among warehouses
            $qtyPerWarehouse = intval($qtyToReturn / $warehouseStocks->count());
            $remainder = $qtyToReturn % $warehouseStocks->count();

            foreach ($warehouseStocks as $idx => $ws) {
                $addQty = $qtyPerWarehouse + ($idx === 0 ? $remainder : 0);
                if ($addQty <= 0) continue;

                $ws->quantity += $addQty;
                $ws->save();

                // Update main stock
                $mainStock = Stock::lockForUpdate()
                    ->where('product_id', $productId)
                    ->first();
                
                if ($mainStock) {
                    $mainStock->qty += $addQty;
                    $mainStock->save();
                }

                StockMovement::create([
                    'product_id' => $productId,
                    'type' => 'in',
                    'qty' => $addQty,
                    'ref_type' => 'SALE_QTY_DECREASE',
                    'ref_id' => $saleId,
                    'warehouse_id' => $ws->warehouse_id,
                    'note' => "Qty decrease returned to warehouse for Sale #{$saleId}"
                ]);
            }
        } else {
            /**
             * Return to specific selected warehouses
             */
            foreach ($warehouseList as $warehouseItem) {
                $warehouseId = $warehouseItem['warehouse_id'] ?? null;
                $warehouseName = $warehouseItem['warehouse_name'] ?? 'Unknown';
                $qtyToAdd = $warehouseItem['quantity'] ?? 0;

                if (!$warehouseId || !$qtyToAdd) {
                    continue;
                }

                // Update warehouse stock
                $warehouseStock = WarehouseStock::lockForUpdate()
                    ->where('product_id', $productId)
                    ->where('warehouse_id', $warehouseId)
                    ->first();

                if ($warehouseStock) {
                    $oldQty = $warehouseStock->quantity;
                    $warehouseStock->quantity += $qtyToAdd;
                    $warehouseStock->save();

                    Log::info('Returned qty to warehouse', [
                        'warehouse_id' => $warehouseId,
                        'old_qty' => $oldQty,
                        'added' => $qtyToAdd,
                        'new_qty' => $warehouseStock->quantity
                    ]);
                } else {
                    WarehouseStock::create([
                        'warehouse_id' => $warehouseId,
                        'product_id' => $productId,
                        'quantity' => $qtyToAdd
                    ]);
                }

                // Update main stock
                $mainStock = Stock::lockForUpdate()
                    ->where('product_id', $productId)
                    ->first();

                if ($mainStock) {
                    $mainStock->qty += $qtyToAdd;
                    $mainStock->save();
                }

                StockMovement::create([
                    'product_id' => $productId,
                    'type' => 'in',
                    'qty' => $qtyToAdd,
                    'ref_type' => 'SALE_QTY_DECREASE',
                    'ref_id' => $saleId,
                    'warehouse_id' => $warehouseId,
                    'note' => "Qty decrease returned from {$warehouseName} for Sale #{$saleId}"
                ]);
            }
        }

        Log::info('QTY DECREASE completed', [
            'sale_id' => $saleId,
            'product_id' => $productId,
            'qty_returned' => $qtyToReturn
        ]);
    }

    /**
     * Update Customer Ledger with balance change
     */
    private function updateCustomerLedger($customerId, $balanceChange)
    {
        $lastLedger = CustomerLedger::where('customer_id', $customerId)
            ->latest('id')
            ->lockForUpdate()
            ->first();

        $previousBalance = $lastLedger->closing_balance ?? 0;
        $newClosing = $previousBalance + $balanceChange;

        CustomerLedger::create([
            'customer_id' => $customerId,
            'admin_or_user_id' => auth()->id(),
            'previous_balance' => $previousBalance,
            'opening_balance' => 0,
            'closing_balance' => $newClosing,
            'note' => 'Updated from sale qty change'
        ]);

        Log::info('Updated customer ledger', [
            'customer_id' => $customerId,
            'previous' => $previousBalance,
            'change' => $balanceChange,
            'new_closing' => $newClosing
        ]);
    }

    /**
     * Parse warehouse selections from JSON
     */
    private function parseSelections($warehouseSelectionsJSON)
    {
        if (empty($warehouseSelectionsJSON)) {
            return [];
        }

        if (is_string($warehouseSelectionsJSON)) {
            try {
                return json_decode($warehouseSelectionsJSON, true) ?? [];
            } catch (\Exception $e) {
                Log::error('Failed to parse warehouse selections JSON', [
                    'error' => $e->getMessage(),
                    'json' => $warehouseSelectionsJSON
                ]);
                return [];
            }
        }

        return $warehouseSelectionsJSON;
    }
}
