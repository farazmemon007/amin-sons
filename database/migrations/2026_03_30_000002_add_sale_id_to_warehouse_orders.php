<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouse_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('warehouse_orders', 'sale_id')) {
                $table->unsignedBigInteger('sale_id')->nullable()->after('customer_id');
                $table->foreign('sale_id')->references('id')->on('sales')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('warehouse_orders', function (Blueprint $table) {
            if (Schema::hasColumn('warehouse_orders', 'sale_id')) {
                $table->dropForeign(['sale_id']);
                $table->dropColumn('sale_id');
            }
        });
    }
};
