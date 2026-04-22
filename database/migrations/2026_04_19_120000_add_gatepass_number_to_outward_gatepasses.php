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
        Schema::table('outward_gatepasses', function (Blueprint $table) {
            // Add gatepass number column for formatted display (like GP-0001, GP-0002)
            if (!Schema::hasColumn('outward_gatepasses', 'gatepass_number')) {
                $table->string('gatepass_number')->nullable()->unique()->after('id')->comment('Formatted gatepass number like GP-0001');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outward_gatepasses', function (Blueprint $table) {
            if (Schema::hasColumn('outward_gatepasses', 'gatepass_number')) {
                $table->dropColumn('gatepass_number');
            }
        });
    }
};
