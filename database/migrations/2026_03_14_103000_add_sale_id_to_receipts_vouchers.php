<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Add sale_id column to receipts_vouchers
     * This allows receipts to reference the actual SALE instead of just the booking
     */
    public function up(): void
    {
        Schema::table('receipts_vouchers', function (Blueprint $table) {
            // Add sale_id column (nullable for backward compatibility)
            if (!Schema::hasColumn('receipts_vouchers', 'sale_id')) {
                $table->bigInteger('sale_id')->nullable()->after('booking_id')->comment('Foreign key to sales table');
            }
            
            // Make sure reference_no column exists and is large enough
            if (!Schema::hasColumn('receipts_vouchers', 'reference_no')) {
                $table->string('reference_no', 50)->nullable()->comment('Invoice number (INV-XXXX or INVSLE-XXXX)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receipts_vouchers', function (Blueprint $table) {
            if (Schema::hasColumn('receipts_vouchers', 'sale_id')) {
                $table->dropColumn('sale_id');
            }
        });
    }
};
