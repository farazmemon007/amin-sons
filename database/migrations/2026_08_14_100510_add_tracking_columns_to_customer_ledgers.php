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
        Schema::table('customer_ledgers', function (Blueprint $table) {
            $table->string('transaction_type')->nullable()->after('customer_id');
            $table->string('reference_id')->nullable()->after('transaction_type');
            $table->text('description')->nullable()->after('reference_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_ledgers', function (Blueprint $table) {
            $table->dropColumn(['transaction_type', 'reference_id', 'description']);
        });
    }
};
