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
        Schema::table('warehouse_orders', function (Blueprint $table) {
            // ✅ Make warehouse_id nullable for branch deliveries
            $table->unsignedBigInteger('warehouse_id')->nullable()->change();
            
            // ✅ Add delivery location type
            if (!Schema::hasColumn('warehouse_orders', 'delivery_location_type')) {
                $table->enum('delivery_location_type', ['warehouse', 'branch'])->nullable()->after('warehouse_id')->comment('Type of delivery location');
            }
            
            // ✅ Add branch_id for branch deliveries
            if (!Schema::hasColumn('warehouse_orders', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('delivery_location_type');
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_orders', function (Blueprint $table) {
            if (Schema::hasColumn('warehouse_orders', 'deletion_location_type')) {
                $table->dropColumn(['delivery_location_type']);
            }
            if (Schema::hasColumn('warehouse_orders', 'branch_id')) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn(['branch_id']);
            }
            // Revert warehouse_id back to non-nullable
            $table->unsignedBigInteger('warehouse_id')->nullable(false)->change();
        });
    }
};
