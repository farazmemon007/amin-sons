<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Alter table using raw SQL to ensure nullable issued_product_id without doctrine/dbal requirement
        DB::statement('ALTER TABLE complaint_replacements MODIFY issued_product_id bigint unsigned NULL');

        Schema::table('complaint_replacements', function (Blueprint $table) {
            $table->string('replacement_slip_no')->nullable()->unique()->after('complaint_id');
            $table->enum('claim_status', ['pending', 'claimed'])->default('claimed')->after('replacement_slip_no');
            $table->timestamp('claimed_at')->nullable()->after('claim_status');
            $table->unsignedBigInteger('claimed_by')->nullable()->after('claimed_at');

            $table->foreign('claimed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('complaint_replacements', function (Blueprint $table) {
            $table->dropForeign(['claimed_by']);
            $table->dropColumn(['replacement_slip_no', 'claim_status', 'claimed_at', 'claimed_by']);
        });

        DB::statement('ALTER TABLE complaint_replacements MODIFY issued_product_id bigint unsigned NOT NULL');
    }
};
