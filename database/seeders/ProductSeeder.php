<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Products are seeded per-branch, with unique category/subcategory/brand per branch
        // so that testing can verify branch-level separation.

        $branches = Branch::pluck('id')->toArray();
        if (empty($branches)) {
            return;
        }

        // Product templates (different SKU sets per branch)
        $productTemplatesByBranch = [
            1 => [
                ['name' => 'Mobile', 'code' => 'MOB', 'price' => 1800, 'model' => 'Model-001', 'hs_code' => '8517.62'],
                ['name' => 'Laptop', 'code' => 'LAP', 'price' => 2200, 'model' => 'Model-002', 'hs_code' => '8471.30'],
                ['name' => 'Tablet', 'code' => 'TAB', 'price' => 1500, 'model' => 'Model-003', 'hs_code' => '8471.41'],
                ['name' => 'Headset', 'code' => 'HED', 'price' => 250, 'model' => 'Model-004', 'hs_code' => '8518.30'],
                ['name' => 'Charger', 'code' => 'CHR', 'price' => 200, 'model' => 'Model-005', 'hs_code' => '8504.40'],
            ],
            2 => [
                ['name' => 'Fan', 'code' => 'FAN', 'price' => 3200, 'model' => 'Model-101', 'hs_code' => '8414.51'],
                ['name' => 'Tube Light', 'code' => 'TBL', 'price' => 150, 'model' => 'Model-102', 'hs_code' => '8539.21'],
                ['name' => 'Switch', 'code' => 'SWT', 'price' => 40, 'model' => 'Model-103', 'hs_code' => '8536.50'],
                ['name' => 'Plug', 'code' => 'PLG', 'price' => 80, 'model' => 'Model-104', 'hs_code' => '8536.69'],
                ['name' => 'Extension', 'code' => 'EXT', 'price' => 250, 'model' => 'Model-105', 'hs_code' => '8536.10'],
            ],
            3 => [
                ['name' => 'Wrench', 'code' => 'WRN', 'price' => 120, 'model' => 'Model-201', 'hs_code' => '8204.11'],
                ['name' => 'Hammer', 'code' => 'HMR', 'price' => 160, 'model' => 'Model-202', 'hs_code' => '8205.40'],
                ['name' => 'Screwdriver', 'code' => 'SDR', 'price' => 80, 'model' => 'Model-203', 'hs_code' => '8205.51'],
                ['name' => 'Drill', 'code' => 'DRL', 'price' => 4500, 'model' => 'Model-204', 'hs_code' => '8467.21'],
                ['name' => 'Saw', 'code' => 'SAW', 'price' => 320, 'model' => 'Model-205', 'hs_code' => '8202.39'],
            ],
        ];

        // Map branch -> category/subcategory names (these should exist from CategorySeeder).
        $branchCategoryMap = [
            1 => ['category' => 'Electronics', 'subcategory' => 'Fan'],
            2 => ['category' => 'Tools', 'subcategory' => 'Hammer'],
            3 => ['category' => 'Hardware', 'subcategory' => 'Nails'],
        ];

        foreach ($branches as $branchId) {
            $mapping = $branchCategoryMap[$branchId] ?? null;

            $category = null;
            $subcategory = null;

            if ($mapping) {
                $category = Category::where('name', $mapping['category'])->first();
                if ($category) {
                    $subcategory = Subcategory::where('category_id', $category->id)
                        ->where('name', $mapping['subcategory'])
                        ->first();
                }
            }

            if (!$category) {
                $category = Category::first();
            }
            if (!$subcategory) {
                $subcategory = Subcategory::where('category_id', $category->id)->first();
            }

            // Use a brand/unit specific to branch for easier testing
            $brand = Brand::firstOrCreate(['name' => "Branch {$branchId} Brand"]);
            $unit = Unit::firstOrCreate(['name' => "Branch {$branchId} Unit"]);

            $productTemplates = $productTemplatesByBranch[$branchId] ?? $productTemplatesByBranch[1];

            foreach ($productTemplates as $idx => $template) {
                $itemCode = sprintf('%s%02d%03d', $template['code'], $branchId, $idx + 1);
                $itemName = $template['name'] . ' (Branch ' . $branchId . ')';

                $product = Product::updateOrCreate(
                    ['item_code' => $itemCode],
                    [
                        'branch_id' => $branchId,
                        'creater_id' => 1,
                        'category_id' => $category->id,
                        'sub_category_id' => $subcategory->id,
                        'brand_id' => $brand->id,
                        'unit_id' => $unit->id,
                        'item_name' => $itemName,
                        'item_code' => $itemCode,
                        'price' => $template['price'],
                        'alert_quantity' => 5,
                        'model' => $template['model'],
                        'hs_code' => $template['hs_code'],
                        'pack_type' => 'Box',
                        'pack_qty' => 1,
                        'piece_per_pack' => 1,
                        'loose_piece' => 0,
                    ]
                );

                // Ensure there is a stock record for the product in this branch
                \App\Models\Stock::updateOrCreate(
                    ['branch_id' => $branchId, 'product_id' => $product->id],
                    ['qty' => 10 + ($idx * 10), 'reserved_qty' => 0]
                );
            }
        }
    }
}
