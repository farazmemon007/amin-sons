<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to support complaints for specific product parts.
     */
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->boolean('is_product_part')->default(false)->after('product_model');
            $table->string('product_part_name')->nullable()->after('is_product_part');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn(['is_product_part', 'product_part_name']);
        });
    }
};
