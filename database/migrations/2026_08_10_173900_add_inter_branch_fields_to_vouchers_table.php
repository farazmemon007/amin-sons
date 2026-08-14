<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            if (!Schema::hasColumn('vouchers', 'from_branch_id')) {
                $table->unsignedBigInteger('from_branch_id')->nullable()->after('amount');
            }
            if (!Schema::hasColumn('vouchers', 'to_branch_id')) {
                $table->unsignedBigInteger('to_branch_id')->nullable()->after('from_branch_id');
            }
            if (!Schema::hasColumn('vouchers', 'method')) {
                $table->enum('method', ['cash', 'bank', 'cheque'])->default('cash')->after('to_branch_id');
            }
            if (!Schema::hasColumn('vouchers', 'reference')) {
                $table->string('reference')->nullable()->after('method');
            }
            if (!Schema::hasColumn('vouchers', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('reference');
            }
            if (!Schema::hasColumn('vouchers', 'remarks')) {
                $table->text('remarks')->nullable()->after('created_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn(['from_branch_id', 'to_branch_id', 'method', 'reference', 'created_by', 'remarks']);
        });
    }
};
