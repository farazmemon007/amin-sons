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
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropUnique('stocks_unique_triplet');
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn('warehouse_id');
            $table->unique(['branch_id', 'product_id'], 'stocks_unique_pair');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropUnique('stocks_unique_pair');
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->unsignedBigInteger('warehouse_id')->nullable()->after('branch_id');
            $table->unique(['branch_id', 'warehouse_id', 'product_id'], 'stocks_unique_triplet');
        });
    }
};
