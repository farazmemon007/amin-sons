<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_orders', 'warehouse_id')) {
                $table->foreignId('warehouse_id')->nullable()->after('branch_id')->constrained();
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders', 'warehouse_id')) {
                $table->dropForeign(['warehouse_id']);
                $table->dropColumn('warehouse_id');
            }
        });
    }
};
