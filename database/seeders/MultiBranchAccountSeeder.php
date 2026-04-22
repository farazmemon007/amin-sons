<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountHead;
use App\Models\Branch;
use App\Services\AccountCodeService;
use Illuminate\Database\Seeder;

class MultiBranchAccountSeeder extends Seeder
{
    /**
     * Run the multi-branch account seeder
     * Creates accounts for multiple branches with independent numbering
     * 
     * Structure:
     * - Account Heads: Bank, Cash (shared across all branches)
     * - Each Branch gets its own accounts under each head
     * - Account codes start from 001 per branch per head
     */
    public function run(): void
    {
        $service = new AccountCodeService();

        // Get or create account heads (shared globally)
        $heads = [
            'Bank' => 'Bank Accounts',
            'Cash' => 'Cash Accounts',
            'Receivable' => 'Accounts Receivable',
            'Payable' => 'Accounts Payable',
        ];

        $headModels = [];
        foreach ($heads as $name => $description) {
            $head = AccountHead::updateOrCreate(
                ['name' => $name],
                ['name' => $name]
            );
            $headModels[$name] = $head;
            echo "✓ Account Head: {$name}\n";
        }

        // Get all active branches
        $branches = Branch::where('status', 'active')
            ->orWhere('status', 1)
            ->orWhere('status', true)
            ->get();

        if ($branches->isEmpty()) {
            echo "❌ No active branches found!\n";
            return;
        }

        // Create accounts for each branch
        foreach ($branches as $branch) {
            echo "\n📍 Branch: {$branch->name}\n";
            echo str_repeat("-", 60) . "\n";

            // Branch 1 specific setup
            if ($branch->id == 1) {
                // Bank accounts for Branch 1
                $this->createAccount($branch, $headModels['Bank'], 'Main Bank Account - HBL', 'Debit', 150000);
                $this->createAccount($branch, $headModels['Bank'], 'Savings Account - UBL', 'Debit', 75000);
                $this->createAccount($branch, $headModels['Bank'], 'Business Account - NIB', 'Debit', 100000);

                // Cash accounts for Branch 1
                $this->createAccount($branch, $headModels['Cash'], 'Cash in Hand - Office', 'Debit', 25000);
                $this->createAccount($branch, $headModels['Cash'], 'Cash in Petty', 'Debit', 5000);

                // Receivable for Branch 1
                $this->createAccount($branch, $headModels['Receivable'], 'Customer A - Outstanding', 'Debit', 50000);
                $this->createAccount($branch, $headModels['Receivable'], 'Customer B - Outstanding', 'Debit', 35000);

                // Payable for Branch 1
                $this->createAccount($branch, $headModels['Payable'], 'Vendor X - Payable', 'Credit', 80000);
                $this->createAccount($branch, $headModels['Payable'], 'Vendor Y - Payable', 'Credit', 45000);
            }
            // Branch 2 specific setup
            elseif ($branch->id == 2) {
                // Bank accounts for Branch 2 (same heads, different codes starting from 001)
                $this->createAccount($branch, $headModels['Bank'], 'Main Bank Account - HBL', 'Debit', 120000);
                $this->createAccount($branch, $headModels['Bank'], 'Savings Account - UBL', 'Debit', 50000);

                // Cash accounts for Branch 2
                $this->createAccount($branch, $headModels['Cash'], 'Cash in Hand - Office', 'Debit', 20000);

                // Receivable for Branch 2
                $this->createAccount($branch, $headModels['Receivable'], 'Customer C - Outstanding', 'Debit', 30000);

                // Payable for Branch 2
                $this->createAccount($branch, $headModels['Payable'], 'Vendor Z - Payable', 'Credit', 60000);
            }
            // Other branches
            else {
                // Minimal accounts for other branches
                $this->createAccount($branch, $headModels['Bank'], 'Main Bank Account', 'Debit', 100000);
                $this->createAccount($branch, $headModels['Cash'], 'Cash in Hand', 'Debit', 15000);
            }
        }

        echo "\n" . str_repeat("=", 60) . "\n";
        echo "✅ Multi-branch account seeding complete!\n";
        echo str_repeat("=", 60) . "\n";

        // Display summary
        $this->displaySummary($branches, $service);
    }

    /**
     * Helper: Create an account with auto-generated code
     */
    private function createAccount($branch, $head, $title, $type, $balance = 0)
    {
        $service = new AccountCodeService();

        // Auto-generate account code
        $code = $service->generateAccountCode($branch, $head);

        // Create account
        Account::create([
            'branch_id' => $branch->id,
            'head_id' => $head->id,
            'account_code' => $code,
            'title' => $title,
            'type' => $type,
            'opening_balance' => $balance,
            'status' => 'active',
        ]);

        echo "  ✓ " . str_pad($code, 10) . " | " . str_pad($head->name, 15) . " | " . str_pad($title, 30) . " | PKR " . number_format($balance, 0) . "\n";
    }

    /**
     * Display summary of all branches and their accounts
     */
    private function displaySummary($branches, $service)
    {
        echo "\n📊 MULTI-BRANCH ACCOUNT SUMMARY\n";
        echo str_repeat("=", 60) . "\n";

        foreach ($branches as $branch) {
            $totalAccounts = $service->getTotalAccountsForBranch($branch->id);
            $balanceSummary = $service->getBranchBalanceSummary($branch->id);

            echo "\n{$branch->name}\n";
            echo str_repeat("-", 40) . "\n";
            echo "  Total Accounts: {$totalAccounts}\n";
            echo "  Total Debit Balance: PKR " . number_format($balanceSummary['total_debit'], 0) . "\n";
            echo "  Total Credit Balance: PKR " . number_format($balanceSummary['total_credit'], 0) . "\n";
            echo "  Net Balance: PKR " . number_format($balanceSummary['balance'], 0) . "\n";

            // Show accounts by head
            echo "\n  Accounts by Head:\n";
            $accountsByHead = $service->getAccountsByBranchAndHead($branch->id);
            foreach ($accountsByHead as $headName => $accounts) {
                echo "    • {$headName}: " . count($accounts) . " account(s)\n";
                foreach ($accounts as $account) {
                    echo "      - {$account['account_code']} | {$account['title']}\n";
                }
            }
        }

        echo "\n" . str_repeat("=", 60) . "\n";
    }
}
