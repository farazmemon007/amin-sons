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
        if (Schema::hasTable('outward_gatepasses') && !Schema::hasColumn('outward_gatepasses', 'gatepass_date')) {
            Schema::table('outward_gatepasses', function (Blueprint $table) {
                $table->date('gatepass_date')->nullable()->after('delivery_city');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('outward_gatepasses') && Schema::hasColumn('outward_gatepasses', 'gatepass_date')) {
            Schema::table('outward_gatepasses', function (Blueprint $table) {
                $table->dropColumn('gatepass_date');
            });
        }
    }
};
