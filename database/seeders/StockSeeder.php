<?php

namespace Database\Seeders;

use App\Models\Stock;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Adds stocks table entries for products:
     * Product 1 (mobile): 30 in Main Store (branch 1, warehouse 1), 20 in Branch A (branch 2, warehouse 2)
     * Product 2 (laptop): 15 in Main Store (branch 1, warehouse 1), 10 in Branch B (branch 3, warehouse 3)
     */
    public function run(): void
    {
        // Product 1 (mobile) - Branch 1
        Stock::create([
            'branch_id' => 1,
            'product_id' => 1,
            'qty' => 50,
            'reserved_qty' => 0,
        ]);

       

        // Product 2 (laptop) - Branch 1
        Stock::create([
            'branch_id' => 1,
            'product_id' => 2,
            'qty' => 25,
            'reserved_qty' => 0,
        ]);

        
    }
}
