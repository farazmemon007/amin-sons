<?php

namespace Database\Seeders;

use App\Models\WarehouseStock;
use Illuminate\Database\Seeder;

class WarehouseStockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Adds warehouse stocks for products:
     * Product 1 (mobile): 30 in Main Store, 20 in Branch A
     * Product 2 (laptop): 15 in Main Store, 10 in Branch B
     */
    public function run(): void
    {
        // Product 1 (mobile) - warehouse 1 (Main Store)
        WarehouseStock::create([
            'warehouse_id' => 1,
            'product_id' => 1,
            'quantity' => 30,
        ]);

        // Product 1 (mobile) - warehouse 2 (Branch A)
        WarehouseStock::create([
            'warehouse_id' => 2,
            'product_id' => 1,
            'quantity' => 20,
        ]);

        // Product 2 (laptop) - warehouse 1 (Main Store)
        WarehouseStock::create([
            'warehouse_id' => 1,
            'product_id' => 2,
            'quantity' => 15,
        ]);

        // Product 2 (laptop) - warehouse 3 (Branch B)
        WarehouseStock::create([
            'warehouse_id' => 3,
            'product_id' => 2,
            'quantity' => 10,
        ]);
    }
}
