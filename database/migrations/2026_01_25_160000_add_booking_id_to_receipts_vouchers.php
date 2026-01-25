<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBookingIdToReceiptsVouchers extends Migration
{
    public function up()
    {
        Schema::table('receipts_vouchers', function (Blueprint $table) {
            $table->unsignedBigInteger('booking_id')->nullable()->after('reference_no')->index();
        });
    }

    public function down()
    {
        Schema::table('receipts_vouchers', function (Blueprint $table) {
            $table->dropIndex(['booking_id']);
            $table->dropColumn('booking_id');
        });
    }
}
