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
        // Add default(0) explicitly to avoid "doesn't have a default value" errors
        
        Schema::table('productbookings', function (Blueprint $table) {
            $table->decimal('discount_percent', 15, 2)->default(0)->change();
        });

        Schema::table('product_booking_items', function (Blueprint $table) {
            $table->decimal('discount_percent', 15, 2)->default(0)->change();
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('discount_percent', 15, 2)->default(0)->change();
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('discount_percent', 15, 2)->default(0)->change();
        });

        Schema::table('product_discounts', function (Blueprint $table) {
            $table->decimal('discount_percentage', 15, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op or revert to old precision if needed, but keeping precision is safer
    }
};
