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
        Schema::create('warehouse_orders', function (Blueprint $table) {
            $table->id();
            // $table->string('order_no')->unique();
            $table->string('dc_no')->nullable();
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('status')->default('pending');
            $table->text('remarks')->nullable();
            $table->string('prepared_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_orders');
    }
};
