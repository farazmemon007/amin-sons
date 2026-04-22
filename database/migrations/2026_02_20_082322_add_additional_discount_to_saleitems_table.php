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
        Schema::table('sales', function (Blueprint $table) {
            
            // $table->decimal('additional_discount', 15, 2)
            //       ->default(0)
            //       ->after('discount_amount');
            $table->decimal('additional_discount', 12, 2)->default(0)->after('discount_amount')->comment('Extra discount on order level');
            $table->decimal('extra_charges', 12, 2)->default(0)->after('additional_discount')->comment('Additional charges (shipping, tax, etc)');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            
            $table->dropColumn('additional_discount');
            $table->dropColumn('extra_charges');

        });
    }
};