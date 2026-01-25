<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Stock;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;

class CheckAndSyncStocks extends Command
{
    protected $signature = 'stocks:check {--fix : Fix mismatches by setting stocks.qty = sum of warehouse_stocks}';
    protected $description = 'Check and optionally sync Stock.qty with sum of WarehouseStock.quantity per product';

    public function handle()
    {
        $this->info('Scanning stocks vs warehouse_stocks...');

        $mismatches = [];

        $stocks = Stock::all();

        foreach ($stocks as $stock) {
            $productId = $stock->product_id;

            $sum = WarehouseStock::where('product_id', $productId)->sum('quantity');

            $stockQty = (float) $stock->qty;
            $sumQty = (float) $sum;

            if (abs($stockQty - $sumQty) > 0.0001) {
                $mismatches[] = [
                    'stock_id' => $stock->id,
                    'product_id' => $productId,
                    'stock_qty' => $stockQty,
                    'warehouse_total' => $sumQty,
                ];
            }
        }

        if (empty($mismatches)) {
            $this->info('No mismatches found.');
            return 0;
        }

        $this->table(
            ['stock_id', 'product_id', 'stock_qty', 'warehouse_total'],
            $mismatches
        );

        if ($this->option('fix')) {
            $this->confirmFix($mismatches);
        } else {
            $this->info("Run with `php artisan stocks:check --fix` to sync Stock.qty to warehouse totals.");
        }

        return 0;
    }

    protected function confirmFix(array $mismatches)
    {
        if (! $this->confirm('Apply fixes: set stocks.qty = warehouse_total for listed products?')) {
            $this->info('Aborted. No changes made.');
            return;
        }

        DB::transaction(function () use ($mismatches) {
            foreach ($mismatches as $row) {
                $s = Stock::find($row['stock_id']);
                if (! $s) continue;
                $s->qty = $row['warehouse_total'];
                $s->save();
            }
        });

        $this->info('Updated stock quantities to match warehouse totals.');
    }
}
