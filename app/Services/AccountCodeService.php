<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountHead;
use App\Models\Branch;

/**
 * AccountCodeService - Generates sequential account codes per branch per head
 * ERP Standard: Each branch has independent account numbering starting from 1
 * 
 * Example:
 * - Branch 1 (amin$sons):
 *   - Bank Head: BANK-001, BANK-002
 *   - Cash Head: CASH-001, CASH-002
 * 
 * - Branch 2 (waqas electronics):
 *   - Bank Head: BANK-001, BANK-002
 *   - Cash Head: CASH-001
 */
class AccountCodeService
{
    /**
     * Generate next sequential account code for a branch and head
     * Format: {HEAD_PREFIX}-{SEQUENTIAL_NUMBER}
     * 
     * @param Branch $branch
     * @param AccountHead $head
     * @return string (e.g., "BANK-001")
     */
    public function generateAccountCode(Branch $branch, AccountHead $head): string
    {
        // Get head prefix (first 4 letters uppercase)
        $prefix = strtoupper(substr($head->name, 0, 4));

        // Count existing accounts for this branch AND this head
        $nextNumber = Account::where('branch_id', $branch->id)
            ->where('head_id', $head->id)
            ->count() + 1;

        // Format as {PREFIX}-{NUMBER}
        return $prefix . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get next sequence number for a branch and head
     * 
     * @param int $branchId
     * @param int $headId
     * @return int
     */
    public function getNextSequenceNumber(int $branchId, int $headId): int
    {
        return Account::where('branch_id', $branchId)
            ->where('head_id', $headId)
            ->count() + 1;
    }

    /**
     * Get all accounts for a branch grouped by head
     * 
     * @param int $branchId
     * @return array [headName => [Account, Account, ...], ...]
     */
    public function getAccountsByBranchAndHead(int $branchId): array
    {
        $accounts = Account::where('branch_id', $branchId)
            ->with('head')
            ->orderBy('head_id')
            ->get();

        return $accounts->groupBy(function ($account) {
            return $account->head->name;
        })->toArray();
    }

    /**
     * Count total accounts for a branch
     * 
     * @param int $branchId
     * @return int
     */
    public function getTotalAccountsForBranch(int $branchId): int
    {
        return Account::where('branch_id', $branchId)->count();
    }

    /**
     * Count total accounts for a branch under specific head
     * 
     * @param int $branchId
     * @param int $headId
     * @return int
     */
    public function getAccountsCountForBranchHead(int $branchId, int $headId): int
    {
        return Account::where('branch_id', $branchId)
            ->where('head_id', $headId)
            ->count();
    }

    /**
     * Get balance summary for a branch
     * 
     * @param int $branchId
     * @return array [total_debit, total_credit, balance]
     */
    public function getBranchBalanceSummary(int $branchId): array
    {
        $accounts = Account::where('branch_id', $branchId)->get();

        $totalDebit = $accounts->where('type', 'Debit')->sum('opening_balance');
        $totalCredit = $accounts->where('type', 'Credit')->sum('opening_balance');

        return [
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'balance' => $totalDebit - $totalCredit,
        ];
    }
}
