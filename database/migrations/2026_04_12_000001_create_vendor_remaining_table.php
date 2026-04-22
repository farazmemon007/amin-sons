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
        Schema::create('vendor_remaining', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys (similar to customer_remaining)
            $table->foreignId('purchase_id')->constrained('purchases')->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            
            // Tracking fields
            $table->integer('ordered_qty')->default(0);      // Original qty from purchase_item
            $table->integer('received_qty')->default(0);     // Total qty received so far
            $table->integer('remaining_qty')->default(0);    // Still pending = ordered - received
            
            // Status tracking (pending → partial → completed)
            $table->string('status')->default('pending');    // pending, partial, completed
            
            $table->timestamps();
            
            // Indexes for quick lookups
            $table->index(['purchase_id']);
            $table->index(['vendor_id']);
            $table->index(['product_id']);
            $table->index(['status']);
            $table->unique(['purchase_id', 'product_id'], 'unique_purchase_product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_remaining');
    }
};
