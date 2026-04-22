<?php

namespace Database\Seeders;

use App\Models\StockMovement;
use Illuminate\Database\Seeder;

class StockMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Adds sample stock movements for testing
     */
    public function run(): void
    {
        // Product 1 (mobile) - Stock IN (Initial stock added)
        StockMovement::create([
            'product_id' => 1,
            'type' => 'in',
            'qty' => 30,
            'ref_type' => 'INITIAL_STOCK',
            'ref_id' => 1,
            'ref_uuid' => null,
            'is_auto_pluck' => 0,
            'note' => 'Initial stock added for mobile in Main Store',
        ]);

        // Product 1 (mobile) - Stock IN (Branch A stock)
        StockMovement::create([
            'product_id' => 1,
            'type' => 'in',
            'qty' => 20,
            'ref_type' => 'BRANCH_TRANSFER',
            'ref_id' => 1,
            'ref_uuid' => null,
            'is_auto_pluck' => 0,
            'note' => 'Stock transferred to Branch A',
        ]);

        // Product 2 (laptop) - Stock IN (Initial stock added)
        StockMovement::create([
            'product_id' => 2,
            'type' => 'in',
            'qty' => 15,
            'ref_type' => 'INITIAL_STOCK',
            'ref_id' => 2,
            'ref_uuid' => null,
            'is_auto_pluck' => 0,
            'note' => 'Initial stock added for laptop in Main Store',
        ]);

        // Product 2 (laptop) - Stock IN (Branch B stock)
        StockMovement::create([
            'product_id' => 2,
            'type' => 'in',
            'qty' => 10,
            'ref_type' => 'BRANCH_TRANSFER',
            'ref_id' => 2,
            'ref_uuid' => null,
            'is_auto_pluck' => 0,
            'note' => 'Stock transferred to Branch B',
        ]);
    }
}
