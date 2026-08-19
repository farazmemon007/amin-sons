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
        Schema::table('receipts_vouchers', function (Blueprint $table) {
            if (!Schema::hasColumn('receipts_vouchers', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('id')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receipts_vouchers', function (Blueprint $table) {
            if (Schema::hasColumn('receipts_vouchers', 'branch_id')) {
                $table->dropColumn('branch_id');
            }
        });
    }
};
