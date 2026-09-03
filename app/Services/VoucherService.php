<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\Vendor;
use App\Models\VendorLedger;
use App\Models\Account;
use App\Models\AccountLedgerEntry;
use App\Models\ReceiptsVoucher;
use App\Models\PaymentVoucher;
use App\Models\ExpenseVoucher;
use App\Models\JournalVoucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VoucherService
{
    /* =========================================================================
     * 1. RECEIPTS VOUCHER (CRV / BRV)
     * ========================================================================= */

    /**
     * Reverse a Receipt Voucher completely.
     * Original: Party Credited (receivable reduced), Cash/Bank Accounts Debited (funds increased).
     * Reversal: Party Debited (receivable restored), Cash/Bank Accounts Credited (funds deducted).
     */
    public static function reverseReceiptVoucher(ReceiptsVoucher $voucher, $userId, bool $isVoid = true): bool
    {
        DB::beginTransaction();
        try {
            $totalAmount = (float) $voucher->total_amount;
            $rvid        = $voucher->rvid;
            $date        = now()->toDateString();
            $branchId    = $voucher->branch_id ?? (auth()->check() ? auth()->user()->branch_id : 0);

            // 1. REVERSE PARTY SIDE (Debiting Party to restore receivable/adjust liability)
            if ($voucher->type === 'customer' || $voucher->type === 'walkin') {
                $lastLedger = CustomerLedger::where('customer_id', $voucher->party_id)
                    ->orderBy('id', 'desc')
                    ->first();

                $customer        = Customer::find($voucher->party_id);
                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($customer->opening_balance ?? 0);

                CustomerLedger::create([
                    'customer_id'      => $voucher->party_id,
                    'admin_or_user_id' => $userId,
                    'previous_balance' => $previousBalance,
                    'opening_balance'  => $customer->opening_balance ?? 0,
                    'total_debit'      => $totalAmount, // Reversed: Customer balance restored
                    'total_credit'     => 0,
                    'closing_balance'  => $previousBalance + $totalAmount,
                    'transaction_type' => 'reversal',
                    'reference_id'     => $rvid,
                    'description'      => "Reversal of Receipt Voucher #{$rvid}",
                ]);
            } elseif ($voucher->type === 'vendor') {
                $lastLedger = VendorLedger::where('vendor_id', $voucher->party_id)
                    ->where('branch_id', $branchId)
                    ->orderBy('id', 'desc')
                    ->first();

                $vendor          = Vendor::find($voucher->party_id);
                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($vendor->opening_balance ?? 0);

                VendorLedger::create([
                    'vendor_id'        => $voucher->party_id,
                    'branch_id'        => $branchId,
                    'admin_or_user_id' => $userId,
                    'transaction_date' => $date,
                    'description'      => "Reversal of Receipt Voucher #{$rvid}",
                    'opening_balance'  => $vendor->opening_balance ?? 0,
                    'previous_balance' => $previousBalance,
                    'debit_amount'     => $totalAmount, // Reversed
                    'credit_amount'    => 0,
                    'closing_balance'  => $previousBalance - $totalAmount,
                    'transaction_type' => 'reversal',
                    'reference_id'     => $rvid,
                ]);
            } elseif (is_numeric($voucher->type)) {
                $account = Account::find($voucher->party_id);
                if ($account) {
                    $account->opening_balance += $totalAmount;
                    $account->save();

                    self::postLedgerEntry(
                        $account->id,
                        'manual',
                        $rvid,
                        $voucher->id,
                        $date,
                        "Reversal of Receipt Party Side: #{$rvid}",
                        $totalAmount,
                        0,
                        $userId
                    );
                }
            }

            // 2. REVERSE ACCOUNT SIDE (Crediting Cash/Bank accounts)
            $accounts = self::safeDecodeArray($voucher->row_account_id);
            $amounts  = self::safeDecodeArray($voucher->amount);

            foreach ($accounts as $index => $accId) {
                $rowAmount = (float)($amounts[$index] ?? 0);
                if ($rowAmount <= 0 || !$accId) continue;

                $rowAccount = Account::find($accId);
                if ($rowAccount) {
                    if (trim(strtolower($rowAccount->type)) === 'debit') {
                        $rowAccount->opening_balance -= $rowAmount; // Reverse
                    } else {
                        $rowAccount->opening_balance += $rowAmount; // Reverse
                    }
                    $rowAccount->save();

                    self::postLedgerEntry(
                        $accId,
                        'manual',
                        $rvid,
                        $voucher->id,
                        $date,
                        "Reversal of Receipt from Party: #{$rvid}",
                        0,
                        $rowAmount,
                        $userId
                    );
                }
            }

            // 3. Mark Voucher Status
            if ($isVoid) {
                $voucher->processed = 0;
                $voucher->status    = 'voided';
                $voucher->save();
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Receipt Voucher Reversal Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Post ledger entries for a Receipt Voucher.
     */
    public static function applyReceiptVoucher(ReceiptsVoucher $voucher, array $data, $userId): bool
    {
        $totalAmount = (float)($data['total_amount'] ?? $voucher->total_amount);
        $rvid        = $voucher->rvid;
        $branchId    = (int)($data['branch_id'] ?? $voucher->branch_id ?? 1);
        $receiptDate = $data['receipt_date'] ?? $voucher->receipt_date ?? now()->toDateString();
        $partyType   = $data['type'] ?? $data['vendor_type'] ?? $voucher->type;
        $partyId     = $data['party_id'] ?? $data['vendor_id'] ?? $voucher->party_id;
        $remarks     = $data['remarks'] ?? $voucher->remarks ?? '';

        // 1. PARTY SIDE POSTING (CREDIT)
        if ($partyType === 'customer' || $partyType === 'walkin') {
            $lastLedger = CustomerLedger::where('customer_id', $partyId)
                ->orderBy('id', 'desc')
                ->first();

            $customer        = Customer::find($partyId);
            $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($customer->opening_balance ?? 0);

            CustomerLedger::create([
                'customer_id'      => $partyId,
                'admin_or_user_id' => $userId,
                'previous_balance' => $previousBalance,
                'opening_balance'  => $customer->opening_balance ?? 0,
                'total_debit'      => 0,
                'total_credit'     => $totalAmount,
                'closing_balance'  => $previousBalance - $totalAmount,
                'transaction_type' => 'receipt',
                'reference_id'     => $rvid,
                'description'      => "Receipt Voucher #{$rvid}" . ($remarks ? " - {$remarks}" : ""),
            ]);
        } elseif ($partyType === 'vendor') {
            $lastLedger = VendorLedger::where('vendor_id', $partyId)
                ->where('branch_id', $branchId)
                ->orderBy('id', 'desc')
                ->first();

            $vendor          = Vendor::find($partyId);
            $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($vendor->opening_balance ?? 0);

            VendorLedger::create([
                'vendor_id'        => $partyId,
                'branch_id'        => $branchId,
                'admin_or_user_id' => $userId,
                'transaction_date' => $receiptDate,
                'description'      => "Receipt Voucher #{$rvid}" . ($remarks ? " - {$remarks}" : ""),
                'opening_balance'  => $vendor->opening_balance ?? 0,
                'previous_balance' => $previousBalance,
                'debit_amount'     => 0,
                'credit_amount'    => $totalAmount,
                'closing_balance'  => $previousBalance + $totalAmount,
                'transaction_type' => 'receipt',
                'reference_id'     => $rvid,
            ]);
        } elseif (is_numeric($partyType)) {
            $account = Account::find($partyId);
            if ($account) {
                self::postLedgerEntry(
                    $account->id,
                    'receipt',
                    $rvid,
                    $voucher->id,
                    $receiptDate,
                    "Receipt Voucher Party Side: " . ($remarks ?: 'N/A'),
                    0,
                    $totalAmount,
                    $userId
                );
                $account->opening_balance -= $totalAmount;
                $account->save();
            }
        }

        // 2. ACCOUNT SIDE POSTING (DEBIT - Cash / Bank Accounts)
        $rowAccountIds = is_array($data['row_account_id'] ?? null) 
            ? $data['row_account_id'] 
            : self::safeDecodeArray($voucher->row_account_id);
        $amounts = is_array($data['amount'] ?? null) 
            ? $data['amount'] 
            : self::safeDecodeArray($voucher->amount);

        foreach ($rowAccountIds as $index => $accId) {
            $rowAmount = (float)($amounts[$index] ?? 0);
            if ($rowAmount <= 0 || !$accId) continue;

            $rowAccount = Account::find($accId);
            if ($rowAccount) {
                if (trim(strtolower($rowAccount->type)) === 'debit') {
                    $rowAccount->opening_balance += $rowAmount;
                } else {
                    $rowAccount->opening_balance -= $rowAmount;
                }
                $rowAccount->save();

                self::postLedgerEntry(
                    $accId,
                    'receipt',
                    $rvid,
                    $voucher->id,
                    $receiptDate,
                    "Receipt from Party: " . ($remarks ?: "Voucher #{$rvid}"),
                    $rowAmount,
                    0,
                    $userId
                );
            }
        }

        return true;
    }


    /* =========================================================================
     * 2. PAYMENT VOUCHER (CPV / BPV)
     * ========================================================================= */

    /**
     * Reverse a Payment Voucher completely.
     * Original: Cash/Bank Credited (funds decreased), Party Debited (liability reduced).
     * Reversal: Cash/Bank Debited (funds restored), Party Credited (liability restored).
     */
    public static function reversePaymentVoucher(PaymentVoucher $voucher, $userId, bool $isVoid = true): bool
    {
        DB::beginTransaction();
        try {
            $totalAmount = (float) $voucher->total_amount;
            $pvid        = $voucher->pvid;
            $date        = now()->toDateString();
            $branchId    = $voucher->branch_id ?? (auth()->check() ? auth()->user()->branch_id : 0);

            // 1. REVERSE ROW ACCOUNTS (Cash / Bank accounts credited originally, now debited back)
            $accounts = self::safeDecodeArray($voucher->row_account_id);
            $amounts  = self::safeDecodeArray($voucher->amount);

            foreach ($accounts as $index => $accId) {
                $rowAmount = isset($amounts[$index]) ? (float)$amounts[$index] : 0;
                if ($rowAmount > 0 && $accId) {
                    $rowAccount = Account::find($accId);
                    if ($rowAccount) {
                        $rowAccount->opening_balance += $rowAmount;
                        $rowAccount->save();

                        self::postLedgerEntry(
                            $accId,
                            'manual',
                            $pvid,
                            $voucher->id,
                            $date,
                            "Reversal of Payment: #{$pvid}",
                            $rowAmount,
                            0,
                            $userId
                        );
                    }
                }
            }

            // 2. REVERSE PARTY SIDE (Vendor/Customer/Account credited to restore payable)
            if ($voucher->type === 'vendor') {
                $lastLedger = VendorLedger::where('vendor_id', $voucher->party_id)
                    ->where('branch_id', $branchId)
                    ->orderBy('id', 'desc')
                    ->first();

                $vendor          = Vendor::find($voucher->party_id);
                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($vendor->opening_balance ?? 0);

                VendorLedger::create([
                    'vendor_id'        => $voucher->party_id,
                    'branch_id'        => $branchId,
                    'admin_or_user_id' => $userId,
                    'transaction_date' => $date,
                    'description'      => "Reversal of Payment Voucher #{$pvid}",
                    'opening_balance'  => $vendor->opening_balance ?? 0,
                    'previous_balance' => $previousBalance,
                    'debit_amount'     => 0,
                    'credit_amount'    => $totalAmount, // Reversed: Payable increased
                    'closing_balance'  => $previousBalance + $totalAmount,
                    'transaction_type' => 'reversal',
                    'reference_id'     => $pvid,
                ]);
            } elseif ($voucher->type === 'customer' || $voucher->type === 'walkin') {
                $lastLedger = CustomerLedger::where('customer_id', $voucher->party_id)
                    ->orderBy('id', 'desc')
                    ->first();

                $customer        = Customer::find($voucher->party_id);
                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($customer->opening_balance ?? 0);

                CustomerLedger::create([
                    'customer_id'      => $voucher->party_id,
                    'admin_or_user_id' => $userId,
                    'previous_balance' => $previousBalance,
                    'opening_balance'  => $customer->opening_balance ?? 0,
                    'total_debit'      => 0,
                    'total_credit'     => $totalAmount,
                    'closing_balance'  => $previousBalance - $totalAmount,
                    'transaction_type' => 'reversal',
                    'reference_id'     => $pvid,
                    'description'      => "Reversal of Payment Voucher #{$pvid}",
                ]);
            } elseif (is_numeric($voucher->type)) {
                $account = Account::find($voucher->party_id);
                if ($account) {
                    $account->opening_balance -= $totalAmount;
                    $account->save();

                    self::postLedgerEntry(
                        $account->id,
                        'manual',
                        $pvid,
                        $voucher->id,
                        $date,
                        "Reversal of Payment Party Side: #{$pvid}",
                        0,
                        $totalAmount,
                        $userId
                    );
                }
            }

            // 3. Mark Voucher Status
            if ($isVoid) {
                $voucher->processed = 0;
                $voucher->status    = 'voided';
                $voucher->save();
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Payment Voucher Reversal Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Post ledger entries for a Payment Voucher.
     */
    public static function applyPaymentVoucher(PaymentVoucher $voucher, array $data, $userId): bool
    {
        $totalAmount = (float)($data['total_amount'] ?? $voucher->total_amount);
        $pvid        = $voucher->pvid;
        $branchId    = (int)($data['branch_id'] ?? $voucher->branch_id ?? (auth()->user()->branch_id ?? 0));
        $receiptDate = $data['receipt_date'] ?? $voucher->receipt_date ?? now()->toDateString();
        $partyType   = $data['type'] ?? $data['vendor_type'] ?? $voucher->type;
        $partyId     = $data['party_id'] ?? $data['vendor_id'] ?? $voucher->party_id;
        $remarks     = $data['remarks'] ?? $voucher->remarks ?? '';

        // 1. ROW ACCOUNTS POSTING (CREDIT - Paid from Bank/Cash)
        $rowAccountIds = is_array($data['row_account_id'] ?? null) 
            ? $data['row_account_id'] 
            : self::safeDecodeArray($voucher->row_account_id);
        $amounts = is_array($data['amount'] ?? null) 
            ? $data['amount'] 
            : self::safeDecodeArray($voucher->amount);

        foreach ($rowAccountIds as $index => $accId) {
            $rowAmount = isset($amounts[$index]) ? (float)$amounts[$index] : 0;
            if ($rowAmount > 0 && $accId) {
                $rowAccount = Account::find($accId);
                if ($rowAccount) {
                    $rowAccount->opening_balance -= $rowAmount;
                    $rowAccount->save();

                    self::postLedgerEntry(
                        $accId,
                        'payment',
                        $pvid,
                        $voucher->id,
                        $receiptDate,
                        $remarks ?: "Payment Voucher #{$pvid}",
                        0,
                        $rowAmount,
                        $userId
                    );
                }
            }
        }

        // 2. PARTY SIDE POSTING (DEBIT - Vendor payable reduced / Customer receivable increased)
        if ($partyType === 'vendor') {
            $lastLedger = VendorLedger::where('vendor_id', $partyId)
                ->where('branch_id', $branchId)
                ->orderBy('id', 'desc')
                ->first();

            $vendor          = Vendor::find($partyId);
            $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($vendor->opening_balance ?? 0);

            VendorLedger::create([
                'vendor_id'        => $partyId,
                'branch_id'        => $branchId,
                'admin_or_user_id' => $userId,
                'transaction_date' => $receiptDate,
                'description'      => "Payment Voucher #{$pvid}" . ($remarks ? " - {$remarks}" : ""),
                'opening_balance'  => $previousBalance,
                'previous_balance' => $previousBalance,
                'debit_amount'     => $totalAmount,
                'credit_amount'    => 0,
                'closing_balance'  => $previousBalance - $totalAmount,
                'transaction_type' => 'payment',
                'reference_id'     => $pvid,
            ]);
        } elseif ($partyType === 'customer' || $partyType === 'walkin') {
            $lastLedger = CustomerLedger::where('customer_id', $partyId)
                ->orderBy('id', 'desc')
                ->first();

            $customer        = Customer::find($partyId);
            $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($customer->opening_balance ?? 0);

            CustomerLedger::create([
                'customer_id'      => $partyId,
                'admin_or_user_id' => $userId,
                'previous_balance' => $previousBalance,
                'opening_balance'  => $previousBalance,
                'total_debit'      => $totalAmount,
                'total_credit'     => 0,
                'closing_balance'  => $previousBalance + $totalAmount,
                'transaction_type' => 'payment',
                'reference_id'     => $pvid,
                'description'      => "Payment Voucher #{$pvid}" . ($remarks ? " - {$remarks}" : ""),
            ]);
        } elseif (is_numeric($partyType)) {
            $account = Account::find($partyId);
            if ($account) {
                $account->opening_balance += $totalAmount;
                $account->save();

                self::postLedgerEntry(
                    $account->id,
                    'payment',
                    $pvid,
                    $voucher->id,
                    $receiptDate,
                    "Payment Voucher Party Side: " . ($remarks ?: 'N/A'),
                    $totalAmount,
                    0,
                    $userId
                );
            }
        }

        return true;
    }


    /* =========================================================================
     * 3. EXPENSE VOUCHER (EV)
     * ========================================================================= */

    /**
     * Reverse an Expense Voucher completely.
     * Original: Source Account Credited (funds decreased), Expense Accounts Debited (expenses increased).
     * Reversal: Source Account Debited (funds restored), Expense Accounts Credited (expenses decreased).
     */
    public static function reverseExpenseVoucher(ExpenseVoucher $voucher, $userId, bool $isVoid = true): bool
    {
        DB::beginTransaction();
        try {
            $totalAmount = (float) $voucher->total_amount;
            $evid        = $voucher->evid;
            $date        = now()->toDateString();
            $branchId    = $voucher->branch_id ?? (auth()->check() ? auth()->user()->branch_id : 0);

            // 1. REVERSE EXPENSE ACCOUNTS (Row side: Debited originally, now Credited back)
            $accounts = self::safeDecodeArray($voucher->row_account_id);
            $amounts  = self::safeDecodeArray($voucher->amount);

            foreach ($accounts as $index => $accId) {
                $rowAmount = isset($amounts[$index]) ? (float)$amounts[$index] : 0;
                if ($rowAmount > 0 && $accId) {
                    $rowAccount = Account::find($accId);
                    if ($rowAccount) {
                        $rowAccount->opening_balance -= $rowAmount; // Deduct expense back
                        $rowAccount->save();

                        self::postLedgerEntry(
                            $accId,
                            'manual',
                            $evid,
                            $voucher->id,
                            $date,
                            "Reversal of Expense Head: #{$evid}",
                            0,
                            $rowAmount,
                            $userId
                        );
                    }
                }
            }

            // 2. REVERSE PAYMENT SOURCE / PARTY SIDE (Credited originally, now Debited back)
            if ($voucher->type === 'vendor') {
                $lastLedger = VendorLedger::where('vendor_id', $voucher->party_id)
                    ->where('branch_id', $branchId)
                    ->orderBy('id', 'desc')
                    ->first();

                $vendor          = Vendor::find($voucher->party_id);
                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($vendor->opening_balance ?? 0);

                VendorLedger::create([
                    'vendor_id'        => $voucher->party_id,
                    'branch_id'        => $branchId,
                    'admin_or_user_id' => $userId,
                    'transaction_date' => $date,
                    'description'      => "Reversal of Expense Voucher #{$evid}",
                    'opening_balance'  => $vendor->opening_balance ?? 0,
                    'previous_balance' => $previousBalance,
                    'debit_amount'     => $totalAmount, // Reversed
                    'credit_amount'    => 0,
                    'closing_balance'  => $previousBalance - $totalAmount,
                    'transaction_type' => 'reversal',
                    'reference_id'     => $evid,
                ]);
            } elseif ($voucher->type === 'customer' || $voucher->type === 'walkin') {
                $lastLedger = CustomerLedger::where('customer_id', $voucher->party_id)
                    ->orderBy('id', 'desc')
                    ->first();

                $customer        = Customer::find($voucher->party_id);
                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($customer->opening_balance ?? 0);

                CustomerLedger::create([
                    'customer_id'      => $voucher->party_id,
                    'admin_or_user_id' => $userId,
                    'previous_balance' => $previousBalance,
                    'opening_balance'  => $customer->opening_balance ?? 0,
                    'total_debit'      => $totalAmount, // Reversed
                    'total_credit'     => 0,
                    'closing_balance'  => $previousBalance + $totalAmount,
                    'transaction_type' => 'reversal',
                    'reference_id'     => $evid,
                    'description'      => "Reversal of Expense Voucher #{$evid}",
                ]);
            } elseif (is_numeric($voucher->type) || is_numeric($voucher->party_id)) {
                $accountId = is_numeric($voucher->party_id) ? $voucher->party_id : $voucher->type;
                $account   = Account::find($accountId);
                if ($account) {
                    $account->opening_balance += $totalAmount; // Cash restored
                    $account->save();

                    self::postLedgerEntry(
                        $account->id,
                        'manual',
                        $evid,
                        $voucher->id,
                        $date,
                        "Reversal of Expense Source: #{$evid}",
                        $totalAmount,
                        0,
                        $userId
                    );
                }
            }

            // 3. Mark Voucher Status
            if ($isVoid) {
                $voucher->status = 'voided';
                $voucher->save();
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Expense Voucher Reversal Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Post ledger entries for an Expense Voucher.
     */
    public static function applyExpenseVoucher(ExpenseVoucher $voucher, array $data, $userId): bool
    {
        $totalAmount = (float)($data['total_amount'] ?? $voucher->total_amount);
        $evid        = $voucher->evid;
        $branchId    = (int)($data['branch_id'] ?? $voucher->branch_id ?? (auth()->user()->branch_id ?? 0));
        $entryDate   = $data['entry_date'] ?? $voucher->entry_date ?? now()->toDateString();
        $partyType   = $data['type'] ?? $data['vendor_type'] ?? $voucher->type;
        $partyId     = $data['party_id'] ?? $data['vendor_id'] ?? $voucher->party_id;
        $remarks     = $data['remarks'] ?? $voucher->remarks ?? '';

        // 1. EXPENSE ACCOUNTS POSTING (DEBIT - Expense rows increased)
        $rowAccountIds = is_array($data['row_account_id'] ?? null) 
            ? $data['row_account_id'] 
            : self::safeDecodeArray($voucher->row_account_id);
        $amounts = is_array($data['amount'] ?? null) 
            ? $data['amount'] 
            : self::safeDecodeArray($voucher->amount);

        foreach ($rowAccountIds as $index => $accId) {
            $rowAmount = isset($amounts[$index]) ? (float)$amounts[$index] : 0;
            if ($rowAmount > 0 && $accId) {
                $rowAccount = Account::find($accId);
                if ($rowAccount) {
                    $rowAccount->opening_balance += $rowAmount;
                    $rowAccount->save();

                    self::postLedgerEntry(
                        $accId,
                        'expense',
                        $evid,
                        $voucher->id,
                        $entryDate,
                        $remarks ?: "Expense Voucher #{$evid}",
                        $rowAmount,
                        0,
                        $userId
                    );
                }
            }
        }

        // 2. PAYMENT SOURCE / PARTY SIDE POSTING (CREDIT - Funds decreased)
        if ($partyType === 'vendor') {
            $lastLedger = VendorLedger::where('vendor_id', $partyId)
                ->where('branch_id', $branchId)
                ->orderBy('id', 'desc')
                ->first();

            $vendor          = Vendor::find($partyId);
            $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($vendor->opening_balance ?? 0);

            VendorLedger::create([
                'vendor_id'        => $partyId,
                'branch_id'        => $branchId,
                'admin_or_user_id' => $userId,
                'transaction_date' => $entryDate,
                'description'      => "Expense Voucher #{$evid}" . ($remarks ? " - {$remarks}" : ""),
                'opening_balance'  => $previousBalance,
                'previous_balance' => $previousBalance,
                'debit_amount'     => 0,
                'credit_amount'    => $totalAmount,
                'closing_balance'  => $previousBalance + $totalAmount,
                'transaction_type' => 'expense',
                'reference_id'     => $evid,
            ]);
        } elseif ($partyType === 'customer' || $partyType === 'walkin') {
            $lastLedger = CustomerLedger::where('customer_id', $partyId)
                ->orderBy('id', 'desc')
                ->first();

            $customer        = Customer::find($partyId);
            $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($customer->opening_balance ?? 0);

            CustomerLedger::create([
                'customer_id'      => $partyId,
                'admin_or_user_id' => $userId,
                'previous_balance' => $previousBalance,
                'opening_balance'  => $previousBalance,
                'total_debit'      => 0,
                'total_credit'     => $totalAmount,
                'closing_balance'  => $previousBalance + $totalAmount,
                'transaction_type' => 'expense',
                'reference_id'     => $evid,
                'description'      => "Expense Voucher #{$evid}" . ($remarks ? " - {$remarks}" : ""),
            ]);
        } else {
            // GL Account (e.g. Cash in Hand / Bank Account)
            $accountId = is_numeric($partyId) ? $partyId : $partyType;
            $account   = Account::find($accountId);
            if ($account) {
                $account->opening_balance -= $totalAmount;
                $account->save();

                self::postLedgerEntry(
                    $account->id,
                    'expense',
                    $evid,
                    $voucher->id,
                    $entryDate,
                    "Expense Payment Source: " . ($remarks ?: 'N/A'),
                    0,
                    $totalAmount,
                    $userId
                );
            }
        }

        return true;
    }


    /* =========================================================================
     * 4. JOURNAL VOUCHER (JV)
     * ========================================================================= */

    /**
     * Reverse a Journal Voucher completely.
     * Original: Debit side debited (payable reduced / asset increased), Credit side credited (receivable reduced).
     * Reversal: Debit side credited (payable restored), Credit side debited (receivable restored).
     */
    public static function reverseJournalVoucher(JournalVoucher $voucher, $userId, bool $isVoid = true): bool
    {
        DB::beginTransaction();
        try {
            $amount   = (float) $voucher->amount;
            $jvid     = $voucher->jvid;
            $date     = now()->toDateString();
            $branchId = $voucher->branch_id ?? (auth()->check() ? auth()->user()->branch_id : 0);

            // 1. REVERSE DEBIT SIDE (Original DEBITED -> Reversal CREDITS)
            if ($voucher->debit_party_type === 'vendor') {
                $lastLedger = VendorLedger::where('vendor_id', $voucher->debit_party_id)
                    ->where('branch_id', $branchId)
                    ->orderBy('id', 'desc')
                    ->first();

                $vendor          = Vendor::find($voucher->debit_party_id);
                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($vendor->opening_balance ?? 0);

                VendorLedger::create([
                    'vendor_id'        => $voucher->debit_party_id,
                    'branch_id'        => $branchId,
                    'admin_or_user_id' => $userId,
                    'transaction_date' => $date,
                    'description'      => "Reversal of Journal Voucher #{$jvid} — Debit Side",
                    'opening_balance'  => $vendor->opening_balance ?? 0,
                    'previous_balance' => $previousBalance,
                    'debit_amount'     => 0,
                    'credit_amount'    => $amount, // Credited back
                    'closing_balance'  => $previousBalance + $amount,
                    'transaction_type' => 'reversal',
                    'reference_id'     => $jvid,
                ]);
            } elseif ($voucher->debit_party_type === 'customer') {
                $lastLedger = CustomerLedger::where('customer_id', $voucher->debit_party_id)
                    ->orderBy('id', 'desc')
                    ->first();

                $customer        = Customer::find($voucher->debit_party_id);
                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($customer->opening_balance ?? 0);

                CustomerLedger::create([
                    'customer_id'      => $voucher->debit_party_id,
                    'admin_or_user_id' => $userId,
                    'previous_balance' => $previousBalance,
                    'opening_balance'  => $customer->opening_balance ?? 0,
                    'total_debit'      => 0,
                    'total_credit'     => $amount, // Credited back
                    'closing_balance'  => $previousBalance - $amount,
                    'transaction_type' => 'reversal',
                    'reference_id'     => $jvid,
                    'description'      => "Reversal of Journal Voucher #{$jvid} — Debit Side",
                ]);
            } elseif ($voucher->debit_party_type === 'account' || is_numeric($voucher->debit_party_type)) {
                $accId   = $voucher->debit_party_id;
                $account = Account::find($accId);
                if ($account) {
                    $account->opening_balance -= $amount;
                    $account->save();

                    self::postLedgerEntry(
                        $account->id,
                        'manual',
                        $jvid,
                        $voucher->id,
                        $date,
                        "Reversal of Journal Voucher #{$jvid} — Debit Side",
                        0,
                        $amount,
                        $userId
                    );
                }
            }

            // 2. REVERSE CREDIT SIDE (Original CREDITED -> Reversal DEBITS)
            if ($voucher->credit_party_type === 'customer') {
                $lastLedger = CustomerLedger::where('customer_id', $voucher->credit_party_id)
                    ->orderBy('id', 'desc')
                    ->first();

                $customer        = Customer::find($voucher->credit_party_id);
                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($customer->opening_balance ?? 0);

                CustomerLedger::create([
                    'customer_id'      => $voucher->credit_party_id,
                    'admin_or_user_id' => $userId,
                    'previous_balance' => $previousBalance,
                    'opening_balance'  => $customer->opening_balance ?? 0,
                    'total_debit'      => $amount, // Debited back (receivable restored)
                    'total_credit'     => 0,
                    'closing_balance'  => $previousBalance + $amount,
                    'transaction_type' => 'reversal',
                    'reference_id'     => $jvid,
                    'description'      => "Reversal of Journal Voucher #{$jvid} — Credit Side",
                ]);
            } elseif ($voucher->credit_party_type === 'vendor') {
                $lastLedger = VendorLedger::where('vendor_id', $voucher->credit_party_id)
                    ->where('branch_id', $branchId)
                    ->orderBy('id', 'desc')
                    ->first();

                $vendor          = Vendor::find($voucher->credit_party_id);
                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($vendor->opening_balance ?? 0);

                VendorLedger::create([
                    'vendor_id'        => $voucher->credit_party_id,
                    'branch_id'        => $branchId,
                    'admin_or_user_id' => $userId,
                    'transaction_date' => $date,
                    'description'      => "Reversal of Journal Voucher #{$jvid} — Credit Side",
                    'opening_balance'  => $vendor->opening_balance ?? 0,
                    'previous_balance' => $previousBalance,
                    'debit_amount'     => $amount, // Debited back
                    'credit_amount'    => 0,
                    'closing_balance'  => $previousBalance - $amount,
                    'transaction_type' => 'reversal',
                    'reference_id'     => $jvid,
                ]);
            } elseif ($voucher->credit_party_type === 'account' || is_numeric($voucher->credit_party_type)) {
                $accId   = $voucher->credit_party_id;
                $account = Account::find($accId);
                if ($account) {
                    $account->opening_balance += $amount;
                    $account->save();

                    self::postLedgerEntry(
                        $account->id,
                        'manual',
                        $jvid,
                        $voucher->id,
                        $date,
                        "Reversal of Journal Voucher #{$jvid} — Credit Side",
                        $amount,
                        0,
                        $userId
                    );
                }
            }

            // 3. Mark Status
            if ($isVoid) {
                $voucher->status = 'voided';
                $voucher->save();
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("JV Reversal Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Post ledger entries for a Journal Voucher.
     */
    public static function applyJournalVoucher(JournalVoucher $voucher, array $data, $userId): bool
    {
        $amount          = (float)($data['amount'] ?? $voucher->amount);
        $jvid            = $voucher->jvid;
        $voucherDate     = $data['voucher_date'] ?? $voucher->voucher_date ?? now()->toDateString();
        $branchId        = (int)($data['branch_id'] ?? $voucher->branch_id ?? 0);
        $remarks         = $data['remarks'] ?? $voucher->remarks ?? '';
        $debitPartyType  = $data['debit_party_type'] ?? $voucher->debit_party_type;
        $debitPartyId    = $data['debit_party_id'] ?? $voucher->debit_party_id;
        $creditPartyType = $data['credit_party_type'] ?? $voucher->credit_party_type;
        $creditPartyId   = $data['credit_party_id'] ?? $voucher->credit_party_id;

        // 1. DEBIT SIDE POSTING
        if ($debitPartyType === 'vendor') {
            $lastLedger = VendorLedger::where('vendor_id', $debitPartyId)
                ->where('branch_id', $branchId)
                ->orderBy('id', 'desc')
                ->first();

            $vendor          = Vendor::find($debitPartyId);
            $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($vendor->opening_balance ?? 0);

            VendorLedger::create([
                'vendor_id'        => $debitPartyId,
                'branch_id'        => $branchId,
                'admin_or_user_id' => $userId,
                'transaction_date' => $voucherDate,
                'description'      => "Journal Voucher #{$jvid} — Debit" . ($remarks ? " - {$remarks}" : ""),
                'opening_balance'  => $vendor->opening_balance ?? 0,
                'previous_balance' => $previousBalance,
                'debit_amount'     => $amount,
                'credit_amount'    => 0,
                'closing_balance'  => $previousBalance - $amount,
                'transaction_type' => 'journal',
                'reference_id'     => $jvid,
            ]);
        } elseif ($debitPartyType === 'customer') {
            $lastLedger = CustomerLedger::where('customer_id', $debitPartyId)
                ->orderBy('id', 'desc')
                ->first();

            $customer        = Customer::find($debitPartyId);
            $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($customer->opening_balance ?? 0);

            CustomerLedger::create([
                'customer_id'      => $debitPartyId,
                'admin_or_user_id' => $userId,
                'previous_balance' => $previousBalance,
                'opening_balance'  => $customer->opening_balance ?? 0,
                'total_debit'      => $amount,
                'total_credit'     => 0,
                'closing_balance'  => $previousBalance + $amount,
                'transaction_type' => 'journal',
                'reference_id'     => $jvid,
                'description'      => "Journal Voucher #{$jvid} — Debit" . ($remarks ? " - {$remarks}" : ""),
            ]);
        } elseif ($debitPartyType === 'account' || is_numeric($debitPartyType)) {
            $accId   = $debitPartyId;
            $account = Account::find($accId);
            if ($account) {
                $account->opening_balance += $amount;
                $account->save();

                self::postLedgerEntry(
                    $account->id,
                    'manual',
                    $jvid,
                    $voucher->id,
                    $voucherDate,
                    "Journal Voucher #{$jvid} — Debit: " . ($remarks ?: 'N/A'),
                    $amount,
                    0,
                    $userId
                );
            }
        }

        // 2. CREDIT SIDE POSTING
        if ($creditPartyType === 'customer') {
            $lastLedger = CustomerLedger::where('customer_id', $creditPartyId)
                ->orderBy('id', 'desc')
                ->first();

            $customer        = Customer::find($creditPartyId);
            $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($customer->opening_balance ?? 0);

            CustomerLedger::create([
                'customer_id'      => $creditPartyId,
                'admin_or_user_id' => $userId,
                'previous_balance' => $previousBalance,
                'opening_balance'  => $customer->opening_balance ?? 0,
                'total_debit'      => 0,
                'total_credit'     => $amount,
                'closing_balance'  => $previousBalance - $amount,
                'transaction_type' => 'journal',
                'reference_id'     => $jvid,
                'description'      => "Journal Voucher #{$jvid} — Credit" . ($remarks ? " - {$remarks}" : ""),
            ]);
        } elseif ($creditPartyType === 'vendor') {
            $lastLedger = VendorLedger::where('vendor_id', $creditPartyId)
                ->where('branch_id', $branchId)
                ->orderBy('id', 'desc')
                ->first();

            $vendor          = Vendor::find($creditPartyId);
            $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($vendor->opening_balance ?? 0);

            VendorLedger::create([
                'vendor_id'        => $creditPartyId,
                'branch_id'        => $branchId,
                'admin_or_user_id' => $userId,
                'transaction_date' => $voucherDate,
                'description'      => "Journal Voucher #{$jvid} — Credit" . ($remarks ? " - {$remarks}" : ""),
                'opening_balance'  => $vendor->opening_balance ?? 0,
                'previous_balance' => $previousBalance,
                'debit_amount'     => 0,
                'credit_amount'    => $amount,
                'closing_balance'  => $previousBalance + $amount,
                'transaction_type' => 'journal',
                'reference_id'     => $jvid,
            ]);
        } elseif ($creditPartyType === 'account' || is_numeric($creditPartyType)) {
            $accId   = $creditPartyId;
            $account = Account::find($accId);
            if ($account) {
                $account->opening_balance -= $amount;
                $account->save();

                self::postLedgerEntry(
                    $account->id,
                    'manual',
                    $jvid,
                    $voucher->id,
                    $voucherDate,
                    "Journal Voucher #{$jvid} — Credit: " . ($remarks ?: 'N/A'),
                    0,
                    $amount,
                    $userId
                );
            }
        }

        return true;
    }


    /* =========================================================================
     * 5. GENERAL LEDGER HELPER (AccountLedgerEntry)
     * ========================================================================= */

    /**
     * Post an entry into account_ledger_entries and maintain cumulative running balance.
     */
    public static function postLedgerEntry(
        int    $accountId,
        string $voucherType,
        string $voucherNo,
        ?int   $voucherId,
        string $date,
        ?string $description,
        float  $debit,
        float  $credit,
        $userId = null
    ): void {
        $account = Account::find($accountId);
        if (!$account) return;

        // Get last running balance for this account
        $lastEntry = AccountLedgerEntry::where('account_id', $accountId)
            ->orderByDesc('id')
            ->first();

        if ($lastEntry) {
            $previousBalance = (float)$lastEntry->running_balance;
        } else {
            // Base balance
            $openingBalance = (float)($account->opening_balance ?? 0);

            if ($openingBalance != 0) {
                $obEntryNo = AccountLedgerEntry::generateEntryNo($accountId, 'opening_balance');
                AccountLedgerEntry::create([
                    'account_id'        => $accountId,
                    'branch_id'         => $account->branch_id ?? 0,
                    'voucher_type'      => 'opening_balance',
                    'voucher_no'        => null,
                    'voucher_id'        => null,
                    'entry_no'          => $obEntryNo,
                    'transaction_date'  => now()->toDateString(),
                    'description'       => 'Opening Balance',
                    'debit'             => $openingBalance >= 0 ? $openingBalance : 0,
                    'credit'            => $openingBalance < 0 ? abs($openingBalance) : 0,
                    'running_balance'   => $openingBalance,
                    'created_by'        => $userId ?? auth()->id(),
                ]);
            }

            $previousBalance = $openingBalance;
        }

        // Account normal balance type calculation
        if (trim(strtolower($account->type ?? 'debit')) === 'credit') {
            $newBalance = $previousBalance + $credit - $debit;
        } else {
            $newBalance = $previousBalance + $debit - $credit;
        }

        $validVoucherTypes = ['opening_balance', 'receipt', 'payment', 'expense', 'manual'];
        $cleanVoucherType = in_array($voucherType, $validVoucherTypes) ? $voucherType : 'manual';

        $entryNo = AccountLedgerEntry::generateEntryNo($accountId, $cleanVoucherType);

        AccountLedgerEntry::create([
            'account_id'        => $accountId,
            'branch_id'         => $account->branch_id ?? 0,
            'voucher_type'      => $cleanVoucherType,
            'voucher_no'        => $voucherNo,
            'voucher_id'        => $voucherId,
            'entry_no'          => $entryNo,
            'transaction_date'  => $date,
            'description'       => $description ?? ucfirst($cleanVoucherType) . ' Voucher #' . $voucherNo,
            'debit'             => $debit,
            'credit'            => $credit,
            'running_balance'   => $newBalance,
            'created_by'        => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Safely decode database column into an array.
     * Handles:
     * - Already an array -> returns array_values
     * - JSON encoded array string -> returns decoded array
     * - JSON encoded scalar (e.g. "4", "500") -> returns [4], [500]
     * - Non-JSON string (e.g. "Cash/Bank", "INV-001") -> returns ["Cash/Bank"], ["INV-001"]
     * - Numeric/scalar value -> returns [value]
     * - Null / empty -> returns []
     */
    public static function safeDecodeArray($value): array
    {
        if (is_array($value)) {
            return array_values($value);
        }
        if ($value === null || $value === '') {
            return [];
        }
        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '' || $trimmed === 'null') {
                return [];
            }
            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                if (is_array($decoded)) {
                    return array_values($decoded);
                }
                if ($decoded !== null && $decoded !== '') {
                    return [$decoded];
                }
                return [];
            }
            // If not valid JSON, treat as raw single value string (e.g. "Cash/Bank" or "INV-0005")
            return [$trimmed];
        }
        // Numbers, etc.
        return [$value];
    }
}

