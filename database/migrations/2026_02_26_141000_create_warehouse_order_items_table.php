<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_order_id')->index();
            $table->unsignedBigInteger('sale_item_id')->nullable()->index();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->string('product_name')->nullable();
            $table->string('item_code')->nullable();
            $table->decimal('qty', 14, 4)->default(0);
            $table->decimal('retail_price', 14, 2)->nullable();
            $table->decimal('amount', 14, 2)->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable()->index();
            $table->timestamps();

            $table->foreign('warehouse_order_id')->references('id')->on('warehouse_orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_order_items');
    }
};
