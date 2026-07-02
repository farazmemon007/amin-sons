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
        // Table to log part/product replacements issued for complaints
        Schema::create('complaint_replacements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained('complaints')->cascadeOnDelete();
            $table->foreignId('issued_product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 12, 3)->default(1.000);
            $table->enum('source_location_type', ['shop', 'warehouse']);
            $table->unsignedBigInteger('source_warehouse_id')->nullable();
            
            // Damaged item collection details
            $table->foreignId('collected_damaged_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->decimal('damaged_qty', 12, 3)->default(0.000);
            $table->enum('damaged_status', ['retained_at_shop', 'transferred_to_warehouse', 'none'])->default('none');
            $table->unsignedBigInteger('transferred_warehouse_id')->nullable();
            
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->foreign('source_warehouse_id')->references('id')->on('warehouses')->nullOnDelete();
            $table->foreign('transferred_warehouse_id')->references('id')->on('warehouses')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        // Table to track defective / damaged parts inventory separate from clean sellable stock
        Schema::create('damaged_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->unsignedBigInteger('warehouse_id')->nullable(); // null means at branch shop
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 12, 3)->default(0.000);
            $table->timestamps();

            $table->unique(['branch_id', 'warehouse_id', 'product_id'], 'damaged_stocks_unique_triplet');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->nullOnDelete();
        });
        
        // Table to record transfer events of damaged stocks
        Schema::create('damaged_stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 12, 3);
            $table->unsignedBigInteger('to_warehouse_id');
            $table->string('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('to_warehouse_id')->references('id')->on('warehouses')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('damaged_stock_transfers');
        Schema::dropIfExists('damaged_stocks');
        Schema::dropIfExists('complaint_replacements');
    }
};
