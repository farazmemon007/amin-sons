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
        // ✅ Create stock_hold table for audit trail
        Schema::create('stock_holds', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('warehouse_order_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            
            // Reference info
            $table->string('invoice_no')->nullable();
            $table->string('dc_no')->nullable();
            
            // Stock details
            $table->decimal('available_qty', 15, 2)->default(0); // Available in warehouse
            $table->decimal('deliver_qty', 15, 2)->default(0);   // Will be delivered
            $table->decimal('remaining_qty', 15, 2)->default(0); // Will remain after delivery
            
            // Product details (audit cache)
            $table->string('product_name')->nullable();
            $table->string('product_code')->nullable();
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->text('remarks')->nullable();
            
            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            // Indexes for fast querying
            $table->index('sale_id');
            $table->index('invoice_no');
            $table->index('dc_no');
            $table->index('product_id');
            $table->index('warehouse_id');
            $table->index('created_at');
            
            // Foreign keys
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('set null');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_holds');
    }
};
