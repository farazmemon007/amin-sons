<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AssignDefaultUnits extends Command
{
    protected $signature = 'products:assign-units';
    protected $description = 'Assign default unit (Piece) to products without units';

    public function handle()
    {
        $this->info('🔧 Assigning default units to products without units...');
        
        // Get the Piece unit ID (usually ID 1)
        $pieceUnit = DB::table('units')->where('name', 'Piece')->first();
        
        if (!$pieceUnit) {
            $this->error('❌ "Piece" unit not found in database!');
            return Command::FAILURE;
        }

        // Find products without units
        $productsWithoutUnits = DB::table('products')
            ->whereNull('unit_id')
            ->select('id', 'item_name')
            ->get();

        if ($productsWithoutUnits->isEmpty()) {
            $this->info('✅ All products already have units assigned!');
            return Command::SUCCESS;
        }

        $this->line("Found " . $productsWithoutUnits->count() . " product(s) without units:");
        
        foreach ($productsWithoutUnits as $product) {
            $this->line("  - " . $product->item_name . " (ID: " . $product->id . ")");
        }

        // Ask for confirmation
        if (!$this->confirm('Assign "Piece" unit to these products?')) {
            return Command::SUCCESS;
        }

        // Update products
        $updated = DB::table('products')
            ->whereNull('unit_id')
            ->update(['unit_id' => $pieceUnit->id, 'updated_at' => now()]);

        $this->info("✅ Successfully assigned unit to $updated product(s)!");
        
        // Show updated products
        $this->line("\n📋 Updated Products:");
        $updatedProducts = DB::table('products')
            ->leftJoin('units', 'products.unit_id', '=', 'units.id')
            ->whereIn('products.id', $productsWithoutUnits->pluck('id'))
            ->select('products.id', 'products.item_name', 'units.name as unit_name')
            ->get();

        foreach ($updatedProducts as $p) {
            $this->line(sprintf("  ✓ %s → %s", $p->item_name, $p->unit_name));
        }

        return Command::SUCCESS;
    }
}
