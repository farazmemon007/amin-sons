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
        // ===== ADD DC COUNTER TO BRANCHES =====
        // Each branch needs its own independent DC counter
        // DCs (Delivery Challans) use: DC-0001, DC-0002, DC-0003, etc.
        
        Schema::table('branches', function (Blueprint $table) {
            if (!Schema::hasColumn('branches', 'dc_counter')) {
                $table->integer('dc_counter')->default(0)->after('booking_counter');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            if (Schema::hasColumn('branches', 'dc_counter')) {
                $table->dropColumn('dc_counter');
            }
        });
    }
};
