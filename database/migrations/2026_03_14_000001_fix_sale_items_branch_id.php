<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ===== CRITICAL DATA FIX =====
        // Sync sale_items.branch_id with parent sale.branch_id
        // This ensures referential integrity across branches
        
        DB::statement('
            UPDATE sale_items 
            SET branch_id = (
                SELECT branch_id FROM sales 
                WHERE sales.id = sale_items.sale_id
            )
            WHERE sale_id IS NOT NULL
        ');

        \Log::info('Fixed sale_items branch_id to match parent sales', [
            'timestamp' => now(),
            'description' => 'All sale_items now correctly reference their parent sale branch'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed - data was already malformed
        // This migration only fixes existing data
    }
};
