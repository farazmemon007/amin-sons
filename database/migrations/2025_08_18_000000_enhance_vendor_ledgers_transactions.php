<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add transaction tracking to vendor_ledgers for proper ERP accounting
     * 
     * ✅ International Accounting Standards (IFRS/IAS):
     *   - Each transaction creates a NEW ledger entry (not overwrite)
     *   - Running balance calculated from transaction history
     *   - Supports vendor aging and reconciliation
     * 
     * ✅ Double-Entry Bookkeeping:
     *   - Purchase: CR Accounts Payable (credit_amount)
     *   - Payment: DR Accounts Payable (debit_amount)
     * 
     * IMPORTANT: This migration runs AFTER vendor_ledgers table is created
     */
    public function up(): void
    {
        Schema::table('vendor_ledgers', function (Blueprint $table) {
            // Skip if columns already exist
            if (!Schema::hasColumn('vendor_ledgers', 'transaction_type')) {
                // Transaction tracking columns
                $table->string('transaction_type')->nullable()->comment('purchase, payment, credit_note, adjustment');
                $table->string('reference_id')->nullable()->comment('Purchase ID, Payment Voucher ID, etc.');
                $table->date('transaction_date')->nullable();
                $table->text('description')->nullable();
            }
            
            if (!Schema::hasColumn('vendor_ledgers', 'debit_amount')) {
                // Amount columns (instead of vague "opening/closing" balances)
                $table->decimal('debit_amount', 12, 2)->default(0)->comment('Payment reduces payable');
                $table->decimal('credit_amount', 12, 2)->default(0)->comment('Purchase increases payable');
                $table->decimal('running_balance', 12, 2)->nullable()->comment('Cumulative balance after this transaction');
            }
            
            // Deprecate old columns (keep for backward compatibility)
            // $table->drop(['opening_balance', 'previous_balance', 'closing_balance']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_ledgers', function (Blueprint $table) {
            if (Schema::hasColumn('vendor_ledgers', 'transaction_type')) {
                $table->dropColumn([
                    'transaction_type',
                    'reference_id',
                    'transaction_date',
                    'description',
                    'debit_amount',
                    'credit_amount',
                    'running_balance',
                ]);
            }
        });
    }
};
