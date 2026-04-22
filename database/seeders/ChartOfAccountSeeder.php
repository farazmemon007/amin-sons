<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChartOfAccountSeeder extends Seeder
{
    /**
     * ✅ ERP Chart of Accounts Seeder
     * 
     * Global Account Heads + Branch-Specific Accounts
     * Bank names are different for each branch for easy identification
     */
    public function run(): void
    {
        // Get all active branches (status can be 1, 'active', true, etc.)
        $branches = Branch::where('status', 'active')
            ->orWhere('status', 1)
            ->orWhere('status', true)
            ->get();

        if ($branches->isEmpty()) {
            // If still empty, get all branches
            $branches = Branch::all();
        }

        if ($branches->isEmpty()) {
            echo "❌ No branches found. Please seed branches first.\n";
            return;
        }

        // ==============================
        // 1️⃣ CREATE GLOBAL ACCOUNT HEADS (Shared across all branches)
        // ==============================
        
        $heads = [
            'bank'     => 'Bank',           // For bank accounts
            'cash'     => 'Cash',           // For cash accounts
            'expense'  => 'Expense',        // For expense accounts
            'asset'    => 'Asset',          // For assets
            'liability' => 'Liability',     // For liabilities
            'revenue'  => 'Revenue',        // For revenue/income
            'equity'   => 'Equity',         // For equity
        ];

        $headIds = [];
        foreach ($heads as $slug => $name) {
            $headIds[$slug] = DB::table('account_heads')->insertOrIgnore([
                'name'       => $name,
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // If insertOrIgnore returns 0, it means the record exists, so retrieve it
            if ($headIds[$slug] === 0) {
                $headIds[$slug] = DB::table('account_heads')
                    ->where('name', $name)
                    ->first()->id;
            }
        }

        echo "✅ Account heads created/verified.\n";

        // ==============================
        // 2️⃣ CREATE BRANCH-SPECIFIC ACCOUNTS
        // ==============================

        // 🏭 Different bank names per branch for easy identification
        $bankNamesByBranch = [
            1 => ['MCB-Main', 'HBL-Main', 'UBL-Main'],        // Branch 1 (Main Store)
            2 => ['Meezan-Karachi', 'IBL-Karachi', 'BOP-Karachi'],  // Branch 2 (Karachi)
            3 => ['NBP-Lahore', 'Alfalah-Lahore', 'Silkbank-Lahore'], // Branch 3 (Lahore)
        ];

        $accountCounter = 1;
        $accounts = [];

        foreach ($branches as $branch) {
            $branchId = $branch->id;
            $branchBanks = $bankNamesByBranch[$branchId] ?? [];

            // 💰 CASH ACCOUNTS (per branch)
            $accounts[] = [
                'branch_id'        => $branchId,
                'head_id'          => $headIds['cash'],
                'account_code'     => str_pad($branchId, 2, '0', STR_PAD_LEFT) . '100',
                'title'            => 'Cash - ' . $branch->name,
                'opening_balance'  => 0.00,
                'status'           => 1,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            $accounts[] = [
                'branch_id'        => $branchId,
                'head_id'          => $headIds['cash'],
                'account_code'     => str_pad($branchId, 2, '0', STR_PAD_LEFT) . '101',
                'title'            => 'Petty Cash - ' . $branch->name,
                'opening_balance'  => 0.00,
                'status'           => 1,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            // 🏦 BANK ACCOUNTS (Multiple banks per branch - different names)
            foreach ($branchBanks as $index => $bankName) {
                $bankCode = str_pad($branchId, 2, '0', STR_PAD_LEFT) . '2' . str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                
                $accounts[] = [
                    'branch_id'        => $branchId,
                    'head_id'          => $headIds['bank'],
                    'account_code'     => $bankCode,
                    'title'            => $bankName,
                    'opening_balance'  => 0.00,
                    'status'           => 1,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }

            // 💸 EXPENSE ACCOUNTS (per branch)
            $expenseTypes = ['Utilities', 'Salaries', 'Rent', 'Transportation', 'Office Supplies'];
            foreach ($expenseTypes as $idx => $expenseType) {
                $accounts[] = [
                    'branch_id'        => $branchId,
                    'head_id'          => $headIds['expense'],
                    'account_code'     => str_pad($branchId, 2, '0', STR_PAD_LEFT) . '3' . str_pad($idx + 1, 2, '0', STR_PAD_LEFT),
                    'title'            => $expenseType . ' - ' . $branch->name,
                    'opening_balance'  => 0.00,
                    'status'           => 1,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }

            // 📊 ASSET ACCOUNTS (per branch)
            $accounts[] = [
                'branch_id'        => $branchId,
                'head_id'          => $headIds['asset'],
                'account_code'     => str_pad($branchId, 2, '0', STR_PAD_LEFT) . '400',
                'title'            => 'Fixed Assets - ' . $branch->name,
                'opening_balance'  => 0.00,
                'status'           => 1,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            $accounts[] = [
                'branch_id'        => $branchId,
                'head_id'          => $headIds['asset'],
                'account_code'     => str_pad($branchId, 2, '0', STR_PAD_LEFT) . '401',
                'title'            => 'Inventory - ' . $branch->name,
                'opening_balance'  => 0.00,
                'status'           => 1,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            // 📈 LIABILITY ACCOUNTS (per branch)
            $accounts[] = [
                'branch_id'        => $branchId,
                'head_id'          => $headIds['liability'],
                'account_code'     => str_pad($branchId, 2, '0', STR_PAD_LEFT) . '500',
                'title'            => 'Payable Accounts - ' . $branch->name,
                'opening_balance'  => 0.00,
                'status'           => 1,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            // 💰 REVENUE ACCOUNTS (per branch)
            $accounts[] = [
                'branch_id'        => $branchId,
                'head_id'          => $headIds['revenue'],
                'account_code'     => str_pad($branchId, 2, '0', STR_PAD_LEFT) . '600',
                'title'            => 'Sales Revenue - ' . $branch->name,
                'opening_balance'  => 0.00,
                'status'           => 1,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            $accounts[] = [
                'branch_id'        => $branchId,
                'head_id'          => $headIds['revenue'],
                'account_code'     => str_pad($branchId, 2, '0', STR_PAD_LEFT) . '601',
                'title'            => 'Service Revenue - ' . $branch->name,
                'opening_balance'  => 0.00,
                'status'           => 1,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        }

        // Insert all accounts
        DB::table('accounts')->insert($accounts);

        echo "✅ Chart of Accounts seeded successfully!\n";
        echo "📊 Total Accounts Created: " . count($accounts) . "\n";
        echo "\n📋 Summary:\n";
        echo "   ✓ Account Heads: " . count($headIds) . " (Global)\n";
        echo "   ✓ Branches: " . $branches->count() . "\n";
        echo "   ✓ Accounts per Branch: ~" . (count($accounts) / $branches->count()) . "\n";
    }
}