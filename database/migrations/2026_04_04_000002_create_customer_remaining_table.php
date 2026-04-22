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
        Schema::create('customer_remaining', function (Blueprint $table) {
            $table->id();
            
            // Link to sale
            $table->unsignedBigInteger('sale_id')->index();
            $table->foreign('sale_id')->references('id')->on('sales')->cascadeOnDelete();
            
            // Customer info
            $table->unsignedBigInteger('customer_id')->index();
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            
            // Product info
            $table->unsignedBigInteger('product_id')->index();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            
            // Warehouse where it's stored
            $table->unsignedBigInteger('warehouse_id')->nullable()->index();
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->cascadeOnDelete();
            
            // Remaining quantity to be delivered
            $table->decimal('remaining_qty', 12, 4)->default(0);
            
            // Tracking info
            $table->string('unit', 50)->nullable();
            $table->string('item_code', 100)->nullable();
            $table->string('product_name')->nullable();
            
            // Status tracking
            $table->enum('status', ['pending', 'partial', 'completed'])->default('pending');
            $table->text('notes')->nullable();
            
            // Links to gate passes created from this
            $table->unsignedBigInteger('last_gatepass_id')->nullable()->index();
            
            $table->timestamps();
            
            // Combined index for faster queries
            $table->index(['sale_id', 'customer_id', 'product_id']);
            $table->index(['customer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_remaining');
    }
};
