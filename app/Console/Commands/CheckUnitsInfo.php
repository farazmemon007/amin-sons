<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckUnitsInfo extends Command
{
    protected $signature = 'units:check';
    protected $description = 'Check products and their unit information';

    public function handle()
    {
        $this->line("📦 All Products with Unit Information:");
        $this->line(str_repeat("=", 90));
        
        $products = DB::table('products')
            ->leftJoin('units', 'products.unit_id', '=', 'units.id')
            ->select('products.id', 'products.item_name', 'products.unit_id', 'units.name as unit_name')
            ->orderBy('products.id')
            ->get();

        foreach ($products as $p) {
            $unitDisplay = $p->unit_name ? $p->unit_name : ($p->unit_id ? "❌ ID {$p->unit_id} NOT FOUND" : "⚠️ NO UNIT");
            $this->line(sprintf(
                "ID: %d | %-25s | Unit ID: %-4s | %s",
                $p->id,
                substr($p->item_name, 0, 25),
                $p->unit_id ?? 'NULL',
                $unitDisplay
            ));
        }

        $this->line("\n");
        $this->line("📋 Available Units in Database:");
        $this->line(str_repeat("=", 90));
        
        $units = DB::table('units')->orderBy('id')->get();
        if ($units->isEmpty()) {
            $this->warn("⚠️ No units found in database!");
        } else {
            foreach ($units as $u) {
                $this->line(sprintf("ID: %d | %s", $u->id, $u->name));
            }
        }

        $this->line("\n");
        $this->line("🔍 Gatepass Item Units Status:");
        $this->line(str_repeat("=", 90));
        
        $gatepasses = DB::table('outward_gatepasses')
            ->whereNotNull('items')
            ->get();

        foreach ($gatepasses as $gp) {
            $items = json_decode($gp->items, true);
            $this->line("Gatepass #" . $gp->id . ":");
            
            foreach ($items as $item) {
                $unitDisplay = !empty($item['unit']) ? ("✅ " . $item['unit']) : "❌ MISSING";
                $this->line(sprintf(
                    "  - %s (ID:%s) | Unit: %s",
                    substr($item['product_name'], 0, 25),
                    $item['product_id'] ?? 'unknown',
                    $unitDisplay
                ));
            }
        }
    }
}
