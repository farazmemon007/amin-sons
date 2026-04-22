<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Services\BranchProductCodeService;

class InitializeBranchProductCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:init-branch-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '✅ Initialize branch-specific item codes for all products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Initializing branch-specific item codes for all products...');

        $products = Product::all();

        if ($products->isEmpty()) {
            $this->warn('⚠️  No products found!');
            return 0;
        }

        $count = 0;
        foreach ($products as $product) {
            try {
                BranchProductCodeService::initializeProductForAllBranches($product);
                $count++;
                $this->line("✓ Product ID {$product->id} ({$product->item_name}) - Codes generated");
            } catch (\Exception $e) {
                $this->error("✗ Error for product {$product->id}: {$e->getMessage()}");
            }
        }

        $this->info("✅ Successfully initialized {$count} products!");
        return 0;
    }
}
