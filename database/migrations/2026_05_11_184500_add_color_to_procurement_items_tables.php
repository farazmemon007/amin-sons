<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_order_items', 'color')) {
                $table->string('color')->nullable()->after('product_id');
            }
        });

        Schema::table('inward_gatepass_items', function (Blueprint $table) {
            if (!Schema::hasColumn('inward_gatepass_items', 'color')) {
                $table->string('color')->nullable()->after('product_id');
            }
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_items', 'color')) {
                $table->string('color')->nullable()->after('product_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn('color');
        });

        Schema::table('inward_gatepass_items', function (Blueprint $table) {
            $table->dropColumn('color');
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
