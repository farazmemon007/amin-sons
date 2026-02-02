<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Product;
use App\Models\WarehouseStock;
use Carbon\Carbon;

class StockAlertService
{
    /**
     * Check product stock and create notification if below alert quantity
     * Call this whenever stock is updated
     * 
     * @param int $productId
     * @param int $warehouseId (optional)
     * @return void
     */
    public static function checkAndCreateAlert($productId, $warehouseId = null)
    {
        try {
            $product = Product::find($productId);
            
            if (!$product || !$product->alert_quantity) {
                return; // No product or no alert quantity set
            }

            // Get current stock
            if ($warehouseId) {
                // Get stock from specific warehouse
                $stock = WarehouseStock::where('product_id', $productId)
                    ->where('warehouse_id', $warehouseId)
                    ->first();
                $currentQty = $stock?->quantity ?? 0;
            } else {
                // Get total stock from all warehouses
                $totalStock = WarehouseStock::where('product_id', $productId)
                    ->sum('quantity');
                $currentQty = $totalStock ?? 0;
            }

            // Check if below alert quantity
            if ($currentQty <= $product->alert_quantity) {
                // Check if notification already exists for today
                $existingNotif = Notification::where('type', 'product_stock_alert')
                    ->where('product_id', $productId)
                    ->whereDate('created_at', Carbon::today())
                    ->where('status', 'pending')
                    ->first();

                // Only create if notification doesn't exist for today
                if (!$existingNotif) {
                    Notification::create([
                        'product_id' => $productId,
                        'warehouse_id' => $warehouseId,
                        'type' => 'product_stock_alert',
                        'title' => 'Stock Alert - ' . $product->item_name,
                        'description' => 'Product "' . $product->item_name . '" stock is now ' . $currentQty . ' units (Alert: ' . $product->alert_quantity . ')',
                        'notification_date' => Carbon::today(),
                        'status' => 'pending',
                        'created_by' => auth()->id() ?? 1, // System user if not authenticated
                    ]);
                }
            } else {
                // If stock is above alert, dismiss any pending alert notifications for this product
                Notification::where('type', 'product_stock_alert')
                    ->where('product_id', $productId)
                    ->where('status', 'pending')
                    ->update(['status' => 'dismissed']);
            }
        } catch (\Exception $e) {
            // Log error but don't break the flow
            \Log::error('Stock alert check failed: ' . $e->getMessage());
        }
    }

    /**
     * Get all pending stock alerts
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getPendingStockAlerts()
    {
        return Notification::where('type', 'product_stock_alert')
            ->where('status', 'pending')
            ->with('product', 'warehouse')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get stock alerts for specific product
     * 
     * @param int $productId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAlertsForProduct($productId)
    {
        return Notification::where('type', 'product_stock_alert')
            ->where('product_id', $productId)
            ->with('product', 'warehouse')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
