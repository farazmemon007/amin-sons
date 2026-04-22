<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ===== ADD INVOICE_NO COLUMN TO PRODUCT_BOOKING_ITEMS =====
        Schema::table('product_booking_items', function (Blueprint $table) {
            if (!Schema::hasColumn('product_booking_items', 'invoice_no')) {
                $table->string('invoice_no')->nullable()->after('booking_id');
            }
        });

        // ===== CRITICAL DATA FIX =====
        // Update product_booking_items to use correct BINV prefix from parent productbookings
        
        DB::statement('
            UPDATE product_booking_items 
            SET invoice_no = (
                SELECT invoice_no FROM productbookings 
                WHERE productbookings.id = product_booking_items.booking_id
            )
            WHERE booking_id IS NOT NULL
        ');

        \Log::info('Fixed product_booking_items to use correct BINV invoice numbers', [
            'timestamp' => now(),
            'description' => 'All product_booking_items now correctly reference their parent productbooking invoice_no'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the column if needed
        Schema::table('product_booking_items', function (Blueprint $table) {
            if (Schema::hasColumn('product_booking_items', 'invoice_no')) {
                $table->dropColumn('invoice_no');
            }
        });
    }
};

