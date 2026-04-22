<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds two-phase product creation workflow:
     * - 'profile_only': Product profile created, awaiting opening stock configuration
     * - 'complete': Product fully configured (opening stock, prices, warehouses assigned)
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Tracks product completion status for 2-phase creation workflow
            $table->enum('completion_status', ['profile_only', 'complete'])->default('profile_only')->after('is_assembled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['completion_status']);
        });
    }
};
