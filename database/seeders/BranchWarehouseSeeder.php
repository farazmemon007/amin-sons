<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\Warehouse;

class BranchWarehouseSeeder extends Seeder
{
    public function run()
    {
        $branches = Branch::all();
        $warehouses = Warehouse::all();

        if ($branches->isEmpty() || $warehouses->isEmpty()) {
            return;
        }

        // Prefer a branch named 'Main' or fallback to first branch
        $main = Branch::where('name', 'like', '%Main%')->first() ?? $branches->first();

        foreach ($warehouses as $w) {
            $attach = [];

            // If warehouse name contains a branch name, attach to that branch
            foreach ($branches as $b) {
                if (!empty($b->branch_name) && stripos($w->warehouse_name ?? '', $b->branch_name) !== false) {
                    $attach[] = $b->id;
                }
            }

            // default: attach to main branch
            if (empty($attach)) {
                $attach[] = $main->id;
            }

            $w->branches()->syncWithoutDetaching($attach);
        }
    }
}
