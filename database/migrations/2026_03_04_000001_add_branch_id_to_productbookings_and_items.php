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
        Schema::table('productbookings', function (Blueprint $table) {
            if (! Schema::hasColumn('productbookings', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('id')->index();
            }
        });

        Schema::table('product_booking_items', function (Blueprint $table) {
            if (! Schema::hasColumn('product_booking_items', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('booking_id')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_booking_items', function (Blueprint $table) {
            if (Schema::hasColumn('product_booking_items', 'branch_id')) {
                $table->dropColumn('branch_id');
            }
        });

        Schema::table('productbookings', function (Blueprint $table) {
            if (Schema::hasColumn('productbookings', 'branch_id')) {
                $table->dropColumn('branch_id');
            }
        });
    }
};
