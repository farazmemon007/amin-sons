<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\Branch;
use App\Models\BranchTransaction;
use App\Models\BranchAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoucherInterBranchController extends Controller
{
    // List vouchers for current branch
    public function index()
    {
        // ✅ ERP PROPER: Super admin has no branch, show all vouchers
        if (auth()->user()->hasRole('super admin')) {
            $vouchers = Voucher::with(['toBranch', 'createdBy'])
                ->orderByDesc('created_at')
                ->paginate(30);
        } else {
            $branchId = auth()->user()->branch_id;
            if (!$branchId) {
                return back()->with('error', 'User must be assigned to a branch.');
            }

            $vouchers = Voucher::where('from_branch_id', $branchId)
                ->with(['toBranch', 'createdBy'])
                ->orderByDesc('created_at')
                ->paginate(30);
        }

        return view('admin_panel.inter_branch.vouchers.index', compact('vouchers'));
    }

    // Create payment voucher form
    public function createPayment()
    {
        // ✅ ERP PROPER: Super admin can create vouchers from any branch
        if (auth()->user()->hasRole('super admin')) {
            $branches = Branch::all();
            $fromBranchId = null;
        } else {
            $fromBranchId = auth()->user()->branch_id;
            if (!$fromBranchId) {
                return back()->with('error', 'User must be assigned to a branch.');
            }
            $branches = Branch::where('id', '!=', $fromBranchId)->get();
        }

        return view('admin_panel.inter_branch.vouchers.create_payment', compact('branches', 'fromBranchId'));
    }

    // Store payment voucher
    public function storePayment(Request $request)
    {
        // ✅ ERP PROPER: Super admin can create vouchers from any branch
        if (auth()->user()->hasRole('super admin')) {
            $fromBranchId = $request->input('from_branch_id');
            if (!$fromBranchId) {
                return back()->with('error', 'As super admin, please select the source branch.');
            }
        } else {
            $fromBranchId = auth()->user()->branch_id;
            if (!$fromBranchId) {
                return back()->with('error', 'User must be assigned to a branch.');
            }
        }

        $validated = $request->validate([
            'to_branch_id' => 'required|exists:branches,id|different:from_branch_id',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,bank,cheque',
            'reference' => 'nullable|string|max:100',
            'remarks' => 'nullable|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($validated, $fromBranchId) {
                // Create voucher
                $voucher = Voucher::create([
                    'type' => 'payment',
                    'from_branch_id' => $fromBranchId,
                    'to_branch_id' => $validated['to_branch_id'],
                    'amount' => $validated['amount'],
                    'method' => $validated['method'],
                    'reference' => $validated['reference'] ?? null,
                    'remarks' => $validated['remarks'] ?? null,
                    'created_by' => auth()->id(),
                ]);

                // Create ledger entries
                // Sender: Debit reduced (Credit - reduces payable)
                BranchTransaction::create([
                    'branch_id' => $fromBranchId,
                    'related_branch_id' => $validated['to_branch_id'],
                    'type' => 'credit', // Reduces liability
                    'amount' => $validated['amount'],
                    'reference_type' => 'payment',
                    'reference_id' => $voucher->id,
                    'description' => "Payment to Branch #{$validated['to_branch_id']}",
                    'created_by' => auth()->id(),
                ]);

                // Receiver: Credit reduced (Debit - reduces receivable)
                BranchTransaction::create([
                    'branch_id' => $validated['to_branch_id'],
                    'related_branch_id' => $fromBranchId,
                    'type' => 'debit', // Reduces asset
                    'amount' => $validated['amount'],
                    'reference_type' => 'payment',
                    'reference_id' => $voucher->id,
                    'description' => "Payment from Branch #{$fromBranchId}",
                    'created_by' => auth()->id(),
                ]);

                // Update account balances
                BranchAccount::where('branch_id', $fromBranchId)->first()?->updateBalance();
                BranchAccount::where('branch_id', $validated['to_branch_id'])->first()?->updateBalance();
            });

            return redirect()->route('inter_branch_vouchers.index')
                ->with('success', 'Payment voucher created successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error creating voucher: ' . $e->getMessage());
        }
    }

    // Create receipt voucher form
    // Create receipt voucher form
    public function createReceipt()
    {
        $branchId = auth()->user()->branch_id;
        if (!$branchId) {
            return back()->with('error', 'User must be assigned to a branch.');
        }

        $branches = Branch::where('id', '!=', $branchId)->get();

        return view('admin_panel.inter_branch.vouchers.create_receipt', compact('branches', 'branchId'));
    }

    // Store receipt voucher
    public function storeReceipt(Request $request)
    {
        $branchId = auth()->user()->branch_id;
        if (!$branchId) {
            return back()->with('error', 'User must be assigned to a branch.');
        }

        $validated = $request->validate([
            'from_branch_id' => 'required|exists:branches,id|different:from_branch_id',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,bank,cheque',
            'reference' => 'nullable|string|max:100',
            'remarks' => 'nullable|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($validated, $branchId) {
                // Create voucher
                $voucher = Voucher::create([
                    'type' => 'receipt',
                    'from_branch_id' => $validated['from_branch_id'],
                    'to_branch_id' => $branchId,
                    'amount' => $validated['amount'],
                    'method' => $validated['method'],
                    'reference' => $validated['reference'] ?? null,
                    'remarks' => $validated['remarks'] ?? null,
                    'created_by' => auth()->id(),
                ]);

                // Create ledger entries
                // Receiver (us): Debit reduced (Credit - reduces receivable)
                BranchTransaction::create([
                    'branch_id' => $branchId,
                    'related_branch_id' => $validated['from_branch_id'],
                    'type' => 'debit', // Reduces asset
                    'amount' => $validated['amount'],
                    'reference_type' => 'receipt',
                    'reference_id' => $voucher->id,
                    'description' => "Receipt from Branch #{$validated['from_branch_id']}",
                    'created_by' => auth()->id(),
                ]);

                // Sender: Credit reduced (Debit - reduces payable)
                BranchTransaction::create([
                    'branch_id' => $validated['from_branch_id'],
                    'related_branch_id' => $branchId,
                    'type' => 'credit', // Reduces liability
                    'amount' => $validated['amount'],
                    'reference_type' => 'receipt',
                    'reference_id' => $voucher->id,
                    'description' => "Receipt to Branch #{$branchId}",
                    'created_by' => auth()->id(),
                ]);

                // Update account balances
                BranchAccount::where('branch_id', $branchId)->first()?->updateBalance();
                BranchAccount::where('branch_id', $validated['from_branch_id'])->first()?->updateBalance();
            });

            return redirect()->route('inter_branch_vouchers.index')
                ->with('success', 'Receipt voucher created successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error creating voucher: ' . $e->getMessage());
        }
    }

    // View voucher details
    public function show(Voucher $voucher)
    {
        return view('admin_panel.inter_branch.vouchers.show', compact('voucher'));
    }
}
