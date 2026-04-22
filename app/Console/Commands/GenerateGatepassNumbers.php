<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateGatepassNumbers extends Command
{
    protected $signature = 'gatepass:generate-numbers';
    protected $description = 'Generate formatted gatepass numbers (GP-BRANCH-0001, GP-BRANCH-0002) for existing records';

    public function handle()
    {
        $this->info('🔢 Generating branch-based formatted gatepass numbers...');
        
        // Get all gatepasses without numbers, group by branch
        $gatepasses = DB::table('outward_gatepasses')
            ->leftJoin('warehouse_orders', 'outward_gatepasses.order_id', '=', 'warehouse_orders.id')
            ->leftJoin('warehouse_stocks', 'warehouse_orders.warehouse_id', '=', 'warehouse_stocks.warehouse_id')
            ->whereNull('outward_gatepasses.gatepass_number')
            ->select('outward_gatepasses.id', 'outward_gatepasses.branch_id', 'warehouse_stocks.branch_id as warehouse_branch_id', 'warehouse_orders.warehouse_id')
            ->orderBy('outward_gatepasses.id')
            ->distinct()
            ->get();

        if ($gatepasses->isEmpty()) {
            $this->info('✅ All gatepasses already have formatted numbers.');
            return Command::SUCCESS;
        }

        $this->line("Found " . count($gatepasses) . " gatepass record(s) to process...");
        $this->output->progressStart(count($gatepasses));

        foreach ($gatepasses as $gp) {
            // Determine branch_id
            $branchId = $gp->branch_id ?? $gp->warehouse_branch_id ?? 1; // Default to 1 if not found
            
            // Get the next sequence number for this branch
            $sequenceNum = DB::table('outward_gatepasses')
                ->where('branch_id', $branchId)
                ->whereNotNull('gatepass_number')
                ->count() + 1;

            // Generate format: GP-BBB-SSSS (Branch 001 - Sequence 0001)
            $gatepassNumber = sprintf(
                'GP-%s-%s',
                str_pad($branchId, 3, '0', STR_PAD_LEFT),
                str_pad($sequenceNum, 4, '0', STR_PAD_LEFT)
            );
            
            DB::table('outward_gatepasses')
                ->where('id', $gp->id)
                ->update([
                    'gatepass_number' => $gatepassNumber,
                    'branch_id' => $branchId,
                    'updated_at' => now(),
                ]);

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $this->info("");
        $this->line("✅ <fg=green>Generated branch-based gatepass numbers!</>");
        
        // Show sample by branch
        $this->line("\n📋 Gatepass Numbers by Branch:");
        $branches = DB::table('outward_gatepasses')
            ->whereNotNull('gatepass_number')
            ->select('branch_id')
            ->distinct()
            ->orderBy('branch_id')
            ->get();

        foreach ($branches as $b) {
            $branchName = DB::table('branches')->where('id', $b->branch_id)->value('name') ?? 'Unknown';
            $count = DB::table('outward_gatepasses')
                ->where('branch_id', $b->branch_id)
                ->count();
            
            $samples = DB::table('outward_gatepasses')
                ->where('branch_id', $b->branch_id)
                ->orderBy('id')
                ->limit(3)
                ->get(['id', 'gatepass_number', 'customer_name']);

            $this->line("\n   Branch: <fg=blue>$branchName</> (ID: {$b->branch_id}) - Total: $count gatepasses");
            foreach ($samples as $s) {
                $this->line(sprintf("      → %s (%s)", $s->gatepass_number, $s->customer_name ?? 'N/A'));
            }
        }

        return Command::SUCCESS;
    }
}
