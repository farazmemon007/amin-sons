<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
          

            [

                'id' => 1,
                'name' => 'amin$sons',
                'address' => 'Main Market, Lahore',
                'status' => 'active',
                'number' => '03001234567',
            ],
            [
                'id' => 2,
                'name' => 'waqas electronics',
                'address' => 'Saddar Bazaar, Rawalpindi',
                'status' => 'active',
                'number' => '03002345678',
            ],
            [
                'id' => 3,
                'name' => 'karachi electronics',
                'address' => 'Techno City, Karachi',
                'status' => 'active',
                'number' => '03003456789',
            ],

        ];

        foreach ($branches as $branch) {

            Branch::updateOrCreate(
                ['id' => $branch['id']],
                $branch
            );

        }

        $this->command->info('✅ 3 Branches seeded successfully!');
    }
}