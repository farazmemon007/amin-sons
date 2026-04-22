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
        Schema::table('stock_request_items', function (Blueprint $table) {
            $table->unsignedBigInteger('to_warehouse_id')->nullable()->after('from_warehouse_id')->comment('Destination warehouse ID');
            $table->decimal('delivery_charges', 12, 2)->nullable()->after('unit_price')->comment('Delivery charges for this item');
            
            // Add foreign key
            $table->foreign('to_warehouse_id')->references('id')->on('warehouses')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_request_items', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['to_warehouse_id']);
            $table->dropColumn(['to_warehouse_id', 'delivery_charges']);
        });
    }
};
