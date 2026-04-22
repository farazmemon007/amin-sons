<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_request_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_request_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('requested_qty')->default(0);
            $table->integer('approved_qty')->nullable()->comment('Approved quantity (can be less than requested)');
            $table->unsignedBigInteger('from_warehouse_id')->nullable()->comment('Warehouse selected during approval');
            $table->decimal('unit_price', 10, 2)->nullable()->comment('Price at time of approval');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('stock_request_id')->references('id')->on('stock_requests')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('from_warehouse_id')->references('id')->on('warehouses')->onDelete('set null');
            
            $table->unique(['stock_request_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_request_items');
    }
};
