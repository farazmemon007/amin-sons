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
        Schema::table('accounts', function (Blueprint $table) {
            // Drop the global unique constraint on account_code
            $table->dropUnique('accounts_account_code_unique');
            
            // Add a composite unique constraint so each branch can have its own BANK-001, CASH-001, etc.
            $table->unique(['branch_id', 'account_code'], 'accounts_branch_account_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropUnique('accounts_branch_account_code_unique');
            $table->unique('account_code', 'accounts_account_code_unique');
        });
    }
};
