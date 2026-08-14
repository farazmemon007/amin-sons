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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VoucherService
{
    /**
     * Reverse a Payment Voucher safely
     */
    public static function reversePaymentVoucher(PaymentVoucher $voucher, $userId)
    {
        DB::beginTransaction();
        try {
            $totalAmount = (float)$voucher->total_amount;
            $pvid = $voucher->pvid;

            // 1. REVERSE ROW ACCOUNTS (Bank/Cash)
            // Payment originally decreased row_account balances, so we must INCREASE them back.
            $accounts = json_decode($voucher->row_account_id, true) ?? [];
            $amounts = json_decode($voucher->amount, true) ?? [];

            foreach ($accounts as $index => $accId) {
                $rowAmount = isset($amounts[$index]) ? (float)$amounts[$index] : 0;
                if ($rowAmount > 0 && $accId) {
                    $rowAccount = Account::find($accId);
                    if ($rowAccount) {
                        $rowAccount->opening_balance += $rowAmount;
                        $rowAccount->save();

                        // Reverse the ledger entry. Original was CREDIT, reversal is DEBIT.
                        self::postLedgerEntry($accId, 'reversal', $pvid, $voucher->id, now()->toDateString(), "Reversal of Payment: #$pvid", $rowAmount, 0, $userId);
                    }
                }
            }

            // 2. REVERSE PARTY SIDE
            if ($voucher->type === 'vendor') {
                $branchId = $voucher->branch_id ?? 0;
                $lastLedger = VendorLedger::where('vendor_id', $voucher->party_id)
                    ->orderBy('id', 'desc')
                    ->first();

                $vendor = Vendor::find($voucher->party_id);
                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($vendor->opening_balance ?? 0);

                // Original DEBITED vendor, so Reversal must CREDIT vendor.
                VendorLedger::create([
                    'vendor_id'        => $voucher->party_id,
                    'branch_id'        => $branchId,
                    'admin_or_user_id' => $userId,
                    'transaction_date' => now()->toDateString(),
                    'description'      => "Reversal of Payment Voucher #$pvid",
                    'opening_balance'  => $vendor->opening_balance ?? 0,
                    'previous_balance' => $previousBalance,
                    'debit_amount'     => 0,
                    'credit_amount'    => $totalAmount, // Reversed
                    'closing_balance'  => $previousBalance + $totalAmount, // Payable increases
                    'transaction_type' => 'reversal',
                    'reference_id'     => $pvid,
                ]);
            } elseif ($voucher->type === 'customer' || $voucher->type === 'walkin') {
                $lastLedger = CustomerLedger::where('customer_id', $voucher->party_id)
                    ->orderBy('id', 'desc')
                    ->first();

                $customer = Customer::find($voucher->party_id);
                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($customer->opening_balance ?? 0);

                // Original increased balance, reversal decreases.
                CustomerLedger::create([
                    'customer_id'      => $voucher->party_id,
                    'admin_or_user_id' => $userId,
                    'previous_balance' => $previousBalance,
                    'opening_balance'  => $customer->opening_balance ?? 0,
                    'total_debit'      => 0,
                    'total_credit'     => $totalAmount, // Assuming credit reduces receivable
                    'closing_balance'  => $previousBalance - $totalAmount,
                    'transaction_type' => 'reversal',
                    'reference_id'     => $pvid,
                    'description'      => "Reversal of Payment Voucher #$pvid",
                ]);
            } elseif (is_numeric($voucher->type)) {
                $account = Account::find($voucher->party_id);
                if ($account) {
                    // Original increased balance, so decrease.
                    $account->opening_balance -= $totalAmount;
                    $account->save();
                    self::postLedgerEntry($account->id, 'reversal', $pvid, $voucher->id, now()->toDateString(), "Reversal of Payment Party Side: #$pvid", 0, $totalAmount, $userId);
                }
            }

            // 3. Mark Voucher as Void
            $voucher->processed = 0;
            $voucher->status = 'voided';
            $voucher->save();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Payment Voucher Reversal Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reverse a Receipt Voucher safely
     */
    public static function reverseReceiptVoucher(ReceiptsVoucher $voucher, $userId)
    {
        DB::beginTransaction();
        try {
            $totalAmount = (float)$voucher->total_amount;
            $rvid = $voucher->rvid;

            // 1. REVERSE PARTY SIDE
            // Original Receipt credited the party, so we must DEBIT them to reverse.
            if ($voucher->type === 'customer' || $voucher->type === 'walkin') {
                $lastLedger = CustomerLedger::where('customer_id', $voucher->party_id)
                    ->orderBy('id', 'desc')
                    ->first();

                $customer = Customer::find($voucher->party_id);
                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($customer->opening_balance ?? 0);

                CustomerLedger::create([
                    'customer_id'      => $voucher->party_id,
                    'admin_or_user_id' => $userId,
                    'previous_balance' => $previousBalance,
                    'opening_balance'  => $customer->opening_balance ?? 0,
                    'total_debit'      => $totalAmount, // Reversed!
                    'total_credit'     => 0,
                    'closing_balance'  => $previousBalance + $totalAmount,
                    'transaction_type' => 'reversal',
                    'reference_id'     => $rvid,
                    'description'      => "Reversal of Receipt Voucher #$rvid",
                ]);
            } elseif ($voucher->type === 'vendor') {
                // If branch_id doesn't exist on voucher directly, we might need to query it or just pass null.
                $branchId = $voucher->branch_id ?? 0;
                $lastLedger = VendorLedger::where('vendor_id', $voucher->party_id)
                    ->orderBy('id', 'desc')
                    ->first();

                $vendor = Vendor::find($voucher->party_id);
                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($vendor->opening_balance ?? 0);

                VendorLedger::create([
                    'vendor_id'        => $voucher->party_id,
                    'branch_id'        => $branchId,
                    'admin_or_user_id' => $userId,
                    'transaction_date' => now()->toDateString(),
                    'description'      => "Reversal of Receipt Voucher #$rvid",
                    'opening_balance'  => $vendor->opening_balance ?? 0,
                    'previous_balance' => $previousBalance,
                    'debit_amount'     => $totalAmount, // Reversed!
                    'credit_amount'    => 0,
                    'closing_balance'  => $previousBalance - $totalAmount,
                    'transaction_type' => 'reversal',
                    'reference_id'     => $rvid,
                ]);
            } elseif (is_numeric($voucher->type)) {
                // Account Head
                $account = Account::find($voucher->party_id);
                if ($account) {
                    $account->opening_balance += $totalAmount;
                    $account->save();

                    self::postLedgerEntry($account->id, 'reversal', $rvid, $voucher->id, now()->toDateString(), "Reversal of Receipt Party Side: #$rvid", $totalAmount, 0, $userId);
                }
            }

            // 2. REVERSE ACCOUNT SIDE
            // Original Receipt debited the bank/cash accounts, so we must CREDIT them.
            $accounts = json_decode($voucher->row_account_id, true) ?? [];
            $amounts = json_decode($voucher->amount, true) ?? [];

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

                    self::postLedgerEntry($accId, 'reversal', $rvid, $voucher->id, now()->toDateString(), "Reversal of Receipt from Party: #$rvid", 0, $rowAmount, $userId);
                }
            }

            // 3. Mark Voucher as Void
            $voucher->processed = 0; // assuming boolean or tinyint
            $voucher->status = 'voided';
            $voucher->save();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Receipt Voucher Reversal Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Helper for Account Ledger Posting
     */
    public static function postLedgerEntry($accountId, $type, $voucherNo, $voucherId, $date, $desc, $debit, $credit, $userId)
    {
        $account = Account::find($accountId);
        if (!$account) return;

        $lastEntry = AccountLedgerEntry::where('account_id', $accountId)->latest('id')->first();
        $runningBalance = $lastEntry ? (float)$lastEntry->running_balance : (float)$account->opening_balance;

        if (trim(strtolower($account->type)) === 'debit') {
            $newBalance = $runningBalance + $debit - $credit;
        } else {
            $newBalance = $runningBalance + $credit - $debit;
        }

        // Sometimes voucher_type Enum doesn't accept 'reversal'. Let's use 'manual' if 'reversal' fails.
        // But let's assume it accepts manual
        AccountLedgerEntry::create([
            'account_id'       => $accountId,
            'branch_id'        => $account->branch_id ?? 0,
            'voucher_type'     => 'manual',
            'voucher_no'       => $voucherNo,
            'voucher_id'       => $voucherId,
            'transaction_date' => $date,
            'description'      => $desc,
            'debit'            => $debit,
            'credit'           => $credit,
            'running_balance'  => $newBalance,
            'created_by'       => $userId
        ]);
    }
}
