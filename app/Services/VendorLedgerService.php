<?php

namespace App\Services;

use App\Models\VendorLedger;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

/**
 * ✅ VendorLedgerService
 * 
 * Implements International Accounting Standards (IFRS/IAS) for Accounts Payable
 * - Each purchase/payment creates a NEW transaction record
 * - Running balance calculated from all transactions
 * - Supports vendor aging and aging analysis
 * - Full transaction history preserved (audit trail)
 * 
 * ✅ Key Principles:
 * 1. CREDIT entries = Purchases (increase payable/liability)
 * 2. DEBIT entries = Payments (decrease payable/liability)
 * 3. Running balance = Sum of all credits - sum of all debits
 */
class VendorLedgerService
{
    /**
     * Record a purchase transaction (Accounts Payable increase)
     * 
     * ✅ Entry: CREDIT Accounts Payable
     * Increases vendor's payable balance
     * 
     * @param int $vendorId
     * @param float $amount
     * @param int $purchaseId
     * @param string|null $description
     * @return VendorLedger
     */
    public static function recordPurchase($vendorId, $amount, $purchaseId, $description = null)
    {
        return self::createLedgerEntry(
            vendorId: $vendorId,
            transactionType: 'purchase',
            referenceId: $purchaseId,
            creditAmount: $amount,
            debitAmount: 0,
            description: $description ?? "Purchase Invoice"
        );
    }

    /**
     * Record a payment transaction (Accounts Payable decrease)
     * 
     * ✅ Entry: DEBIT Accounts Payable
     * Decreases vendor's payable balance
     * 
     * @param int $vendorId
     * @param float $amount
     * @param string|int $paymentVoucherId
     * @param string|null $description
     * @return VendorLedger
     */
    public static function recordPayment($vendorId, $amount, $paymentVoucherId, $description = null)
    {
        return self::createLedgerEntry(
            vendorId: $vendorId,
            transactionType: 'payment',
            referenceId: $paymentVoucherId,
            creditAmount: 0,
            debitAmount: $amount,
            description: $description ?? "Payment Made"
        );
    }

    /**
     * Record a credit note (Accounts Payable decrease - return/adjustment)
     * 
     * @param int $vendorId
     * @param float $amount
     * @param string $referenceId
     * @param string|null $description
     * @return VendorLedger
     */
    public static function recordCreditNote($vendorId, $amount, $referenceId, $description = null)
    {
        return self::createLedgerEntry(
            vendorId: $vendorId,
            transactionType: 'credit_note',
            referenceId: $referenceId,
            creditAmount: 0,
            debitAmount: $amount,
            description: $description ?? "Credit Note / Return"
        );
    }

    /**
     * Create a vendor ledger transaction entry
     * 
     * ✅ Each call creates a NEW record (not updateOrCreate)
     * ✅ Calculates running balance from all previous transactions
     * 
     * @return VendorLedger
     */
    private static function createLedgerEntry(
        $vendorId,
        $transactionType,
        $referenceId,
        $creditAmount = 0,
        $debitAmount = 0,
        $description = null
    ) {
        // Calculate running balance from all previous transactions for this vendor
        $previousTransactions = VendorLedger::where('vendor_id', $vendorId)
            ->orderBy('created_at', 'asc')
            ->get();

        $runningBalance = 0;
        foreach ($previousTransactions as $entry) {
            $runningBalance += ($entry->credit_amount ?? 0) - ($entry->debit_amount ?? 0);
        }

        // Add current transaction
        $runningBalance += $creditAmount - $debitAmount;

        // ✅ Create NEW ledger entry (not updateOrCreate)
        $ledgerEntry = VendorLedger::create([
            'vendor_id'        => $vendorId,
            'admin_or_user_id' => auth()->id(),
            'transaction_type' => $transactionType,
            'reference_id'     => $referenceId,
            'transaction_date' => now()->format('Y-m-d'),
            'description'      => $description,
            'credit_amount'    => $creditAmount,  // Purchase increases payable
            'debit_amount'     => $debitAmount,   // Payment decreases payable
            'running_balance'  => $runningBalance, // Current vendor payable amount

            // Keep backward compatibility with old columns
            'opening_balance'  => $runningBalance - $creditAmount + $debitAmount,
            'closing_balance'  => $runningBalance,
            'previous_balance' => $runningBalance - $creditAmount + $debitAmount,
        ]);

        return $ledgerEntry;
    }

    /**
     * Get vendor's current outstanding payable balance
     * 
     * ✅ Latest running balance = Total amount owed to vendor
     * 
     * @param int $vendorId
     * @return float
     */
    public static function getVendorBalance($vendorId)
    {
        $latestEntry = VendorLedger::where('vendor_id', $vendorId)
            ->latest('created_at')
            ->first();

        return $latestEntry?->running_balance ?? 0;
    }

    /**
     * Get vendor statement (transaction history)
     * 
     * ✅ For vendor aging and reconciliation
     * 
     * @param int $vendorId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getVendorStatement($vendorId, $startDate = null, $endDate = null)
    {
        $query = VendorLedger::where('vendor_id', $vendorId)
            ->orderBy('transaction_date', 'asc')
            ->orderBy('created_at', 'asc');

        if ($startDate) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }

        return $query->get();
    }

    /**
     * Get total payables across all vendors
     * 
     * ✅ For balance sheet reporting
     * 
     * @return float
     */
    public static function getTotalPayables()
    {
        return VendorLedger::whereIn('id', function ($query) {
            // Get latest entry per vendor
            $query->selectRaw('max(id)')
                ->from('vendor_ledgers')
                ->groupBy('vendor_id');
        })
        ->sum('running_balance');
    }
}
