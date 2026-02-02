<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\StockAlertService;
use Illuminate\Console\Command;

class CheckStockAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stocks:check-alerts {--product_id= : Check specific product}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Check all products and create stock alert notifications if below alert quantity';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking stock alerts...');

        if ($this->option('product_id')) {
            // Check specific product
            $productId = $this->option('product_id');
            StockAlertService::checkAndCreateAlert($productId);
            $this->info("✓ Checked product ID: {$productId}");
        } else {
            // Check all products with alert quantity set
            $products = Product::where('alert_quantity', '>', 0)
                ->where('alert_quantity', '!=', null)
                ->get();

            $this->info("Found {$products->count()} products with alert quantities");

            foreach ($products as $product) {
                StockAlertService::checkAndCreateAlert($product->id);
                $this->line("✓ {$product->item_name}");
            }
        }

        $this->info("\n✅ Stock alert check completed!");
    }
}
