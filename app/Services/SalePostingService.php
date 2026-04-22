<?php

namespace App\Services;

use App\Models\SalePosting;
use App\Models\Sale;
use App\Models\Stock;
use App\Models\WarehouseStock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * Sale Posting Service
 * ═══════════════════════════════════════════════════════════════════════════
 * 
 * This service handles the processing of draft sale postings:
 * - Fetch pending postings for a sale
 * - Process stock deduction based on source_type
 * - Mark postings as processed
 * - Create stock movements for audit trail
 */
class SalePostingService
{
    /**
     * Process pending sale postings (draft → actual stock deduction)
     * Called when Gate Pass is generated or manually triggered
     */
    public static function processForSale($saleId)
    {
        try {
            return DB::transaction(function () use ($saleId) {
                $sale = Sale::lockForUpdate()->find($saleId);
                if (!$sale) {
                    throw new \Exception('Sale not found');
                }

                // Fetch all pending postings for this sale
                $postings = SalePosting::where('sale_id', $saleId)
                    ->where('status', 'pending')
                    ->lockForUpdate()
                    ->get();

                if ($postings->isEmpty()) {
                    Log::warning('No pending postings found for sale', ['sale_id' => $saleId]);
                    return [
                        'ok' => false,
                        'message' => 'No pending postings to process'
                    ];
                }

                $processedCount = 0;
                $failedItems = [];

                foreach ($postings as $posting) {
                    try {
                        self::processPosting($posting, $sale);
                        $processedCount++;
                    } catch (\Exception $e) {
                        Log::error('Failed to process posting', [
                            'posting_id' => $posting->id,
                            'error' => $e->getMessage()
                        ]);
                        $failedItems[] = $posting->product_id;
                    }
                }

                if (!empty($failedItems)) {
                    throw new \Exception('Failed to process ' . count($failedItems) . ' items');
                }

                Log::info('All postings processed', [
                    'sale_id' => $saleId,
                    'processed' => $processedCount
                ]);

                return [
                    'ok' => true,
                    'processed' => $processedCount,
                    'message' => "Successfully processed {$processedCount} items"
                ];
            });
        } catch (\Exception $e) {
            Log::error('Sale posting processing failed', [
                'sale_id' => $saleId,
                'error' => $e->getMessage()
            ]);
            return [
                'ok' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process a single posting: deduct stock based on source type
     */
    private static function processPosting($posting, $sale)
    {
        $product = $posting->product;
        if (!$product) {
            throw new \Exception("Product #{$posting->product_id} not found");
        }

        $qty = $posting->qty;
        $sourceType = $posting->source_type;
        $sourceId = $posting->source_id;
        $branchId = $sale->branch_id;

        if ($sourceType === 'warehouse') {
            // Deduct from warehouse_stocks
            $warehouseStock = WarehouseStock::lockForUpdate()
                ->where('product_id', $posting->product_id)
                ->where('branch_id', $branchId)
                ->where('warehouse_id', $sourceId)
                ->first();

            if (!$warehouseStock) {
                throw new \Exception("Warehouse stock not found for product {$product->item_name}");
            }

            $before = $warehouseStock->quantity ?? 0;
            $warehouseStock->quantity = max(0, $before - $qty);
            $warehouseStock->save();

            Log::info('Deducted from warehouse_stocks', [
                'product_id' => $posting->product_id,
                'warehouse_id' => $sourceId,
                'qty_before' => $before,
                'qty_after' => $warehouseStock->quantity
            ]);
        } else {
            // source_type = 'branch' → deduct from branch stock
            $branchStock = WarehouseStock::lockForUpdate()
                ->where('product_id', $posting->product_id)
                ->where('branch_id', $branchId)
                ->whereNull('warehouse_id')
                ->first();

            if (!$branchStock) {
                throw new \Exception("Branch stock not found for product {$product->item_name}");
            }

            $before = $branchStock->quantity ?? 0;
            $branchStock->quantity = max(0, $before - $qty);
            $branchStock->save();

            Log::info('Deducted from branch stock', [
                'product_id' => $posting->product_id,
                'branch_id' => $branchId,
                'qty_before' => $before,
                'qty_after' => $branchStock->quantity
            ]);
        }

        // Also deduct from main Stock table (branch-level overall)
        $mainStock = Stock::lockForUpdate()
            ->where('product_id', $posting->product_id)
            ->where('branch_id', $branchId)
            ->first();

        if ($mainStock) {
            $before = $mainStock->qty ?? 0;
            $mainStock->qty = max(0, $before - $qty);
            $mainStock->save();

            Log::info('Deducted from main stocks', [
                'product_id' => $posting->product_id,
                'qty_before' => $before,
                'qty_after' => $mainStock->qty
            ]);
        }

        // Create stock movement for audit
        StockMovement::create([
            'product_id' => $posting->product_id,
            'type' => 'out',
            'qty' => $qty,
            'ref_type' => 'SALE_POSTING',
            'ref_id' => $posting->id,
            'ref_uuid' => $posting->sale->invoice_no,
            'is_auto_pluck' => 0,
            'note' => 'Gate Pass / Draft Sale - ' . $posting->sale->invoice_no
        ]);

        // Mark posting as processed
        $posting->status = 'processed';
        $posting->save();

        Log::info('Posting marked as processed', [
            'posting_id' => $posting->id,
            'product_id' => $posting->product_id,
            'qty' => $qty
        ]);
    }

    /**
     * Fetch pending postings for a sale
     */
    public static function getPendingPostings($saleId)
    {
        return SalePosting::where('sale_id', $saleId)
            ->where('status', 'pending')
            ->with(['product', 'sale'])
            ->get();
    }

    /**
     * Get postings summary for dashboard
     */
    public static function getPendingPostingsSummary()
    {
        return SalePosting::where('status', 'pending')
            ->with(['sale', 'product'])
            ->groupBy('sale_id')
            ->selectRaw('sale_id, COUNT(*) as item_count, SUM(qty) as total_qty')
            ->get();
    }
}
