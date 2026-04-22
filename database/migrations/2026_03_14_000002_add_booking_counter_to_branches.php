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
        // ===== ADD BOOKING COUNTER TO BRANCHES =====
        // Each branch needs its own independent booking counter
        // Separate from invoice_counter which is for SALES
        // Bookings use: BINV-0001, BINV-0002, BINV-0003, etc.
        // Sales use: INV-0001, INV-0002, INV-0003, etc.
        
        Schema::table('branches', function (Blueprint $table) {
            if (!Schema::hasColumn('branches', 'booking_counter')) {
                $table->integer('booking_counter')->default(0)->after('invoice_counter');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            if (Schema::hasColumn('branches', 'booking_counter')) {
                $table->dropColumn('booking_counter');
            }
        });
    }
};
