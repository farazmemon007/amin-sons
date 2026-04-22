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
            // ✅ Add delivery status column with enum values
            if (!Schema::hasColumn('outward_gatepasses', 'status')) {
                $table->enum('status', ['pending', 'in_transit', 'delivered'])->default('pending')->after('dc_no');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outward_gatepasses', function (Blueprint $table) {
            if (Schema::hasColumn('outward_gatepasses', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
