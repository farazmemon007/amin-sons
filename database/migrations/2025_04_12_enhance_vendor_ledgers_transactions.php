<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add transaction tracking to vendor_ledgers for proper ERP accounting
     * 
     * ✅ International AccountingStandards (IFRS/IAS):
     *   - Each transaction creates a NEW ledger entry (not overwrite)
     *   - Running balance calculated from transaction history
     *   - Supports vendor aging and reconciliation
     * 
     * ✅ Double-Entry Bookkeeping:
     *   - Purchase: CR Accounts Payable (credit_amount)
     *   - Payment: DR Accounts Payable (debit_amount)
     */
    public function up(): void
    {
        // DEPRECATED: This migration has been superseded by 2025_08_18_000000_enhance_vendor_ledgers_transactions.php
        // which runs AFTER the vendor_ledgers table is created. This migration is kept for history only.
        // The actual enhancements are applied in the later migration.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_ledgers', function (Blueprint $table) {
            $table->dropColumn([
                'transaction_type',
                'reference_id',
                'transaction_date',
                'description',
                'debit_amount',
                'credit_amount',
                'running_balance',
            ]);
        });
    }
};
