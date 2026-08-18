<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\Branch;
use App\Models\BranchTransaction;
use App\Models\BranchAccount;
use App\Models\Account;
use App\Models\AccountHead;
use App\Models\AccountLedgerEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoucherInterBranchController extends Controller
{
    // List vouchers for current branch
    public function index()
    {
        // ✅ ERP PROPER: Super admin has no branch, show all vouchers
        if (auth()->user()->hasRole('super admin')) {
            $vouchers = Voucher::with(['fromBranch', 'toBranch', 'createdBy'])
                ->orderByDesc('created_at')
                ->paginate(30);
        } else {
            $branchId = auth()->user()->branch_id;
            if (!$branchId) {
                return back()->with('error', 'User must be assigned to a branch.');
            }

            $vouchers = Voucher::where(function($q) use ($branchId) {
                    $q->where('from_branch_id', $branchId)
                      ->orWhere('to_branch_id', $branchId);
                })
                ->with(['fromBranch', 'toBranch', 'createdBy'])
                ->orderByDesc('created_at')
                ->paginate(30);
        }

        return view('admin_panel.inter_branch.vouchers.index', compact('vouchers'));
    }

    // Create payment voucher form
    public function createPayment()
    {
        $isSuperAdmin = auth()->user()->hasRole('super admin');
        $branches = Branch::all();

        if ($isSuperAdmin) {
            $fromBranchId = null;
        } else {
            $fromBranchId = auth()->user()->branch_id;
            if (!$fromBranchId) {
                return back()->with('error', 'User must be assigned to a branch.');
            }
        }

        return view('admin_panel.inter_branch.vouchers.create_payment', compact('branches', 'fromBranchId', 'isSuperAdmin'));
    }

    // Store payment voucher
    public function storePayment(Request $request)
    {
        $isSuperAdmin = auth()->user()->hasRole('super admin');
        
        if ($isSuperAdmin) {
            $fromBranchId = $request->input('from_branch_id');
            if (!$fromBranchId) {
                return back()->with('error', 'As super admin, please select the sending branch.');
            }
        } else {
            $fromBranchId = auth()->user()->branch_id;
            if (!$fromBranchId) {
                return back()->with('error', 'User must be assigned to a branch.');
            }
        }

        $validated = $request->validate([
            'to_branch_id'     => 'required|exists:branches,id|different:from_branch_id',
            'from_head_id'     => 'nullable|exists:account_heads,id',
            'from_account_id'  => 'required|exists:accounts,id',
            'to_head_id'       => 'nullable|exists:account_heads,id',
            'to_account_id'    => 'required|exists:accounts,id',
            'amount'           => 'required|numeric|min:0.01',
            'method'           => 'nullable|string',
            'reference'        => 'nullable|string|max:100',
            'remarks'          => 'nullable|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($validated, $fromBranchId) {
                $amount = (float)$validated['amount'];
                $toBranchId = $validated['to_branch_id'];
                $fromAccountId = $validated['from_account_id'];
                $toAccountId = $validated['to_account_id'];

                // Auto-determine payment method from account head if not provided
                $fromAccount = Account::with('head')->lockForUpdate()->find($fromAccountId);
                $toAccount = Account::with('head')->lockForUpdate()->find($toAccountId);

                $headName = strtolower($fromAccount?->head?->name ?? '');
                $method = $validated['method'] ?? (str_contains($headName, 'cash') ? 'cash' : 'bank');

                // 1. Create Voucher Record
                $voucher = Voucher::create([
                    'type'            => 'payment',
                    'voucher_type'    => 'inter_branch_payment',
                    'from_branch_id'  => $fromBranchId,
                    'to_branch_id'    => $toBranchId,
                    'from_account_id' => $fromAccountId,
                    'to_account_id'   => $toAccountId,
                    'amount'          => $amount,
                    'method'          => $method,
                    'reference'       => $validated['reference'] ?? null,
                    'remarks'         => $validated['remarks'] ?? null,
                    'status'          => 'approved',
                    'date'            => now()->toDateString(),
                    'created_by'      => auth()->id(),
                ]);

                // 2. Post Ledger Entry for Sending Account (Money goes OUT)
                if ($fromAccount) {
                    $lastEntryFrom = AccountLedgerEntry::where('account_id', $fromAccountId)->latest('id')->first();
                    $prevBalFrom = $lastEntryFrom ? (float)$lastEntryFrom->running_balance : (float)$fromAccount->opening_balance;

                    $debitFrom = 0;
                    $creditFrom = $amount;
                    if (trim(strtolower($fromAccount->type)) === 'debit') {
                        $newBalFrom = $prevBalFrom + $debitFrom - $creditFrom;
                    } else {
                        $newBalFrom = $prevBalFrom + $creditFrom - $debitFrom;
                    }

                    AccountLedgerEntry::create([
                        'account_id'       => $fromAccountId,
                        'branch_id'        => $fromBranchId,
                        'voucher_type'     => 'payment',
                        'voucher_no'       => 'IBP-' . $voucher->id,
                        'voucher_id'       => $voucher->id,
                        'entry_no'         => AccountLedgerEntry::generateEntryNo($fromAccountId, 'payment'),
                        'transaction_date' => now()->toDateString(),
                        'description'      => "Inter-Branch Payment to Branch #" . $toBranchId . " (" . ($toAccount->title ?? 'Account') . ")",
                        'debit'            => $debitFrom,
                        'credit'           => $creditFrom,
                        'running_balance'  => $newBalFrom,
                        'created_by'       => auth()->id(),
                    ]);
                }

                // 3. Post Ledger Entry for Receiving Account (Money comes IN)
                if ($toAccount) {
                    $lastEntryTo = AccountLedgerEntry::where('account_id', $toAccountId)->latest('id')->first();
                    $prevBalTo = $lastEntryTo ? (float)$lastEntryTo->running_balance : (float)$toAccount->opening_balance;

                    $debitTo = $amount;
                    $creditTo = 0;
                    if (trim(strtolower($toAccount->type)) === 'debit') {
                        $newBalTo = $prevBalTo + $debitTo - $creditTo;
                    } else {
                        $newBalTo = $prevBalTo + $creditTo - $debitTo;
                    }

                    AccountLedgerEntry::create([
                        'account_id'       => $toAccountId,
                        'branch_id'        => $toBranchId,
                        'voucher_type'     => 'receipt',
                        'voucher_no'       => 'IBR-' . $voucher->id,
                        'voucher_id'       => $voucher->id,
                        'entry_no'         => AccountLedgerEntry::generateEntryNo($toAccountId, 'receipt'),
                        'transaction_date' => now()->toDateString(),
                        'description'      => "Inter-Branch Receipt from Branch #" . $fromBranchId . " (" . ($fromAccount->title ?? 'Account') . ")",
                        'debit'            => $debitTo,
                        'credit'           => $creditTo,
                        'running_balance'  => $newBalTo,
                        'created_by'       => auth()->id(),
                    ]);
                }

                // 4. Create Inter-Branch Transactions & Update Branch Balances
                BranchTransaction::create([
                    'branch_id'         => $fromBranchId,
                    'related_branch_id' => $toBranchId,
                    'type'              => 'credit',
                    'amount'            => $amount,
                    'reference_type'    => 'payment',
                    'reference_id'      => $voucher->id,
                    'description'       => "Inter-Branch Payment to Branch #{$toBranchId}",
                    'created_by'        => auth()->id(),
                ]);

                BranchTransaction::create([
                    'branch_id'         => $toBranchId,
                    'related_branch_id' => $fromBranchId,
                    'type'              => 'debit',
                    'amount'            => $amount,
                    'reference_type'    => 'payment',
                    'reference_id'      => $voucher->id,
                    'description'       => "Inter-Branch Payment from Branch #{$fromBranchId}",
                    'created_by'        => auth()->id(),
                ]);

                BranchAccount::where('branch_id', $fromBranchId)->first()?->updateBalance();
                BranchAccount::where('branch_id', $toBranchId)->first()?->updateBalance();
            });

            return redirect()->route('inter_branch_vouchers.index')
                ->with('success', 'Payment voucher recorded successfully! Account ledgers updated.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error creating payment voucher: ' . $e->getMessage());
        }
    }

    // Create receipt voucher form
    public function createReceipt()
    {
        $isSuperAdmin = auth()->user()->hasRole('super admin');
        $branches = Branch::all();

        if ($isSuperAdmin) {
            $toBranchId = null;
        } else {
            $toBranchId = auth()->user()->branch_id;
            if (!$toBranchId) {
                return back()->with('error', 'User must be assigned to a branch.');
            }
        }

        return view('admin_panel.inter_branch.vouchers.create_receipt', compact('branches', 'toBranchId', 'isSuperAdmin'));
    }

    // Store receipt voucher
    public function storeReceipt(Request $request)
    {
        $isSuperAdmin = auth()->user()->hasRole('super admin');

        if ($isSuperAdmin) {
            $toBranchId = $request->input('to_branch_id');
            if (!$toBranchId) {
                return back()->with('error', 'As super admin, please select the receiving branch.');
            }
        } else {
            $toBranchId = auth()->user()->branch_id;
            if (!$toBranchId) {
                return back()->with('error', 'User must be assigned to a branch.');
            }
        }

        $validated = $request->validate([
            'from_branch_id'   => 'required|exists:branches,id|different:to_branch_id',
            'from_head_id'     => 'nullable|exists:account_heads,id',
            'from_account_id'  => 'required|exists:accounts,id',
            'to_head_id'       => 'nullable|exists:account_heads,id',
            'to_account_id'    => 'required|exists:accounts,id',
            'amount'           => 'required|numeric|min:0.01',
            'method'           => 'nullable|string',
            'reference'        => 'nullable|string|max:100',
            'remarks'          => 'nullable|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($validated, $toBranchId) {
                $amount = (float)$validated['amount'];
                $fromBranchId = $validated['from_branch_id'];
                $fromAccountId = $validated['from_account_id'];
                $toAccountId = $validated['to_account_id'];

                $fromAccount = Account::with('head')->lockForUpdate()->find($fromAccountId);
                $toAccount = Account::with('head')->lockForUpdate()->find($toAccountId);

                $headName = strtolower($toAccount?->head?->name ?? '');
                $method = $validated['method'] ?? (str_contains($headName, 'cash') ? 'cash' : 'bank');

                // 1. Create Voucher Record
                $voucher = Voucher::create([
                    'type'            => 'receipt',
                    'voucher_type'    => 'inter_branch_receipt',
                    'from_branch_id'  => $fromBranchId,
                    'to_branch_id'    => $toBranchId,
                    'from_account_id' => $fromAccountId,
                    'to_account_id'   => $toAccountId,
                    'amount'          => $amount,
                    'method'          => $method,
                    'reference'       => $validated['reference'] ?? null,
                    'remarks'         => $validated['remarks'] ?? null,
                    'status'          => 'approved',
                    'date'            => now()->toDateString(),
                    'created_by'      => auth()->id(),
                ]);

                // 2. Post Ledger Entry for Sending Account (Money goes OUT)
                if ($fromAccount) {
                    $lastEntryFrom = AccountLedgerEntry::where('account_id', $fromAccountId)->latest('id')->first();
                    $prevBalFrom = $lastEntryFrom ? (float)$lastEntryFrom->running_balance : (float)$fromAccount->opening_balance;

                    $debitFrom = 0;
                    $creditFrom = $amount;
                    if (trim(strtolower($fromAccount->type)) === 'debit') {
                        $newBalFrom = $prevBalFrom + $debitFrom - $creditFrom;
                    } else {
                        $newBalFrom = $prevBalFrom + $creditFrom - $debitFrom;
                    }

                    AccountLedgerEntry::create([
                        'account_id'       => $fromAccountId,
                        'branch_id'        => $fromBranchId,
                        'voucher_type'     => 'payment',
                        'voucher_no'       => 'IBP-' . $voucher->id,
                        'voucher_id'       => $voucher->id,
                        'entry_no'         => AccountLedgerEntry::generateEntryNo($fromAccountId, 'payment'),
                        'transaction_date' => now()->toDateString(),
                        'description'      => "Inter-Branch Receipt to Branch #" . $toBranchId . " (" . ($toAccount->title ?? 'Account') . ")",
                        'debit'            => $debitFrom,
                        'credit'           => $creditFrom,
                        'running_balance'  => $newBalFrom,
                        'created_by'       => auth()->id(),
                    ]);
                }

                // 3. Post Ledger Entry for Receiving Account (Money comes IN)
                if ($toAccount) {
                    $lastEntryTo = AccountLedgerEntry::where('account_id', $toAccountId)->latest('id')->first();
                    $prevBalTo = $lastEntryTo ? (float)$lastEntryTo->running_balance : (float)$toAccount->opening_balance;

                    $debitTo = $amount;
                    $creditTo = 0;
                    if (trim(strtolower($toAccount->type)) === 'debit') {
                        $newBalTo = $prevBalTo + $debitTo - $creditTo;
                    } else {
                        $newBalTo = $prevBalTo + $creditTo - $debitTo;
                    }

                    AccountLedgerEntry::create([
                        'account_id'       => $toAccountId,
                        'branch_id'        => $toBranchId,
                        'voucher_type'     => 'receipt',
                        'voucher_no'       => 'IBR-' . $voucher->id,
                        'voucher_id'       => $voucher->id,
                        'entry_no'         => AccountLedgerEntry::generateEntryNo($toAccountId, 'receipt'),
                        'transaction_date' => now()->toDateString(),
                        'description'      => "Inter-Branch Receipt from Branch #" . $fromBranchId . " (" . ($fromAccount->title ?? 'Account') . ")",
                        'debit'            => $debitTo,
                        'credit'           => $creditTo,
                        'running_balance'  => $newBalTo,
                        'created_by'       => auth()->id(),
                    ]);
                }

                // 4. Create Inter-Branch Transactions & Update Branch Balances
                BranchTransaction::create([
                    'branch_id'         => $toBranchId,
                    'related_branch_id' => $fromBranchId,
                    'type'              => 'debit',
                    'amount'            => $amount,
                    'reference_type'    => 'receipt',
                    'reference_id'      => $voucher->id,
                    'description'       => "Inter-Branch Receipt from Branch #{$fromBranchId}",
                    'created_by'        => auth()->id(),
                ]);

                BranchTransaction::create([
                    'branch_id'         => $fromBranchId,
                    'related_branch_id' => $toBranchId,
                    'type'              => 'credit',
                    'amount'            => $amount,
                    'reference_type'    => 'receipt',
                    'reference_id'      => $voucher->id,
                    'description'       => "Inter-Branch Receipt to Branch #{$toBranchId}",
                    'created_by'        => auth()->id(),
                ]);

                BranchAccount::where('branch_id', $toBranchId)->first()?->updateBalance();
                BranchAccount::where('branch_id', $fromBranchId)->first()?->updateBalance();
            });

            return redirect()->route('inter_branch_vouchers.index')
                ->with('success', 'Receipt voucher recorded successfully! Account ledgers updated.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error creating receipt voucher: ' . $e->getMessage());
        }
    }

    // View voucher details
    public function show(Voucher $voucher)
    {
        return view('admin_panel.inter_branch.vouchers.show', compact('voucher'));
    }

    // ==========================================
    // AJAX APIs for Cascading Dropdowns
    // ==========================================

    // Get Account Heads for a Branch
    public function getBranchHeads($branchId)
    {
        $headIds = Account::where('branch_id', $branchId)->pluck('head_id')->unique()->filter()->toArray();
        $heads = AccountHead::whereIn('id', $headIds)->get(['id', 'name']);
        
        // If branch has no accounts set up, fallback to all active heads
        if ($heads->isEmpty()) {
            $heads = AccountHead::get(['id', 'name']);
        }

        return response()->json([
            'success' => true,
            'heads' => $heads,
        ]);
    }

    // Get Accounts under a specific Head & Branch
    public function getHeadAccounts($branchId = null, $headId = null)
    {
        $query = Account::query();

        if ($headId) {
            $query->where('head_id', $headId);
        }

        if ($branchId) {
            $query->where(function($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->orWhereNull('branch_id')
                  ->orWhere('branch_id', 0);
            });
        }

        $accounts = $query->get();

        // If no accounts found for that specific branch+head combination, fallback to all accounts under that head
        if ($accounts->isEmpty() && $headId) {
            $accounts = Account::where('head_id', $headId)->get();
        }

        $formattedAccounts = $accounts->map(function ($acc) {
            $lastEntry = AccountLedgerEntry::where('account_id', $acc->id)->latest('id')->first();
            $balance = $lastEntry ? (float)$lastEntry->running_balance : (float)$acc->opening_balance;
            
            return [
                'id' => $acc->id,
                'title' => $acc->title,
                'account_code' => $acc->account_code,
                'type' => $acc->type,
                'balance' => $balance,
                'formatted_balance' => 'Rs. ' . number_format(abs($balance), 2) . ($balance < 0 ? ' (Dr)' : ' (Cr)'),
            ];
        });

        return response()->json([
            'success' => true,
            'accounts' => $formattedAccounts,
        ]);
    }

    // Get Account Balance for badge display
    public function getAccountBalance($accountId)
    {
        $acc = Account::findOrFail($accountId);
        $lastEntry = AccountLedgerEntry::where('account_id', $acc->id)->latest('id')->first();
        $balance = $lastEntry ? (float)$lastEntry->running_balance : (float)$acc->opening_balance;

        return response()->json([
            'success' => true,
            'account_id' => $acc->id,
            'balance' => $balance,
            'formatted_balance' => 'Rs. ' . number_format(abs($balance), 2) . ($balance < 0 ? ' (Dr)' : ' (Cr)'),
            'type' => $acc->type,
        ]);
    }
}
