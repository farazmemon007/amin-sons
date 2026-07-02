<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing collect_damaged column to complaint_replacements table.
     */
    public function up(): void
    {
        Schema::table('complaint_replacements', function (Blueprint $table) {
            // Add collect_damaged flag right before collected_damaged_product_id
            if (!Schema::hasColumn('complaint_replacements', 'collect_damaged')) {
                $table->boolean('collect_damaged')->default(false)->after('source_warehouse_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('complaint_replacements', function (Blueprint $table) {
            $table->dropColumn('collect_damaged');
        });
    }
};
