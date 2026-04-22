<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Warehouse;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouses = [
            ['warehouse_name' => 'Main Store', 'creater_id' => 1, 'location' => 'Karachi', 'remarks' => 'Main stock storage'],
            ['warehouse_name' => 'warehouse 1 ', 'creater_id' => 1, 'location' => 'Lahore', 'remarks' => 'North region store'],
            ['warehouse_name' => 'warehouse 2', 'creater_id' => 1, 'location' => 'Islamabad', 'remarks' => 'Capital branch'],
        ];

        foreach ($warehouses as $data) {
            Warehouse::firstOrCreate(['warehouse_name' => $data['warehouse_name']], $data);
        }
    }
}
