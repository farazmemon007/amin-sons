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
            // Add branch_id for branch-based counters
            if (!Schema::hasColumn('outward_gatepasses', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('id')->index();
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            }
            
            // Update gatepass_number column to be unique per branch (not globally unique)
            if (!Schema::hasColumn('outward_gatepasses', 'gatepass_number')) {
                $table->string('gatepass_number')->nullable()->after('branch_id')->comment('Formatted gatepass number like GP-001-0001');
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
            if (Schema::hasColumn('outward_gatepasses', 'branch_id')) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            }
        });
    }
};
