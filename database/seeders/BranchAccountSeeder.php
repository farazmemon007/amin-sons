<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\BranchAccount;
use Illuminate\Database\Seeder;

class BranchAccountSeeder extends Seeder
{
    public function run(): void
    {
        // Create branch accounts for all existing branches
        $branches = Branch::all();

        foreach ($branches as $branch) {
            BranchAccount::firstOrCreate(
                ['branch_id' => $branch->id],
                ['current_balance' => 0]
            );
        }
    }
}
