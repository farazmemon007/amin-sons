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
        Schema::table('product_booking_items', function (Blueprint $table) {
            // Add discount_type column (percent or pkr)
            $table->enum('discount_type', ['percent', 'pkr'])->default('percent')->after('discount_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_booking_items', function (Blueprint $table) {
            $table->dropColumn('discount_type');
        });
    }
};
