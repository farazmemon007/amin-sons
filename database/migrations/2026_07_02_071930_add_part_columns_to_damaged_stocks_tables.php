<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. First add a separate index on branch_id so MySQL can use it for branch_id foreign key constraint
        try {
            Schema::table('damaged_stocks', function (Blueprint $table) {
                $table->index('branch_id', 'damaged_stocks_branch_id_idx');
            });
        } catch (\Exception $e) {}

        // 2. Now we can safely drop unique triplet index, add columns and create new unique index
        Schema::table('damaged_stocks', function (Blueprint $table) {
            try {
                $table->dropUnique('damaged_stocks_unique_triplet');
            } catch (\Exception $e) {}
            
            // Add columns conditionally
            if (!Schema::hasColumn('damaged_stocks', 'is_part')) {
                $table->boolean('is_part')->default(false)->after('product_id');
            }
            if (!Schema::hasColumn('damaged_stocks', 'part_name')) {
                $table->string('part_name')->nullable()->after('is_part');
            }

            // New unique index handling parts
            try {
                $table->unique(['branch_id', 'warehouse_id', 'product_id', 'is_part', 'part_name'], 'damaged_stocks_part_unique');
            } catch (\Exception $e) {}
        });

        // 3. Modify damaged_stock_transfers table
        Schema::table('damaged_stock_transfers', function (Blueprint $table) {
            if (!Schema::hasColumn('damaged_stock_transfers', 'is_part')) {
                $table->boolean('is_part')->default(false)->after('product_id');
            }
            if (!Schema::hasColumn('damaged_stock_transfers', 'part_name')) {
                $table->string('part_name')->nullable()->after('is_part');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('damaged_stock_transfers', function (Blueprint $table) {
            if (Schema::hasColumn('damaged_stock_transfers', 'is_part')) {
                $table->dropColumn(['is_part', 'part_name']);
            }
        });

        Schema::table('damaged_stocks', function (Blueprint $table) {
            try {
                $table->dropUnique('damaged_stocks_part_unique');
            } catch (\Exception $e) {}

            if (Schema::hasColumn('damaged_stocks', 'is_part')) {
                $table->dropColumn(['is_part', 'part_name']);
            }

            try {
                $table->unique(['branch_id', 'warehouse_id', 'product_id'], 'damaged_stocks_unique_triplet');
            } catch (\Exception $e) {}

            try {
                $table->dropIndex('damaged_stocks_branch_id_idx');
            } catch (\Exception $e) {}
        });
    }
};
