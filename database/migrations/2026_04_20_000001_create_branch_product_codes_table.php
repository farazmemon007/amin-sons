<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ✅ ERP Multi-Branch Product Codes
     * Stores branch-specific item codes (001, 002, 003 per branch)
     */
    public function up(): void
    {
        Schema::create('branch_product_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('item_code')->nullable(); // e.g., "001", "002" for this branch
            $table->integer('sequence')->default(0); // sequence counter per branch
            $table->boolean('is_primary')->default(false); // true if has warehouse_stocks in this branch
            $table->timestamps();
            
            // Unique constraint: one item code per branch-product combo
            $table->unique(['branch_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_product_codes');
    }
};
