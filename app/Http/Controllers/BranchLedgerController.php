<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchAccount;
use App\Models\BranchTransaction;
use Illuminate\Http\Request;

class BranchLedgerController extends Controller
{
    // View ledger for current branch
    public function index()
    {
        // ✅ ERP PROPER: Super admin has no branch, show all transactions
        if (auth()->user()->hasRole('super admin')) {
            // Super admin can view all branch transactions
            $currentBranch = null;
            $account = null;
            $transactions = BranchTransaction::with(['relatedBranch', 'createdBy'])
                ->orderByDesc('created_at')
                ->paginate(30);
        } else {
            $branchId = auth()->user()->branch_id;
            if (!$branchId) {
                return back()->with('error', 'User must be assigned to a branch.');
            }

            $currentBranch = Branch::findOrFail($branchId);
            $account = BranchAccount::where('branch_id', $branchId)->first();

            // Get all transactions for this branch
            $transactions = BranchTransaction::where('branch_id', $branchId)
                ->with(['relatedBranch', 'createdBy'])
                ->orderByDesc('created_at')
                ->paginate(30);
        }

        // Summary statistics (calculated from all transactions using display_amount)
        if (auth()->user()->hasRole('super admin')) {
            $allTx = BranchTransaction::all();
        } else {
            $allTx = BranchTransaction::where('branch_id', $branchId)->get();
        }
        $totalDebit = $allTx->sum(fn($t) => $t->isDebit() ? $t->display_amount : 0);
        $totalCredit = $allTx->sum(fn($t) => $t->isCredit() ? $t->display_amount : 0);
        $balance = $totalCredit - $totalDebit;

        return view('admin_panel.branch_ledger.index', compact(
            'currentBranch',
            'account',
            'transactions',
            'totalDebit',
            'totalCredit',
            'balance'
        ));
    }

    // View summary/dashboard
    public function summary()
    {
        // ✅ ERP PROPER: Super admin can view all branch summaries
        if (auth()->user()->hasRole('super admin')) {
            $currentBranch = null;
            $account = null;

            // Statistics for all branches (calculated using display_amount)
            $allTx = BranchTransaction::all();
            $totalDebit = $allTx->sum(fn($t) => $t->isDebit() ? $t->display_amount : 0);
            $totalCredit = $allTx->sum(fn($t) => $t->isCredit() ? $t->display_amount : 0);
            $balance = $totalCredit - $totalDebit;

            // Recent transactions (all branches)
            $recentTransactions = BranchTransaction::with(['relatedBranch'])
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();

            // All transactions grouped by branch
            $owingTransactions = BranchTransaction::where('type', 'debit')
                ->with(['relatedBranch'])
                ->get()
                ->groupBy('related_branch_id');

            $dueTransactions = BranchTransaction::where('type', 'credit')
                ->with(['relatedBranch'])
                ->get()
                ->groupBy('related_branch_id');
        } else {
            $branchId = auth()->user()->branch_id;
            if (!$branchId) {
                return back()->with('error', 'User must be assigned to a branch.');
            }

            $currentBranch = Branch::findOrFail($branchId);
            $account = BranchAccount::where('branch_id', $branchId)->first();

            // Statistics (calculated using display_amount)
            $allTx = BranchTransaction::where('branch_id', $branchId)->get();
            $totalDebit = $allTx->sum(fn($t) => $t->isDebit() ? $t->display_amount : 0);
            $totalCredit = $allTx->sum(fn($t) => $t->isCredit() ? $t->display_amount : 0);

            $balance = $totalCredit - $totalDebit;

            // Recent transactions
            $recentTransactions = BranchTransaction::where('branch_id', $branchId)
                ->with(['relatedBranch'])
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();

            // Branches we owe to
            $owingTransactions = BranchTransaction::where('branch_id', $branchId)
                ->where('type', 'debit')
                ->with(['relatedBranch'])
                ->get()
                ->groupBy('related_branch_id');

            // Branches that owe to us
            $dueTransactions = BranchTransaction::where('branch_id', $branchId)
                ->where('type', 'credit')
                ->with(['relatedBranch'])
                ->get()
                ->groupBy('related_branch_id');
        }

        return view('admin_panel.branch_ledger.summary', compact(
            'currentBranch',
            'account',
            'balance',
            'totalDebit',
            'totalCredit',
            'recentTransactions',
            'owingTransactions',
            'dueTransactions'
        ));
    }

    // Detailed ledger with filters
    public function ledger(Request $request)
    {
        $branchId = auth()->user()->branch_id;
        if (!$branchId) {
            return back()->with('error', 'User must be assigned to a branch.');
        }

        $query = BranchTransaction::where('branch_id', $branchId)
            ->with(['relatedBranch', 'createdBy']);

        // Filter by type
        if ($request->has('type') && in_array($request->type, ['debit', 'credit'])) {
            $query->where('type', $request->type);
        }

        // Filter by reference type
        if ($request->filled('reference_type')) {
            $query->where('reference_type', $request->reference_type);
        }

        // Filter by related branch
        if ($request->filled('related_branch_id')) {
            $query->where('related_branch_id', $request->related_branch_id);
        }

        // Date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $transactions = $query->orderByDesc('created_at')->paginate(50);

        // Get available branches for filter
        $branches = Branch::where('id', '!=', $branchId)->get();

        // Summary (calculated using display_amount from all filtered transactions)
        $allFilteredTx = (clone $query)->get();
        $summary = [
            'totalDebit' => $allFilteredTx->sum(fn($t) => $t->isDebit() ? $t->display_amount : 0),
            'totalCredit' => $allFilteredTx->sum(fn($t) => $t->isCredit() ? $t->display_amount : 0),
        ];

        return view('admin_panel.branch_ledger.ledger', compact('transactions', 'branches', 'summary'));
    }

