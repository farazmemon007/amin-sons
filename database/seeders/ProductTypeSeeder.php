<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productTypes = [
            ['id' => 1, 'name' => 'copper'],
            ['id' => 2, 'name' => 'silver'],
            // ['id' => 3, 'name' => '', 'status' => 'active'],
        ];

        foreach ($productTypes as $type) {
            \App\Models\ProductType::updateOrCreate(
                ['id' => $type['id']],
                $type
            );
        }

        $this->command->info('✅ Product types seeded successfully!');
    }
}
