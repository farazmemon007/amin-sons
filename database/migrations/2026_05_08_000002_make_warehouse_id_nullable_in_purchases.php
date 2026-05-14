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
        Schema::table('purchases', function (Blueprint $table) {
            $table->unsignedBigInteger('warehouse_id')->nullable()->change();
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_items', 'warehouse_id')) {
                $table->unsignedBigInteger('warehouse_id')->nullable()->after('product_id');
            } else {
                $table->unsignedBigInteger('warehouse_id')->nullable()->change();
            }
        });
        
        Schema::table('vendor_remaining', function (Blueprint $table) {
            $table->unsignedBigInteger('warehouse_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ... (rollback if needed)
    }
};
