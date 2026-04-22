<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Branch;
use App\Models\WarehouseStock;
use App\Models\Category;
use App\Models\Unit;
use App\Services\BranchProductCodeService;

class ProductSeederWithBranchCodes extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create test data
        $category = Category::firstOrCreate(['name' => 'Electronics']);
        $unit = Unit::firstOrCreate(['name' => 'Piece']);
        $branches = Branch::all();

        // ✅ Create sample products
        $productsData = [
            ['item_name' => 'Motor', 'price' => 1500, 'alert_quantity' => 10],
            ['item_name' => 'Hammer', 'price' => 500, 'alert_quantity' => 20],
            ['item_name' => 'Wire', 'price' => 50, 'alert_quantity' => 100],
            ['item_name' => 'Battery', 'price' => 800, 'alert_quantity' => 15],
            ['item_name' => 'Bulb', 'price' => 100, 'alert_quantity' => 50],
        ];

        $createdProducts = [];

        foreach ($productsData as $data) {
            $product = Product::create([
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'item_name' => $data['item_name'],
                'model' => 'Model-' . $data['item_name'],
                'price' => $data['price'],
                'alert_quantity' => $data['alert_quantity'],
                'item_code' => 'GLOBAL-' . substr(md5($data['item_name']), 0, 6),
                'hs_code' => '00' . rand(1000, 9999),
                'pack_type' => 'Piece',
                'pack_qty' => 1,
                'piece_per_pack' => 1,
                'loose_piece' => 0,
                'is_part' => 0,
                'is_assembled' => 0,
                'completion_status' => 'complete',
            ]);

            $createdProducts[] = $product;

            // ✅ Initialize branch-specific codes for each product
            BranchProductCodeService::initializeProductForAllBranches($product);

            $this->command->info("✓ Created product: {$product->item_name}");
        }

        // ✅ Add warehouse stock for PRIMARY products (Branch 1 & 2)
        if ($branches->count() > 0) {
            $branch1 = $branches->first();
            $branch2 = $branches->get(1) ?? $branches->first();

            // Branch 1: All products are PRIMARY (have stock)
            foreach ($createdProducts as $product) {
                WarehouseStock::create([
                    'product_id' => $product->id,
                    'branch_id' => $branch1->id,
                    'warehouse_id' => 1, // Main warehouse
                    'quantity' => rand(50, 200),
                    'price' => $product->price,
                ]);

                // Update to PRIMARY status for this branch
                BranchProductCodeService::updatePrimaryStatus($product, $branch1->id);
            }

            $this->command->info("✓ Branch 1 ({$branch1->name}): All products as PRIMARY");

            // Branch 2: Only first 3 products are PRIMARY
            for ($i = 0; $i < min(3, count($createdProducts)); $i++) {
                WarehouseStock::create([
                    'product_id' => $createdProducts[$i]->id,
                    'branch_id' => $branch2->id,
                    'warehouse_id' => 1,
                    'quantity' => rand(30, 100),
                    'price' => $createdProducts[$i]->price,
                ]);

                // Update to PRIMARY status
                BranchProductCodeService::updatePrimaryStatus($createdProducts[$i], $branch2->id);
            }

            // Rest are SECONDARY for Branch 2
            for ($i = 3; $i < count($createdProducts); $i++) {
                BranchProductCodeService::updatePrimaryStatus($createdProducts[$i], $branch2->id);
            }

            $this->command->info("✓ Branch 2 ({$branch2->name}): First 3 products as PRIMARY, rest as SECONDARY");
        }

        $this->command->info("✅ Product seeding complete with branch-specific codes!");
    }
}
