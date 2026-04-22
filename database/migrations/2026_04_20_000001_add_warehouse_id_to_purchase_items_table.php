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
        Schema::table('purchase_items', function (Blueprint $table) {
            // ✅ ERP STANDARD: Per-line warehouse assignment
            // Each product can go to different warehouse (overrides header warehouse)
            // NULL = use purchase.warehouse_id (backward compatible)
            $table->foreignId('warehouse_id')
                ->nullable()
                ->after('product_id')
                ->constrained('warehouses')
                ->onDelete('set null');
            
            $table->index(['warehouse_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropForeignKey(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });
    }
};
