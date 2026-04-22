<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class FixOutwardGatepassUnits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gatepass:fix-units';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix missing unit information in existing outward gatepass items';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Starting Outward Gatepass Unit Fix...');
        
        // Get all outward gatepasses with items
        $gatepasses = DB::table('outward_gatepasses')
            ->whereNotNull('items')
            ->get();

        if ($gatepasses->isEmpty()) {
            $this->info('✅ No gatepasses found to update.');
            return Command::SUCCESS;
        }

        $this->info("📦 Found {$gatepasses->count()} gatepass record(s) to check...");
        
        // Get product units in one query to optimize
        $productsWithUnits = [];
        $productIds = [];
        
        foreach ($gatepasses as $gp) {
            $items = is_string($gp->items) ? json_decode($gp->items, true) : $gp->items;
            if (!is_array($items)) {
                continue;
            }
            
            foreach ($items as $item) {
                if (!empty($item['product_id'])) {
                    $productIds[] = $item['product_id'];
                }
            }
        }

        if (!empty($productIds)) {
            // Use DB query to join products with units table
            $products = DB::table('products')
                ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                ->whereIn('products.id', array_unique($productIds))
                ->select('products.id', 'products.unit_id', 'units.name as unit_name')
                ->get();
            
            foreach ($products as $p) {
                $productsWithUnits[$p->id] = [
                    'unit_name' => $p->unit_name ?? '',
                    'unit_id' => $p->unit_id,
                ];
            }
        }

        $updated = 0;
        $this->output->progressStart($gatepasses->count());

        foreach ($gatepasses as $gp) {
            $items = is_string($gp->items) ? json_decode($gp->items, true) : $gp->items;
            if (!is_array($items)) {
                $this->output->progressAdvance();
                continue;
            }

            $needsUpdate = false;
            foreach ($items as &$item) {
                // Check if unit is missing
                if (empty($item['unit']) && !empty($item['product_id'])) {
                    if (isset($productsWithUnits[$item['product_id']])) {
                        $item['unit'] = $productsWithUnits[$item['product_id']]['unit_name'];
                        $needsUpdate = true;
                    }
                }
            }
            
            // If any items were updated, save the gatepass
            if ($needsUpdate) {
                DB::table('outward_gatepasses')
                    ->where('id', $gp->id)
                    ->update([
                        'items' => json_encode($items),
                        'updated_at' => now(),
                    ]);
                $updated++;
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $this->info("");
        $this->line("✅ <fg=green>Successfully updated {$updated} gatepass record(s)!</>");
        $this->info("🎉 All outward gatepass items now have unit information.");
        
        return Command::SUCCESS;
    }
}
