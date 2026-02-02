<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add product_id and warehouse_id to notifications table for stock alerts
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Add product and warehouse relationship fields
            $table->unsignedBigInteger('product_id')->nullable()->after('customer_id');
            $table->unsignedBigInteger('warehouse_id')->nullable()->after('product_id');
            
            // Add foreign keys
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('set null');
            
            // Add index for faster queries
            $table->index('product_id');
            $table->index('warehouse_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeignKey(['product_id']);
            $table->dropForeignKey(['warehouse_id']);
            $table->dropIndex(['product_id']);
            $table->dropIndex(['warehouse_id']);
            $table->dropColumn(['product_id', 'warehouse_id']);
        });
    }
};
