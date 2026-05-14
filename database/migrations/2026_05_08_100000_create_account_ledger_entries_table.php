<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');

            // Voucher reference
            $table->enum('voucher_type', ['opening_balance', 'receipt', 'payment', 'expense', 'manual'])
                  ->default('manual');
            $table->string('voucher_no')->nullable();     // RVID-001 / PVID-001 etc.
            $table->unsignedBigInteger('voucher_id')->nullable(); // FK to the actual voucher record

            // Entry serial per account (BR-1, CR-1, JV-1, OB-1)
            $table->string('entry_no')->nullable();       // e.g., BR-1

            $table->date('transaction_date');
            $table->text('description')->nullable();

            // Double-entry columns
            $table->decimal('debit', 15, 2)->default(0.00);   // Money IN to this account
            $table->decimal('credit', 15, 2)->default(0.00);  // Money OUT from this account
            $table->decimal('running_balance', 15, 2)->default(0.00); // Cumulative balance

            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            // Indexes for fast ledger queries
            $table->index(['account_id', 'transaction_date']);
            $table->index(['account_id', 'voucher_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_ledger_entries');
    }
};
