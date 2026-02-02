<?php

use App\Models\Notification;
use App\Models\Product;
use App\Models\WarehouseStock;
use App\Services\StockAlertService;
use Illuminate\Support\Facades\DB;

// This file tests stock alert service
echo "=== TESTING STOCK ALERT ===\n";

// Test 1: Check if service can be called
echo "\n1. Testing StockAlertService::checkAndCreateAlert(4, 1)...\n";
try {
    StockAlertService::checkAndCreateAlert(4, 1);
    echo "✓ Service called successfully\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Test 2: Check if notification was created
echo "\n2. Checking notifications table...\n";
$notifs = Notification::where('product_id', 4)
    ->where('type', 'product_stock_alert')
    ->orderBy('created_at', 'desc')
    ->get();

if ($notifs->isEmpty()) {
    echo "❌ No notifications found for product 4\n";
} else {
    echo "✓ Found " . $notifs->count() . " notifications\n";
    $notifs->each(function($n) {
        echo "  - ID: {$n->id}, Status: {$n->status}, Created: {$n->created_at}\n";
    });
}

// Test 3: Check product details
echo "\n3. Product details...\n";
$product = Product::find(4);
echo "Product: {$product->item_name}\n";
echo "Alert Qty: {$product->alert_quantity}\n";

// Test 4: Manual check
echo "\n4. Manual alert condition check...\n";
$totalStock = WarehouseStock::where('product_id', 4)->sum('quantity');
echo "Total Stock: {$totalStock}\n";

if ($product->alert_quantity && $totalStock <= $product->alert_quantity) {
    echo "✓ Alert condition IS MET\n";
    
    // Check if we need to create notification
    $existingNotif = Notification::where('type', 'product_stock_alert')
        ->where('product_id', 4)
        ->whereDate('created_at', DB::raw('CURDATE()'))
        ->where('status', 'pending')
        ->first();
    
    if ($existingNotif) {
        echo "❌ Notification already exists for today (ID: {$existingNotif->id})\n";
    } else {
        echo "✓ No notification exists for today, should create one\n";
    }
} else {
    echo "❌ Alert condition NOT met\n";
}

echo "\n=== END TEST ===\n";
