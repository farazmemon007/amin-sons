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
        // 1. productbookings
        Schema::table('productbookings', function (Blueprint $table) {
            $table->decimal('discount_percent', 15, 2)->default(0)->change();
        });

        // 2. product_booking_items
        Schema::table('product_booking_items', function (Blueprint $table) {
            $table->decimal('discount_percent', 15, 2)->default(0)->change();
        });

        // 3. sales
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('discount_percent', 15, 2)->default(0)->change();
        });

        // 4. sale_items
        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('discount_percent', 15, 2)->default(0)->change();
        });

        // 5. product_discounts
        Schema::table('product_discounts', function (Blueprint $table) {
            $table->decimal('discount_percentage', 15, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productbookings', function (Blueprint $table) {
            $table->decimal('discount_percent', 5, 2)->default(0)->change();
        });

        Schema::table('product_booking_items', function (Blueprint $table) {
            $table->decimal('discount_percent', 5, 2)->default(0)->change();
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('discount_percent', 5, 2)->default(0)->change();
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('discount_percent', 5, 2)->default(0)->change();
        });

        Schema::table('product_discounts', function (Blueprint $table) {
            $table->decimal('discount_percentage', 5, 2)->default(0)->change();
        });
    }
};
