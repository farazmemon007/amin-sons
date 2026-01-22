<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Unit;
use App\Models\Brand;

class ProductBulkSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::firstOrCreate([
            'name' => 'Electronics'
        ]);

        $subCategory = Subcategory::firstOrCreate([
            'category_id' => $category->id,
            'name'        => 'General'
        ]);

        $unit = Unit::firstOrCreate([
            'name' => 'Piece'
        ]);

        $brand = Brand::firstOrCreate([
            'name' => 'GenericBrand'
        ]);

        $now = now();
        $products = [];
        for ($i = 1; $i <= 1000; $i++) {
            $products[] = [
                'creater_id'       => rand(1, 5),
                'category_id'      => $category->id,
                'sub_category_id'  => $subCategory->id,
                'brand_id'         => $brand->id,
                'is_part'          => rand(0, 1),
                'is_assembled'     => rand(0, 1),
                'item_code'        => 'ITEM-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'unit_id'          => $unit->id,
                'item_name'        => 'Product ' . $i,
                'color'            => json_encode(['White', 'Black', 'Red', 'Blue'][rand(0,3)]),
                'price'            => rand(1000, 200000),
                'alert_quantity'   => rand(1, 10),
                'created_at'       => $now,
                'updated_at'       => $now,
                'deleted_at'       => null,
                'barcode_path'     => rand(100000000000, 999999999999),
                'initial_stock'    => rand(1, 100),
                'wholesale_price'  => rand(800, 190000),
                'image'            => null,
                'model'            => 'Model-' . $i,
                'hs_code'          => 'HS' . rand(1000,9999),
                'pack_type'        => 'Box',
                'pack_qty'         => rand(1, 20),
                'piece_per_pack'   => rand(1, 50),
                'loose_piece'      => rand(0, 10),
            ];
        }
        Product::insert($products);
    }
}
