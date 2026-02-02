<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\WarehouseStock;
use Illuminate\Console\Command;

class DebugStockAlert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:stock-alert {--product_name= : Product name to debug}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Debug stock alert for a specific product';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $productName = $this->option('product_name') ?? 'laptop';
        
        $this->info("=== STOCK ALERT DEBUG ===\n");
        $this->info("Searching for product: {$productName}\n");

        // Find product
        $product = Product::where('item_name', 'like', "%{$productName}%")->first();

        if (!$product) {
            $this->error("❌ Product NOT found!");
            $this->info("\nAll products:");
            Product::select('id', 'item_name', 'alert_quantity')->get()
                ->each(function($p) {
                    $this->line("  ID: {$p->id}, Name: {$p->item_name}, Alert: {$p->alert_quantity}");
                });
            return;
        }

        $this->info("✓ Product Found:");
        $this->line("  ID: {$product->id}");
        $this->line("  Name: {$product->item_name}");
        $this->line("  Alert Qty: " . ($product->alert_quantity ?? 'NULL'));

        // Check warehouse stocks
        $this->info("\nWarehouse Stocks:");
        $stocks = WarehouseStock::where('product_id', $product->id)->get();

        if ($stocks->isEmpty()) {
            $this->error("  ❌ No warehouse stocks found!");
        } else {
            $stocks->each(function($s) {
                $this->line("  Warehouse {$s->warehouse_id}: {$s->quantity} units");
            });
        }

        // Total stock
        $totalStock = $stocks->sum('quantity');
        $this->info("\nTotal Stock: {$totalStock}");

        // Check if alert should trigger
        if ($product->alert_quantity && $totalStock <= $product->alert_quantity) {
            $this->info("\n✓ ALERT CONDITION MET:");
            $this->line("  Stock ({$totalStock}) <= Alert Qty ({$product->alert_quantity})");
            $this->line("  → Notification SHOULD be created");
        } else {
            $this->info("\n❌ ALERT CONDITION NOT MET:");
            if (!$product->alert_quantity) {
                $this->line("  → alert_quantity is NULL or 0");
            } else {
                $this->line("  → Stock ({$totalStock}) > Alert Qty ({$product->alert_quantity})");
            }
        }

        // Check existing notifications
        $this->info("\nExisting Notifications:");
        $notifs = \App\Models\Notification::where('product_id', $product->id)
            ->where('type', 'product_stock_alert')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($notifs->isEmpty()) {
            $this->line("  No notifications found");
        } else {
            $notifs->each(function($n) {
                $this->line("  Status: {$n->status}, Created: {$n->created_at}");
            });
        }

        $this->info("\n=== END DEBUG ===\n");
    }
}