    // View all branches and their ledger status
    public function allBranches()
    {
        // Authorization: Only super admin can view all branches
        $authUser = auth()->user();
        if (!$authUser->hasRole('super admin')) {
            // Regular users see only their own branch
            return redirect()->route('branch_ledger_view_branch', $authUser->branch_id)
                ->with('info', 'You can only view your own branch ledger.');
        }

        // Get all branches with their account balances
        $branches = Branch::with(['account'])
            ->get()
            ->map(function ($branch) {
                $allTx = BranchTransaction::where('branch_id', $branch->id)->get();
                $totalDebit = $allTx->sum(fn($t) => $t->isDebit() ? $t->display_amount : 0);
                $totalCredit = $allTx->sum(fn($t) => $t->isCredit() ? $t->display_amount : 0);
                $balance = $totalCredit - $totalDebit;
                
                return [
                    'id' => $branch->id,
                    'name' => $branch->name ?? 'Branch #' . $branch->id,
                    'balance' => $balance,
                    'totalCredit' => $totalCredit,
                    'totalDebit' => $totalDebit,
                    'status' => $balance > 0 ? 'owed' : ($balance < 0 ? 'owing' : 'balanced'),
                ];
            })
            ->toArray();  // ✅ Convert Collection to Array

        return view('admin_panel.branch_ledger.all_branches', compact('branches'));
    }

    // View detailed ledger for a specific branch
    public function viewBranchLedger(Request $request, $branchId)
    {
        // Authorization: User can only view their own branch, super admin can view any
        $authUser = auth()->user();
        if (!$authUser->hasRole('super admin') && $authUser->branch_id != $branchId) {
            return back()->with('error', 'Unauthorized: You can only view your own branch ledger.');
        }

        $branch = Branch::findOrFail($branchId);
        $account = BranchAccount::where('branch_id', $branchId)->first();

        // Build query with filters
        $query = BranchTransaction::where('branch_id', $branchId)
            ->with(['relatedBranch', 'createdBy'])
            ->orderByDesc('created_at');

        // Apply filters
        if ($request->filled('type') && in_array($request->type, ['debit', 'credit'])) {
            $query->where('type', $request->type);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Summary statistics (calculated using display_amount for all matching transactions)
        $allTx = (clone $query)->get();
        $totalDebit = $allTx->sum(fn($t) => $t->isDebit() ? $t->display_amount : 0);
        $totalCredit = $allTx->sum(fn($t) => $t->isCredit() ? $t->display_amount : 0);
        $balance = $totalCredit - $totalDebit;

        // Paginated transactions with query string preserved
        $transactions = $query->paginate(50)->withQueryString();

        return view('admin_panel.branch_ledger.view_branch', compact(
            'branch',
            'account',
            'transactions',
            'totalDebit',
            'totalCredit',
            'balance'
        ));
    }

    // Show transfer details with date range filter
    public function transferDetails(Request $request, $branchId)
    {
        // Authorization: User can only view their own branch transfers, super admin can view any
        $authUser = auth()->user();
        if (!$authUser->hasRole('super admin') && $authUser->branch_id != $branchId) {
            return back()->with('error', 'Unauthorized: You can only view your own branch transfer details.');
        }

        $branch = Branch::findOrFail($branchId);
        
        // Build query for stock transfers
        $query = \App\Models\StockTransfer::where(function ($q) use ($branchId) {
            $q->where('from_branch_id', $branchId)
              ->orWhere('to_branch_id', $branchId);
        })
        ->with(['product', 'fromBranch', 'toBranch', 'fromWarehouse', 'toWarehouse'])
        ->orderByDesc('created_at');

        // Apply date range filters
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $transfers = $query->paginate(50);

        // Calculate totals using model accessors
        $totalQuantity = 0;
        $totalValue = 0;
        foreach ($transfers as $transfer) {
            $totalQuantity += (float)$transfer->quantity;
            $totalValue += (float)$transfer->total_value;
        }

        return view('admin_panel.branch_ledger.transfer_details', compact(
            'branch',
            'transfers',
            'totalQuantity',
            'totalValue'
        ));
    }

    // Generate PDF report
    public function report(Request $request)
    {
        $authUser = auth()->user();
        if ($authUser->hasRole('super admin')) {
            $branchId = $request->input('branch_id', $authUser->branch_id);
        } else {
            $branchId = $authUser->branch_id;
        }

        if (!$branchId) {
            return back()->with('error', 'Please specify a branch to generate the report.');
        }

        $currentBranch = Branch::findOrFail($branchId);
        $account = BranchAccount::where('branch_id', $branchId)->first();

        $query = BranchTransaction::where('branch_id', $branchId)
            ->with(['relatedBranch', 'createdBy']);

        if ($request->filled('type') && in_array($request->type, ['debit', 'credit'])) {
            $query->where('type', $request->type);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $transactions = $query->orderByDesc('created_at')->get();

        $totalDebit = $transactions->sum(fn($t) => $t->isDebit() ? $t->display_amount : 0);
        $totalCredit = $transactions->sum(fn($t) => $t->isCredit() ? $t->display_amount : 0);
        $balance = $totalCredit - $totalDebit;

        // Generate PDF using barryvdh/laravel-dompdf
        $pdf = \PDF::loadView('admin_panel.branch_ledger.report', compact(
            'currentBranch',
            'account',
            'transactions',
            'totalDebit',
            'totalCredit',
            'balance'
        ));

        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $currentBranch->name ?? $currentBranch->branch_name ?? "branch_{$branchId}");
        return $pdf->download("branch_ledger_{$safeName}_" . now()->format('Y-m-d') . ".pdf");
    }
}
