<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Journal Voucher — used when a shop owner wants to transfer
     * a customer's outstanding payment directly to a vendor.
     * Both Customer Ledger (credit) and Vendor Ledger (debit) are affected.
     */
    public function up(): void
    {
        Schema::create('journal_vouchers', function (Blueprint $table) {
            $table->id();

            // Voucher Identity
            $table->string('jvid')->unique();               // e.g. JVID-00001
            $table->date('voucher_date');                   // Voucher date
            $table->date('entry_date');                     // System entry date
            $table->text('remarks')->nullable();            // Remarks / description
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            // DEBIT SIDE — Vendor (payable reduced / expense)
            $table->string('debit_party_type');             // 'vendor' | 'customer' | 'account'
            $table->unsignedBigInteger('debit_party_id')->nullable();  // vendor_id / customer_id / account_id

            // CREDIT SIDE — Customer (receivable reduced)
            $table->string('credit_party_type');            // 'vendor' | 'customer' | 'account'
            $table->unsignedBigInteger('credit_party_id')->nullable(); // vendor_id / customer_id / account_id

            // Amount
            $table->decimal('amount', 15, 2)->default(0);

            // Extra rows (for multi-line JVs) - JSON stored
            $table->text('narration_id')->nullable();       // JSON array of narration ids
            $table->text('reference_no')->nullable();       // JSON array of references

            // Status
            $table->enum('status', ['draft', 'posted'])->default('posted');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('branch_id');
            $table->index('debit_party_id');
            $table->index('credit_party_id');
            $table->index('voucher_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_vouchers');
    }
};
