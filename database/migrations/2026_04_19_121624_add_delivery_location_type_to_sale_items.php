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
        Schema::table('sale_items', function (Blueprint $table) {
            // ✅ ADD: Column to distinguish between warehouse and branch deliveries
            // branch_id already exists in sale_items table
            if (!Schema::hasColumn('sale_items', 'delivery_location_type')) {
                $table->enum('delivery_location_type', ['warehouse', 'branch'])->nullable()->after('warehouse_id')->comment('warehouse = warehouse delivery, branch = branch/shop delivery');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            if (Schema::hasColumn('sale_items', 'delivery_location_type')) {
                $table->dropColumn(['delivery_location_type']);
            }
        });
    }
};
