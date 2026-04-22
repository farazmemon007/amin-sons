<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * ✅ Add missing columns to sales table:
     * - booking_id: FK to product bookings (tracks source booking for this sale)
     * - additional_discount: Extra discount amount from booking
     * - extra_charges: Additional charges from booking
     * 
     * Context: Sale model + SaleController expect these columns but they were never created
     * Error: SQLSTATE[42S22]: Column not found when trying to insert into sales
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Add booking_id after branch_id to track the source booking
            if (!Schema::hasColumn('sales', 'booking_id')) {
                $table->unsignedBigInteger('booking_id')
                    ->nullable()
                    ->after('branch_id')
                    ->index()
                    ->comment('FK to product bookings - tracks source booking for this sale');
                
                // Add foreign key constraint
                $table->foreign('booking_id')
                    ->references('id')
                    ->on('productbookings')
                    ->onDelete('set null'); // If booking deleted, keep sale but clear reference
            }

            // Add additional_discount column (comes from booking)
            if (!Schema::hasColumn('sales', 'additional_discount')) {
                $table->decimal('additional_discount', 12, 2)
                    ->default(0)
                    ->after('discount_amount')
                    ->comment('Extra discount amount from booking');
            }

            // Add extra_charges column (comes from booking)
            if (!Schema::hasColumn('sales', 'extra_charges')) {
                $table->decimal('extra_charges', 12, 2)
                    ->default(0)
                    ->after('additional_discount')
                    ->comment('Additional charges from booking');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Drop in reverse order
            if (Schema::hasColumn('sales', 'extra_charges')) {
                $table->dropColumn('extra_charges');
            }

            if (Schema::hasColumn('sales', 'additional_discount')) {
                $table->dropColumn('additional_discount');
            }

            if (Schema::hasColumn('sales', 'booking_id')) {
                $table->dropForeign(['booking_id']);
                $table->dropColumn('booking_id');
            }
        });
    }
};
