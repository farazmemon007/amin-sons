<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountHead;
use App\Models\AccountLedgerEntry;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\ExpenseVoucher;
use App\Models\Narration;
use App\Models\PaymentVoucher;
use App\Models\ReceiptsVoucher;
use App\Models\Vendor;
use App\Models\VendorLedger;
use App\Models\JournalVoucher;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    public function index($type)
    {

        // Sirf selected type ka data laa lo
        $vouchers = Voucher::where('voucher_type', $type)->latest()->get();
        $narration = Narration::where('expense_head', $type)->get();

        return view('admin_panel.accounts.expenses', [
            'vouchers' => $vouchers,
            'type' => $type,
            'narration' => $narration
        ]);
    }


    public function store(Request $request)
    {
        // Validate that arrays are present and match in length
        $request->validate([
            'date' => 'required',
            'type' => 'required',
            'person' => 'required',
            'narration' => 'required',
            'amount' => 'required',
        ]);

        // Loop through each row and create a voucher
        foreach ($request->date as $index => $date) {
            Voucher::create([
                'voucher_type' => $request->sub_head,
                'sales_officer' => auth()->user()->name,
                'date' => $date,
                'type' => $request->type[$index],
                'person' => $request->person[$index],
                'sub_head' => $request->sub_head[$index] ?? null,
                'narration' => $request->narration[$index],
                'amount' => $request->amount[$index],
                'status' => 'draft',
            ]);
        }

        return back()->with('success', 'Vouchers saved successfully!');
    }


    /**
     * Display the specified resource.
     */
    public function show(Voucher $voucher)
    {
        //
    }
    public function receipt($id)
    {
        $voucher = Voucher::findOrFail($id);

        $customerName = $voucher->person; // Default
        $customerAddress = '-';
        $closingBalance = 0;

        //yahan accounts bhi show karwayn all heads 
        // bank cash  
        if ($voucher->type === 'Main Customer' && $voucher->mainCustomer) {
            $customerName = $voucher->mainCustomer->customer_name;
            $customerAddress = $voucher->mainCustomer->address;
            $closingBalance = $voucher->mainCustomer->closing_balance;
        } elseif ($voucher->type === 'Sub Customer' && $voucher->subCustomer) {
            $customerName = $voucher->subCustomer->customer_name;
            $customerAddress = $voucher->subCustomer->address;
            $closingBalance = $voucher->subCustomer->closing_balance;
        }

        return view('admin_panel.accounts.receipt', compact('voucher', 'customerName', 'customerAddress', 'closingBalance'));
    }




    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Voucher $voucher)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Voucher $voucher)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Voucher $voucher)
    {
        //
    }

    public function all_recepit_vochers(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole('super admin');
        $selectedBranch = $request->get('branch_id', 'all');

        $query = \App\Models\ReceiptsVoucher::with('branch')->orderBy('id', 'DESC');

        if (!$isSuperAdmin) {
            // Branch user: strictly restrict to their branch
            $userBranchId = $user->branch_id ?? 0;
            $query->where('branch_id', $userBranchId);
        } else {
            // Super Admin: allow filtering by branch
            if ($request->filled('branch_id') && $request->branch_id !== 'all') {
                $query->where('branch_id', $request->branch_id);
            }
        }

        $receipts = $query->get();

        // Fetch active branches for the Super Admin filter
        $branches = $isSuperAdmin ? \App\Models\Branch::where('status', '!=', 'inactive')->orWhereNull('status')->orderBy('name')->get() : collect();

        foreach ($receipts as $voucher) {
            $partyName = '-';
            $typeLabel = '-';

            // 🧩 Check if type is numeric → account-based
            if (is_numeric($voucher->type)) {
                $accountHead = DB::table('account_heads')->where('id', $voucher->type)->first();
                $account = DB::table('accounts')->where('id', $voucher->party_id)->first();

                $typeLabel = $accountHead->name ?? 'Account';
                $partyName = $account->title ?? '-';
            } elseif ($voucher->type === 'vendor') {
                $vendor = DB::table('vendors')->where('id', $voucher->party_id)->first();
                $typeLabel = 'Vendor';
                $partyName = $vendor->name ?? '-';
            } elseif ($voucher->type === 'customer') {
                $customer = DB::table('customers')->where('id', $voucher->party_id)->first();
                $typeLabel = 'Customer';
                $partyName = $customer->customer_name ?? '-';
            } elseif ($voucher->type === 'walkin') {
                $walkin = DB::table('customers')
                    ->where('id', $voucher->party_id)
                    ->where('customer_type', 'Walking Customer')
                    ->first();
                $typeLabel = 'Walk-in';
                $partyName = $walkin->customer_name ?? '-';
            }

            // Attach new properties to the object
            $voucher->type_label = $typeLabel;
            $voucher->party_name = $partyName;
        }

        return view('admin_panel.vochers.all_recepit_vochers', compact(
            'receipts',
            'branches',
            'isSuperAdmin',
            'selectedBranch'
        ));
    }


    public function print($id)
    {
        $voucher = ReceiptsVoucher::findOrFail($id);

        // Decode JSON arrays
        $narrations = json_decode($voucher->narration_id, true) ?? [];
        $references = json_decode($voucher->reference_no, true) ?? [];
        $accountHeads = json_decode($voucher->row_account_head, true) ?? [];
        $accounts = json_decode($voucher->row_account_id, true) ?? [];
        $amounts = json_decode($voucher->amount, true) ?? [];

        // Rows build
        $rows = [];
        foreach ($accounts as $index => $accountId) {
            $narrId = $narrations[$index] ?? null;
            $narration = DB::table('narrations')->where('id', $narrId)->value('narration');
            $ref = $references[$index] ?? null;
            $accountHead = DB::table('account_heads')->where('id', $accountHeads[$index] ?? null)->value('name');
            $account = DB::table('accounts')->where('id', $accountId)->first();
            $amount = (float)($amounts[$index] ?? 0);

            $rows[] = [
                'narration' => $narration,
                'reference' => $ref,
                'account_head' => $accountHead,
                'account_name' => $account->title ?? null,
                'account_code' => $account->account_code ?? null,
                'amount' => $amount,
            ];
        }

        // 🧩 Party setup — dynamic based on type
        $party = null;
        $previousBalance = 0;

        // ✅ If type is numeric → means from Account Head
        if (is_numeric($voucher->type)) {
            $accountHead = DB::table('account_heads')->where('id', $voucher->type)->first();
            $account = DB::table('accounts')->where('id', $voucher->party_id)->first();

            if ($account) {
                $party = (object)[
                    'name' => $account->title ?? '—',
                    'address' => '—',
                    'phone' => $account->account_code ?? '—',
                    'head_name' => $accountHead->name ?? '—',
                ];
            }

            // ✅ If vendor
        } elseif ($voucher->type === 'vendor') {
            $party = DB::table('vendors')->where('id', $voucher->party_id)->first();
            $previousBalance = DB::table('vendor_ledgers')
                ->where('vendor_id', $voucher->party_id)
                ->orderByDesc('id')
                ->value('closing_balance') ?? 0;

            // ✅ If customer
        } elseif ($voucher->type === 'customer') {
            $party = DB::table('customers')->where('id', $voucher->party_id)->first();
            $previousBalance = DB::table('customer_ledgers')
                ->where('customer_id', $voucher->party_id)
                ->orderByDesc('id')
                ->value('closing_balance') ?? 0;

            // ✅ If walkin
        } elseif ($voucher->type === 'walkin') {
            $party = DB::table('customers')
                ->where('id', $voucher->party_id)
                ->where('customer_type', 'Walking Customer')
                ->first();
        }

        $branch = null;
        if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->branch_id) {
            $branch = DB::table('branches')->where('id', \Illuminate\Support\Facades\Auth::user()->branch_id)->first();
        }

        return view('admin_panel.vochers.print', compact('voucher', 'rows', 'party', 'previousBalance', 'branch'));
    }

     public function getAccountsByHead(Request $request, $headId)
    {
        // ✅ BRANCH-AWARE ACCOUNTS: Simple users see only their branch's accounts
        $isSuper = Auth::check() && Auth::user()->hasRole('super admin');
        
        // If super admin and a specific branch is requested via AJAX
        if ($isSuper && $request->has('branch_id') && $request->branch_id != '') {
            $branchId = $request->branch_id;
        } else {
            $branchId = Auth::check() ? Auth::user()->branch_id : null;
        }
        
        $accounts = Account::where('head_id', $headId)
            ->where('status', 1)
            ->when(!$isSuper && $branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            // If Super Admin selected a specific branch
            ->when($isSuper && $request->has('branch_id') && $request->branch_id != '', function ($q) use ($branchId) {
                 $q->where('branch_id', $branchId);
            })
            ->get();
        
        return response()->json($accounts);
    }
public function getOpeningBalance($type, $id)
{
    if ($type === 'customer' || $type === 'walkin') {
        $customer = Customer::find($id);
        echo "<pre>";
        print_r($customer);
        echo "<pre>";
        dd();
        return response()->json([
            'opening_balance' => $customer->opening_balance ?? 0
        ]);
    }

    // Account case
    $account = AccountHead::find($id);
    return response()->json([
        'opening_balance' => $account->opening_balance ?? 0
    ]);
}




    /**
     * ✅ BRANCH-AWARE: Fetch party list for Receipt Voucher based on type
     * Returns customers, walkin customers, or vendors with their current balances
     */
    public function getReceiptPartyList(Request $request)
    {
        $type = strtolower($request->query('type', ''));
        $isSuper = Auth::check() && Auth::user()->hasRole('super admin');
        
        if ($isSuper && $request->has('branch_id') && $request->branch_id != '') {
            $branchId = $request->branch_id;
        } else {
            $branchId = Auth::check() ? Auth::user()->branch_id : null;
        }

        if ($type === 'customer') {
            $query = Customer::with(['ledgers' => fn($q) => $q->latest()])
                ->whereIn('customer_type', ['credit', 'cash']);

            // Branch filter
            if (!$isSuper && $branchId) {
                $query->where('branch_id', $branchId);
            }
            if ($isSuper && $request->has('branch_id') && $request->branch_id != '') {
                $query->where('branch_id', $branchId);
            }

            $customers = $query->get()->map(function ($c) {
                $ledger = $c->ledgers->first();
                $closing = $ledger ? (float)$ledger->closing_balance : (float)($c->opening_balance ?? 0);
                return [
                    'id'              => $c->id,
                    'text'            => $c->customer_name,
                    'mobile'          => $c->mobile ?? '',
                    'closing_balance' => $closing,
                ];
            });
            return response()->json($customers);

        } elseif ($type === 'walkin') {
            $query = Customer::where('customer_type', 'walking');

            if (!$isSuper && $branchId) {
                $query->where('branch_id', $branchId);
            }
            if ($isSuper && $request->has('branch_id') && $request->branch_id != '') {
                $query->where('branch_id', $branchId);
            }

            $walkins = $query->get()->map(function ($c) {
                $closing = (float)($c->opening_balance ?? 0);
                return [
                    'id'              => $c->id,
                    'text'            => $c->customer_name,
                    'mobile'          => $c->mobile ?? '',
                    'closing_balance' => $closing,
                ];
            });
            return response()->json($walkins);

        } elseif ($type === 'vendor') {
            $query = \App\Models\Vendor::query();
            if (!$isSuper && $branchId) {
                $query->where('branch_id', $branchId);
            }
            if ($isSuper && $request->has('branch_id') && $request->branch_id != '') {
                $query->where('branch_id', $branchId);
            }

            $vendors = $query->get()->map(function ($v) use ($branchId) {
                $lastLedger = \App\Models\VendorLedger::where('vendor_id', $v->id)
                    ->where('branch_id', $branchId ?? $v->branch_id)
                    ->orderByDesc('id')
                    ->first();
                $closing = $lastLedger ? (float)$lastLedger->closing_balance : (float)($v->opening_balance ?? 0);
                return [
                    'id'              => $v->id,
                    'text'            => $v->name,
                    'mobile'          => $v->phone ?? $v->contact ?? '',
                    'closing_balance' => $closing,
                ];
            });
            return response()->json($vendors);
        }

        return response()->json([]);
    }


    public function recepit_vochers()
    {
        $narrations = \App\Models\Narration::where('expense_head', 'Receipts Voucher')
            ->pluck('narration', 'id');
        $AccountHeads = AccountHead::get();

        // Check if user is super admin
        $isSuperAdmin = Auth::user()->hasRole('super admin');
        $currentBranch = Auth::user()->branch_id;
        $Branch = \App\Models\Branch::all();

        // Last RVID nikalna
        $lastVoucher = \App\Models\ReceiptsVoucher::latest('id')->first();

        // Next ID generate karna
        $nextId = $lastVoucher ? $lastVoucher->id + 1 : 1;
        $nextRvid = 'RVID-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        return view('admin_panel.vochers.reciepts_vouchers', compact('narrations', 'AccountHeads', 'nextRvid', 'isSuperAdmin', 'currentBranch', 'Branch'));
    }


    public function store_rec_vochers(Request $request)
    {
        DB::beginTransaction();
        try {
            // 1. Generate RVID
            $rvid = $request->rvid ?: \App\Models\ReceiptsVoucher::generateRVID(auth()->id());
            
            // 2. Process Narrations (Handle 'Add New' case)
            $narrationIds = [];
            if ($request->narration_id) {
                foreach ($request->narration_id as $index => $narrId) {
                    $manualText = $request->narration_text[$index] ?? null;
                    if (empty($narrId) && !empty($manualText)) {
                        $newNar = \App\Models\Narration::create([
                            'expense_head' => 'Receipts Voucher',
                            'narration'    => $manualText,
                        ]);
                        $narrationIds[] = (string)$newNar->id;
                    } else {
                        $narrationIds[] = (string)$narrId;
                    }
                }
            }

            // 3. Save Receipt Voucher Record
            $remarks = $request->remarks;
            if ($request->vendor_type === 'walkin' && !empty($request->walking_customer_name)) {
                $remarks = 'Walk-in Name: ' . $request->walking_customer_name . ($remarks ? ' - ' . $remarks : '');
            }

            // Get proper branch_id from request or auth
            if (Auth::user()->hasRole('super admin') && $request->has('branch_id') && $request->branch_id != '') {
                $branchId = (int) $request->branch_id;
            } else {
                $branchId = (int) (auth()->user()->branch_id ?? 1);
            }

            $voucherData = [
                'branch_id'        => $branchId,
                'rvid'             => $rvid,
                'receipt_date'     => $request->receipt_date ?? now()->toDateString(),
                'entry_date'       => $request->entry_date ?? now()->toDateString(),
                'type'             => $request->vendor_type,
                'party_id'         => $request->vendor_id,
                'tel'              => $request->tel,
                'remarks'          => $remarks,
                'narration_id'     => json_encode($narrationIds),
                'reference_no'     => json_encode($request->reference_no),
                'row_account_head' => json_encode($request->row_account_head),
                'row_account_id'   => json_encode($request->row_account_id),
                'discount_value'   => json_encode($request->discount_value),
                'rate'             => json_encode($request->rate),
                'amount'           => json_encode($request->amount),
                'total_amount'     => (float)$request->total_amount,
                'processed'        => true,
            ];

            $savedVoucher = ReceiptsVoucher::create($voucherData);
            $totalAmount = (float)$request->total_amount;

            /**
             * 4. PARTY SIDE POSTING (CREDIT)
             * Receipt from customer/vendor decreases their receivable balance.
             */
            if ($request->vendor_type === 'customer') {
                // Get latest ledger entry to calculate accurate closing balance
                $lastLedger = CustomerLedger::where('customer_id', $request->vendor_id)
                    ->orderBy('id', 'desc')
                    ->first();

                $customer = \App\Models\Customer::find($request->vendor_id);
                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($customer->opening_balance ?? 0);
                
                CustomerLedger::create([
                    'customer_id'      => $request->vendor_id,
                    'admin_or_user_id' => auth()->id(),
                    'previous_balance' => $previousBalance,
                    'opening_balance'  => $customer->opening_balance ?? 0,
                    'total_debit'      => 0,
                    'total_credit'     => $totalAmount,
                    'closing_balance'  => $previousBalance - $totalAmount,
                ]);

            } elseif ($request->vendor_type === 'vendor') {
                $lastLedger = VendorLedger::where('vendor_id', $request->vendor_id)
                    ->where('branch_id', $branchId)
                    ->orderBy('id', 'desc')
                    ->first();

                $vendor = \App\Models\Vendor::find($request->vendor_id);
                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($vendor->opening_balance ?? 0);

                VendorLedger::create([
                    'vendor_id'        => $request->vendor_id,
                    'branch_id'        => $branchId,
                    'admin_or_user_id' => auth()->id(),
                    'transaction_date' => $request->receipt_date ?? now()->toDateString(),
                    'description'      => "Receipt Voucher #$rvid",
                    'opening_balance'  => $vendor->opening_balance ?? 0,
                    'previous_balance' => $previousBalance,
                    'debit_amount'     => 0,
                    'credit_amount'    => $totalAmount, // Receiving from vendor (e.g. refund) credits them
                    'closing_balance'  => $previousBalance + $totalAmount,
                ]);

            } elseif (is_numeric($request->vendor_type)) {
                // Direct GL Account Head case
                $account = Account::find($request->vendor_id);
                if ($account) {
                    $this->postLedgerEntry(
                        $account->id, 
                        'receipt', 
                        $rvid, 
                        $savedVoucher->id, 
                        $request->receipt_date ?? now()->toDateString(), 
                        "Receipt Voucher Party Side: " . ($request->remarks ?? 'N/A'), 
                        0, 
                        $totalAmount
                    );
                    // Update account balance
                    $account->opening_balance -= $totalAmount; // Credit increases/decreases based on type, helper handles ledger
                    $account->save();
                }
            }

            /**
             * 5. ACCOUNT SIDE POSTING (DEBIT)
             * Receiving funds into bank/cash accounts increases their balance.
             */
            if ($request->row_account_id && $request->amount) {
                foreach ($request->row_account_id as $index => $accId) {
                    $rowAmount = (float)($request->amount[$index] ?? 0);
                    if ($rowAmount <= 0 || !$accId) continue;

                    $rowAccount = Account::find($accId);
                    if ($rowAccount) {
                        // Update Account Balance (Debit increases asset accounts)
                        if (trim(strtolower($rowAccount->type)) === 'debit') {
                            $rowAccount->opening_balance += $rowAmount;
                        } else {
                            $rowAccount->opening_balance -= $rowAmount;
                        }
                        $rowAccount->save();

                        // Post to GL Ledger
                        $this->postLedgerEntry(
                            $accId, 
                            'receipt', 
                            $rvid, 
                            $savedVoucher->id, 
                            $request->receipt_date ?? now()->toDateString(), 
                            "Receipt from Party: " . ($request->remarks ?? 'Voucher #' . $rvid), 
                            $rowAmount, 
                            0
                        );
                    }
                }
            }

            DB::commit();
            return back()->with('success', "Receipt Voucher #$rvid saved successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Receipt Voucher Store Error: " . $e->getMessage());
            return back()->with('error', "Error saving voucher: " . $e->getMessage());
        }
    }

    public function destroy_receipt($id)
    {
        $voucher = ReceiptsVoucher::findOrFail($id);
        
        if ($voucher->status === 'voided') {
            return back()->with('error', 'Voucher is already voided!');
        }
        
        $success = \App\Services\VoucherService::reverseReceiptVoucher($voucher, auth()->id());
        
        if ($success) {
            return back()->with('success', 'Receipt Voucher voided successfully! Ledgers reversed.');
        } else {
            return back()->with('error', 'Failed to void Receipt Voucher.');
        }
    }

    public function edit_receipt($id)
    {
        // Edit logic can be added here
        return back()->with('error', 'Edit feature is under construction for strict ERP mode. Please void and create a new voucher.');
    }

    public function update_receipt(Request $request, $id)
    {
        // Update logic
    }

    public function Payment_vochers()
    {
        $narrations = \App\Models\Narration::where('expense_head', 'Payment voucher')
            ->pluck('narration', 'id');
        $AccountHeads = AccountHead::get();
        // echo"<pre>";
        // print_r($AccountHeads);
        // echo"</pre>";
        // dd();

        // Last RVID nikalna
        $lastVoucher = \App\Models\PaymentVoucher::latest('id')->first();

        // Next ID generate karna
        $nextId = $lastVoucher ? $lastVoucher->id + 1 : 1;
        $nextPVID = 'PVID-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        return view('admin_panel.vochers.payment_vochers.payment_vouchers', compact('narrations', 'AccountHeads', 'nextPVID'));
    }

    public function store_Pay_vochers(Request $request)
    {
            // echo "<pre>";
            // print_r($request->all());
            // dd();
        DB::beginTransaction();
        try {
            $pvid = PaymentVoucher::generateInvoiceNo();
            $narrationIds = [];

            foreach ($request->narration_id as $index => $narrId) {
                $manualText = $request->narration_text[$index] ?? null;
                $manualType = $request->narration_type_text[$index] ?? 'Manual';

                if (empty($narrId) && !empty($manualText)) {
                    // Auto expense_head set based on voucher type
                    $expenseHead = 'Payment voucher';
                    if (stripos($manualType, 'Receipt') !== false || $request->voucher_type == 'receipt') {
                        $expenseHead = 'Payment voucher';
                    }

                    $new = \App\Models\Narration::create([
                        'expense_head' => $expenseHead,
                        'narration'    => $manualText,
                    ]);

                    $narrationIds[] = (string)$new->id; // store as string → ["7"]
                } else {
                    $narrationIds[] = (string)$narrId; // force string format
                }
            }
            $voucherData = [
                'pvid'             => $pvid,
                'receipt_date'     => $request->receipt_date,
                'entry_date'       => $request->entry_date,
                'type'             => $request->vendor_type,
                'party_id'         => $request->vendor_id,
                'tel'              => $request->tel,
                'remarks'          => $request->remarks,
                'narration_id' => json_encode($narrationIds),
                'reference_no'     => json_encode($request->reference_no),
                'row_account_head' => json_encode($request->row_account_head),
                'row_account_id'   => json_encode($request->row_account_id),
                'discount_value'   => json_encode($request->discount_value),
                // 'kg'               => json_encode($request->kg),
                // 'rate'             => json_encode($request->rate),
                'amount'           => json_encode($request->amount),
                'total_amount'     => $request->total_amount,
            ];

            PaymentVoucher::create($voucherData);

            $amount = (float)$request->total_amount;
            /**
             * STEP 1: Row accounts → MINUS (opposite of receipt voucher)
             */
            if ($request->row_account_id && $request->amount) {
                foreach ($request->row_account_id as $index => $accId) {
                    $rowAmount = isset($request->amount[$index]) ? (float)$request->amount[$index] : 0;

                    if ($rowAmount > 0) {
                        $rowAccount = Account::find($accId);
                        if ($rowAccount) {
                            $rowAccount->opening_balance = $rowAccount->opening_balance - $rowAmount;
                            $rowAccount->save();
                        }
                    }
                }
            }

            /**
             * STEP 2: Party side (Vendor / Customer / Account Head) → PLUS
             */
            if ($request->vendor_type === 'vendor') {
                $branchId = auth()->user()->branch_id ?? 0;
                $lastLedger = VendorLedger::where('vendor_id', $request->vendor_id)
                    ->where('branch_id', $branchId)
                    ->orderBy('id', 'desc')
                    ->first();

                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)(\App\Models\Vendor::find($request->vendor_id)->opening_balance ?? 0);

                VendorLedger::create([
                    'vendor_id'        => $request->vendor_id,
                    'branch_id'        => $branchId,
                    'admin_or_user_id' => auth()->id(),
                    'transaction_date' => now(),
                    'description'      => "Payment Voucher #$pvid",
                    'opening_balance'  => $previousBalance,
                    'previous_balance' => $previousBalance,
                    'debit_amount'     => $amount,
                    'credit_amount'    => 0,
                    'closing_balance'  => $previousBalance - $amount,
                ]);
            } elseif ($request->vendor_type === 'customer') {
                $lastLedger = CustomerLedger::where('customer_id', $request->vendor_id)->orderBy('id', 'desc')->first();
                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)(\App\Models\Customer::find($request->vendor_id)->opening_balance ?? 0);

                CustomerLedger::create([
                    'customer_id'      => $request->vendor_id,
                    'admin_or_user_id' => auth()->id(),
                    'previous_balance' => $previousBalance,
                    'opening_balance'  => $previousBalance,
                    'closing_balance'  => $previousBalance + $amount,
                ]);
            } else {
                // agar vendor_type me account head/account ki id ayi
                $account = Account::find($request->vendor_id);
                if ($account) {
                    $account->opening_balance = $account->opening_balance + $amount;
                    $account->save();
                }
            }

            // ✅ POST TO ACCOUNT LEDGER (Payment = CREDIT from bank/cash accounts)
            $savedPVoucher = PaymentVoucher::latest('id')->first();
            if ($request->row_account_id && $request->amount) {
                foreach ($request->row_account_id as $index => $accId) {
                    $rowAmount = (float)($request->amount[$index] ?? 0);
                    if ($rowAmount <= 0 || !$accId) continue;
                    $this->postLedgerEntry($accId, 'payment', $pvid, $savedPVoucher?->id, $request->receipt_date ?? now()->toDateString(), $request->remarks, 0, $rowAmount);
                }
            }

            DB::commit();
            return back()->with('success', 'Payment Voucher saved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy_payment($id)
    {
        $voucher = PaymentVoucher::findOrFail($id);
        
        if ($voucher->status === 'voided') {
            return back()->with('error', 'Voucher is already voided!');
        }
        
        $success = \App\Services\VoucherService::reversePaymentVoucher($voucher, auth()->id());
        
        if ($success) {
            return back()->with('success', 'Payment Voucher voided successfully! Ledgers reversed.');
        } else {
            return back()->with('error', 'Failed to void Payment Voucher.');
        }
    }

    public function edit_payment($id)
    {
        return back()->with('error', 'Edit feature is under construction for strict ERP mode. Please void and create a new voucher.');
    }

    public function update_payment(Request $request, $id)
    {
        // Update logic
    }
    
     public function all_Payment_vochers()
    {
        $receipts = \App\Models\PaymentVoucher::orderBy('id', 'DESC')->get();

        foreach ($receipts as $voucher) {
            $partyName = '-';
            $typeLabel = '-';

            // 🧩 If type is numeric → Account Head / Account
            if (is_numeric($voucher->type)) {
                $accountHead = DB::table('account_heads')->where('id', $voucher->type)->first();
                $account = DB::table('accounts')->where('id', $voucher->party_id)->first();

                $typeLabel = $accountHead->name ?? 'Account';
                $partyName = $account->title ?? '-';
            } elseif ($voucher->type === 'vendor') {
                $vendor = DB::table('vendors')->where('id', $voucher->party_id)->first();
                $typeLabel = 'Vendor';
                $partyName = $vendor->name ?? '-';
            } elseif ($voucher->type === 'customer') {
                $customer = DB::table('customers')->where('id', $voucher->party_id)->first();
                $typeLabel = 'Customer';
                $partyName = $customer->customer_name ?? '-';
            } elseif ($voucher->type === 'walkin') {
                $walkin = DB::table('customers')
                    ->where('id', $voucher->party_id)
                    ->where('customer_type', 'Walking Customer')
                    ->first();
                $typeLabel = 'Walk-in';
                $partyName = $walkin->customer_name ?? '-';
            }

            // Attach extra fields for Blade
            $voucher->type_label = $typeLabel;
            $voucher->party_name = $partyName;
        }

        return view('admin_panel.vochers.payment_vochers.all_payment_vochers', compact('receipts'));
    }

    public function uploadProof(Request $request, $id)
    {
        $request->validate([
            'receiving_proof' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $voucher = \App\Models\PaymentVoucher::findOrFail($id);

        if ($request->hasFile('receiving_proof')) {
            if ($voucher->receiving_proof) {
                $oldPath = public_path('uploads/receipts/' . $voucher->receiving_proof);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $file = $request->file('receiving_proof');
            $filename = 'proof_' . $voucher->pvid . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Ensure destination directory exists
            $destinationPath = public_path('uploads/receipts');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);

            $voucher->receiving_proof = $filename;
            $voucher->save();

            return back()->with('success', 'Receiving proof uploaded successfully.');
        }

        return back()->with('error', 'Failed to upload receiving proof.');
    }

    public function Paymentprint($id)
    {
        $voucher = \App\Models\PaymentVoucher::findOrFail($id);

        // Decode JSON arrays
        $narrations = json_decode($voucher->narration_id, true) ?? [];
        $references = json_decode($voucher->reference_no, true) ?? [];
        $accountHeads = json_decode($voucher->row_account_head, true) ?? [];
        $accounts = json_decode($voucher->row_account_id, true) ?? [];
        $amounts = json_decode($voucher->amount, true) ?? [];

        // 🧾 Build detailed rows
        $rows = [];
        foreach ($accounts as $index => $accountId) {
            $narrId = $narrations[$index] ?? null;
            $narration = DB::table('narrations')->where('id', $narrId)->value('narration');
            $ref = $references[$index] ?? null;
            $accountHead = DB::table('account_heads')->where('id', $accountHeads[$index] ?? null)->value('name');
            $account = DB::table('accounts')->where('id', $accountId)->first();
            $amount = (float)($amounts[$index] ?? 0);

            $rows[] = [
                'narration' => $narration,
                'reference' => $ref,
                'account_head' => $accountHead,
                'account_name' => $account->title ?? null,
                'account_code' => $account->account_code ?? null,
                'amount' => $amount,
            ];
        }

        // 🧩 Party setup — dynamic based on type
        $party = null;
        $previousBalance = 0;

        // ✅ Account Head type (numeric)
        if (is_numeric($voucher->type)) {
            $accountHead = DB::table('account_heads')->where('id', $voucher->type)->first();
            $account = DB::table('accounts')->where('id', $voucher->party_id)->first();

            if ($account) {
                $party = (object)[
                    'name' => $account->title ?? '—',
                    'address' => '—',
                    'phone' => $account->account_code ?? '—',
                    'head_name' => $accountHead->name ?? '—',
                ];
            }

            $previousBalance = $account->opening_balance ?? 0;

            // ✅ Vendor
        } elseif ($voucher->type === 'vendor') {
            $party = DB::table('vendors')->where('id', $voucher->party_id)->first();
            $previousBalance = DB::table('vendor_ledgers')
                ->where('vendor_id', $voucher->party_id)
                ->orderByDesc('id')
                ->value('closing_balance') ?? 0;

            // ✅ Customer
        } elseif ($voucher->type === 'customer') {
            $party = DB::table('customers')->where('id', $voucher->party_id)->first();
            $previousBalance = DB::table('customer_ledgers')
                ->where('customer_id', $voucher->party_id)
                ->orderByDesc('id')
                ->value('closing_balance') ?? 0;

            // ✅ Walking customer
        } elseif ($voucher->type === 'walkin') {
            $party = DB::table('customers')
                ->where('id', $voucher->party_id)
                ->where('customer_type', 'Walking Customer')
                ->first();
        }

        $branch = null;
        if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->branch_id) {
            $branch = DB::table('branches')->where('id', \Illuminate\Support\Facades\Auth::user()->branch_id)->first();
        }

        return view('admin_panel.vochers.payment_vochers.print', compact('voucher', 'rows', 'party', 'previousBalance', 'branch'));
    }


    public function expense_vochers()
    {
        $narrations = \App\Models\Narration::where('expense_head', 'Expense voucher')
            ->pluck('narration', 'id');
        $AccountHeads = AccountHead::get();

        // Last RVID nikalna
        $lastVoucher = \App\Models\ExpenseVoucher::latest('id')->first();

        // Next ID generate karna
        $nextId = $lastVoucher ? $lastVoucher->id + 1 : 1;
        $nextRvid = 'EVID-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        return view('admin_panel.vochers.expense_vochers.expense_vouchers', compact('narrations', 'AccountHeads', 'nextRvid'));
    }

    public function store_expense_vochers(Request $request)
    {
        DB::beginTransaction();
        try {
            $evid = ExpenseVoucher::generateInvoiceNo();
            $narrationIds = [];

            foreach ($request->narration_id as $index => $narrId) {
                $manualText = $request->narration_text[$index] ?? null;
                $manualType = $request->narration_type_text[$index] ?? 'Manual';

                if (empty($narrId) && !empty($manualText)) {
                    // Auto expense_head set based on voucher type
                    $expenseHead = 'Expense voucher';
                    if (stripos($manualType, 'Receipt') !== false || $request->voucher_type == 'receipt') {
                        $expenseHead = 'Expense voucher';
                    }

                    $new = \App\Models\Narration::create([
                        'expense_head' => $expenseHead,
                        'narration'    => $manualText,
                    ]);

                    $narrationIds[] = (string)$new->id; // store as string → ["7"]
                } else {
                    $narrationIds[] = (string)$narrId; // force string format
                }
            }
            $voucherData = [
                'evid'             => $evid,
                'entry_date'       => $request->entry_date,
                'type'             => $request->vendor_type,
                'party_id'         => $request->vendor_id,
                'tel'              => $request->tel,
                'remarks'          => $request->remarks,
                'narration_id' => json_encode($narrationIds),
                'row_account_head' => json_encode($request->row_account_head),
                'row_account_id'   => json_encode($request->row_account_id),
                'amount'           => json_encode($request->amount),
                'total_amount'     => $request->total_amount,
            ];

            ExpenseVoucher::create($voucherData);

            $amount = (float)$request->total_amount;

            /**
             * STEP 1: Expense Accounts (row side) → PLUS
             */
            if ($request->row_account_id && $request->amount) {
                foreach ($request->row_account_id as $index => $accId) {
                    $rowAmount = isset($request->amount[$index]) ? (float)$request->amount[$index] : 0;

                    if ($rowAmount > 0) {
                        $rowAccount = Account::find($accId);
                        if ($rowAccount) {
                            $rowAccount->opening_balance = $rowAccount->opening_balance + $rowAmount; // PLUS
                            $rowAccount->save();
                        }
                    }
                }
            }

            /**
             * STEP 2: Party side → MINUS
             */
            if ($request->vendor_type === 'vendor') {
                $branchId = auth()->user()->branch_id ?? 0;
                $lastLedger = VendorLedger::where('vendor_id', $request->vendor_id)
                    ->where('branch_id', $branchId)
                    ->orderBy('id', 'desc')
                    ->first();

                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)(\App\Models\Vendor::find($request->vendor_id)->opening_balance ?? 0);

                VendorLedger::create([
                    'vendor_id'        => $request->vendor_id,
                    'branch_id'        => $branchId,
                    'admin_or_user_id' => auth()->id(),
                    'transaction_date' => now(),
                    'description'      => "Expense Voucher #$evid",
                    'opening_balance'  => $previousBalance,
                    'previous_balance' => $previousBalance,
                    'debit_amount'     => 0,
                    'credit_amount'    => $amount,
                    'closing_balance'  => $previousBalance + $amount,
                ]);
            } elseif ($request->vendor_type === 'customer') {
                $lastLedger = CustomerLedger::where('customer_id', $request->vendor_id)->orderBy('id', 'desc')->first();
                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)(\App\Models\Customer::find($request->vendor_id)->opening_balance ?? 0);

                CustomerLedger::create([
                    'customer_id'      => $request->vendor_id,
                    'admin_or_user_id' => auth()->id(),
                    'previous_balance' => $previousBalance,
                    'opening_balance'  => $previousBalance,
                    'closing_balance'  => $previousBalance + $amount,
                ]);
            } else {
                // yahan vendor_type numeric (1,2,3) hai → matlab Account ID
                $account = Account::find($request->vendor_id);
                if ($account) {
                    $account->opening_balance = $account->opening_balance - $amount; // MINUS
                    $account->save();
                }
            }

            // ✅ POST TO ACCOUNT LEDGER (Expense = DEBIT to expense accounts, ROW side)
            $savedEVoucher = ExpenseVoucher::latest('id')->first();
            if ($request->row_account_id && $request->amount) {
                foreach ($request->row_account_id as $index => $accId) {
                    $rowAmount = (float)($request->amount[$index] ?? 0);
                    if ($rowAmount <= 0 || !$accId) continue;
                    $this->postLedgerEntry($accId, 'expense', $evid, $savedEVoucher?->id, $request->entry_date ?? now()->toDateString(), $request->remarks, $rowAmount, 0);
                }
            }

            DB::commit();
            return back()->with('success', 'Expense Voucher saved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function all_expense_vochers()
    {
        $receipts = \App\Models\ExpenseVoucher::orderBy('id', 'DESC')->get();

        foreach ($receipts as $voucher) {
            $partyName = '-';
            $typeLabel = '-';

            // 🧩 If type is numeric → Account Head / Account
            if (is_numeric($voucher->type)) {
                $accountHead = DB::table('account_heads')->where('id', $voucher->type)->first();
                $account = DB::table('accounts')->where('id', $voucher->party_id)->first();

                $typeLabel = $accountHead->name ?? 'Account';
                $partyName = $account->title ?? '-';
            } elseif ($voucher->type === 'vendor') {
                $vendor = DB::table('vendors')->where('id', $voucher->party_id)->first();
                $typeLabel = 'Vendor';
                $partyName = $vendor->name ?? '-';
            } elseif ($voucher->type === 'customer') {
                $customer = DB::table('customers')->where('id', $voucher->party_id)->first();
                $typeLabel = 'Customer';
                $partyName = $customer->customer_name ?? '-';
            } elseif ($voucher->type === 'walkin') {
                $walkin = DB::table('customers')
                    ->where('id', $voucher->party_id)
                    ->where('customer_type', 'Walking Customer')
                    ->first();
                $typeLabel = 'Walk-in';
                $partyName = $walkin->customer_name ?? '-';
            }

            // 🔗 Attach extra fields for Blade
            $voucher->type_label = $typeLabel;
            $voucher->party_name = $partyName;
        }

        return view('admin_panel.vochers.expense_vochers.all_expense_vochers', compact('receipts'));
    }



    public function expenseprint($id)
    {
        $voucher = \App\Models\ExpenseVoucher::findOrFail($id);

        // Decode JSON arrays safely
        $narrations = json_decode($voucher->narration_id, true) ?? [];
        $references = json_decode($voucher->reference_no, true) ?? [];
        $accountHeads = json_decode($voucher->row_account_head, true) ?? [];
        $accounts = json_decode($voucher->row_account_id, true) ?? [];
        $amounts = json_decode($voucher->amount, true) ?? [];

        // 🧾 Prepare detailed rows
        $rows = [];
        foreach ($accounts as $index => $accountId) {
            $narrId = $narrations[$index] ?? null;
            $narration = DB::table('narrations')->where('id', $narrId)->value('narration');
            $ref = $references[$index] ?? null;
            $accountHead = DB::table('account_heads')->where('id', $accountHeads[$index] ?? null)->value('name');
            $account = DB::table('accounts')->where('id', $accountId)->first();
            $amount = (float)($amounts[$index] ?? 0);

            $rows[] = [
                'narration' => $narration,
                'reference' => $ref,
                'account_head' => $accountHead,
                'account_name' => $account->title ?? null,
                'account_code' => $account->account_code ?? null,
                'amount' => $amount,
            ];
        }

        // 🧩 Party Setup Based on Type
        $party = null;
        $previousBalance = 0;

        if (is_numeric($voucher->type)) {
            // ✅ Account Head type (numeric)
            $accountHead = DB::table('account_heads')->where('id', $voucher->type)->first();
            $account = DB::table('accounts')->where('id', $voucher->party_id)->first();

            if ($account) {
                $party = (object)[
                    'name' => $account->title ?? '—',
                    'address' => '—',
                    'phone' => $account->account_code ?? '—',
                    'head_name' => $accountHead->name ?? '—',
                ];
            }

            $previousBalance = $account->opening_balance ?? 0;
        } elseif ($voucher->type === 'vendor') {
            // ✅ Vendor Type
            $party = DB::table('vendors')->where('id', $voucher->party_id)->first();
            $previousBalance = DB::table('vendor_ledgers')
                ->where('vendor_id', $voucher->party_id)
                ->orderByDesc('id')
                ->value('closing_balance') ?? 0;
        } elseif ($voucher->type === 'customer') {
            // ✅ Customer Type
            $party = DB::table('customers')->where('id', $voucher->party_id)->first();
            $previousBalance = DB::table('customer_ledgers')
                ->where('customer_id', $voucher->party_id)
                ->orderByDesc('id')
                ->value('closing_balance') ?? 0;
        } elseif ($voucher->type === 'walkin') {
            // ✅ Walking Customer
            $party = DB::table('customers')
                ->where('id', $voucher->party_id)
                ->where('customer_type', 'Walking Customer')
                ->first();
        }

        $branch = null;
        if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->branch_id) {
            $branch = DB::table('branches')->where('id', \Illuminate\Support\Facades\Auth::user()->branch_id)->first();
        }

        return view('admin_panel.vochers.expense_vochers.print', compact('voucher', 'rows', 'party', 'previousBalance', 'branch'));
    }

    // =========================================================================
    // ✅ ERP STANDARD: Account Ledger Entry Posting Helper
    // Called after every voucher save to log transactions into account_ledger_entries
    // =========================================================================
    private function postLedgerEntry(
        int    $accountId,
        string $voucherType,
        string $voucherNo,
        ?int   $voucherId,
        string $date,
        ?string $description,
        float  $debit,
        float  $credit
    ): void {
        $account = \App\Models\Account::find($accountId);
        if (!$account) return;

        // Get last running balance for this account
        $lastEntry = \App\Models\AccountLedgerEntry::where('account_id', $accountId)
            ->orderByDesc('id')
            ->first();

        if ($lastEntry) {
            $previousBalance = (float)$lastEntry->running_balance;
        } else {
            // First ever entry — use account's opening_balance as base
            $openingBalance = (float)($account->opening_balance ?? 0);

            if ($openingBalance != 0) {
                // Post the opening balance as OB entry first
                $obEntryNo = \App\Models\AccountLedgerEntry::generateEntryNo($accountId, 'opening_balance');
                \App\Models\AccountLedgerEntry::create([
                    'account_id'        => $accountId,
                    'branch_id'         => $account->branch_id,
                    'voucher_type'      => 'opening_balance',
                    'voucher_no'        => null,
                    'voucher_id'        => null,
                    'entry_no'          => $obEntryNo,
                    'transaction_date'  => now()->toDateString(),
                    'description'       => 'Opening Balance',
                    'debit'             => $openingBalance >= 0 ? $openingBalance : 0,
                    'credit'            => $openingBalance < 0 ? abs($openingBalance) : 0,
                    'running_balance'   => $openingBalance,
                    'created_by'        => auth()->id(),
                ]);
            }

            $previousBalance = $openingBalance;
        }

        // Calculate new running balance
        $newBalance = $previousBalance + $debit - $credit;

        // Generate sequential entry number (BR-1, CR-1, JV-1 etc.)
        $entryNo = \App\Models\AccountLedgerEntry::generateEntryNo($accountId, $voucherType);

        \App\Models\AccountLedgerEntry::create([
            'account_id'        => $accountId,
            'branch_id'         => $account->branch_id,
            'voucher_type'      => $voucherType,
            'voucher_no'        => $voucherNo,
            'voucher_id'        => $voucherId,
            'entry_no'          => $entryNo,
            'transaction_date'  => $date,
            'description'       => $description ?? ucfirst($voucherType) . ' Voucher #' . $voucherNo,
            'debit'             => $debit,
            'credit'            => $credit,
            'running_balance'   => $newBalance,
            'created_by'        => auth()->id(),
        ]);
    }

    public function storeNarrationAjax(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'narration' => 'required|string|max:255'
        ]);

        $narration = \App\Models\Narration::create([
            'expense_head' => 'Receipts Voucher',
            'narration'    => $request->narration,
        ]);

        return response()->json([
            'success' => true,
            'id' => $narration->id,
            'text' => $narration->narration
        ]);
    }

    /* =====================================================================
     * ██╗ ██╗ ██╗ ██╗ ██╗██╗    ██╗   ██╗ ██████╗ ██╗   ██╗ ██████╗██╗  ██╗███████╗██████╗
     * ██║██╔╝██╔╝██╔╝██╔╝██║    ██║   ██║██╔═══██╗██║   ██║██╔════╝██║  ██║██╔════╝██╔══██╗
     * JOURNAL VOUCHER (JV) — Double Entry: Customer Credit → Vendor Debit
     * ===================================================================== */

    /**
     * Show Journal Voucher creation form.
     */
    public function journal_voucher_create()
    {
        $narrations    = \App\Models\Narration::where('expense_head', 'Journal Voucher')->pluck('narration', 'id');
        $AccountHeads  = AccountHead::get();
        $isSuperAdmin  = Auth::user()->hasRole('super admin');
        $currentBranch = Auth::user()->branch_id;
        $Branch        = \App\Models\Branch::all();

        $lastId    = (int) JournalVoucher::withTrashed()->max('id');
        $nextJVID  = 'JVID-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);

        return view('admin_panel.vochers.journal_vouchers.create', compact(
            'narrations', 'AccountHeads', 'nextJVID', 'isSuperAdmin', 'currentBranch', 'Branch'
        ));
    }

    /**
     * Store Journal Voucher — posts double-entry to Customer & Vendor ledgers.
     */
    public function journal_voucher_store(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'voucher_date'       => 'required|date',
            'debit_party_type'   => 'required|string',
            'debit_party_id'     => 'required',
            'credit_party_type'  => 'required|string',
            'credit_party_id'    => 'required',
            'amount'             => 'required|numeric|min:0.01',
            'remarks'            => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $jvid   = JournalVoucher::generateJVID();
            $amount = (float) $request->amount;

            // Determine branch
            $branchId = (Auth::user()->hasRole('super admin') && $request->filled('branch_id'))
                ? $request->branch_id
                : (Auth::user()->branch_id ?? 0);

            // Handle narration
            $narrationIds = [];
            if ($request->has('narration_id')) {
                foreach ($request->narration_id as $idx => $narrId) {
                    $manualText = $request->narration_text[$idx] ?? null;
                    if (empty($narrId) && !empty($manualText)) {
                        $new = \App\Models\Narration::create([
                            'expense_head' => 'Journal Voucher',
                            'narration'    => $manualText,
                        ]);
                        $narrationIds[] = (string) $new->id;
                    } else {
                        $narrationIds[] = (string) $narrId;
                    }
                }
            }

            // Save Journal Voucher header
            $jv = JournalVoucher::create([
                'jvid'              => $jvid,
                'voucher_date'      => $request->voucher_date,
                'entry_date'        => now()->toDateString(),
                'remarks'           => $request->remarks,
                'branch_id'         => $branchId,
                'created_by'        => Auth::id(),
                'debit_party_type'  => $request->debit_party_type,
                'debit_party_id'    => $request->debit_party_id,
                'credit_party_type' => $request->credit_party_type,
                'credit_party_id'   => $request->credit_party_id,
                'amount'            => $amount,
                'narration_id'      => json_encode($narrationIds),
                'reference_no'      => json_encode($request->reference_no ?? []),
                'status'            => 'posted',
            ]);

            // ─────────────────────────────────────────────────────────────
            // DEBIT SIDE — typically the VENDOR (we are paying/reducing payable)
            // ─────────────────────────────────────────────────────────────
            if ($request->debit_party_type === 'vendor') {
                $lastLedger = VendorLedger::where('vendor_id', $request->debit_party_id)
                    ->where('branch_id', $branchId)
                    ->orderBy('id', 'desc')
                    ->first();

                $vendor          = \App\Models\Vendor::find($request->debit_party_id);
                $previousBalance = $lastLedger
                    ? (float) $lastLedger->closing_balance
                    : (float) ($vendor->opening_balance ?? 0);

                VendorLedger::create([
                    'vendor_id'        => $request->debit_party_id,
                    'branch_id'        => $branchId,
                    'admin_or_user_id' => Auth::id(),
                    'transaction_date' => $request->voucher_date,
                    'description'      => "Journal Voucher #$jvid — Debit",
                    'opening_balance'  => $vendor->opening_balance ?? 0,
                    'previous_balance' => $previousBalance,
                    'debit_amount'     => $amount,   // Debit = Vendor payable reduced
                    'credit_amount'    => 0,
                    'closing_balance'  => $previousBalance - $amount,
                ]);

            } elseif ($request->debit_party_type === 'customer') {
                $lastLedger = CustomerLedger::where('customer_id', $request->debit_party_id)
                    ->orderBy('id', 'desc')
                    ->first();

                $customer        = \App\Models\Customer::find($request->debit_party_id);
                $previousBalance = $lastLedger
                    ? (float) $lastLedger->closing_balance
                    : (float) ($customer->opening_balance ?? 0);

                CustomerLedger::create([
                    'customer_id'      => $request->debit_party_id,
                    'admin_or_user_id' => Auth::id(),
                    'previous_balance' => $previousBalance,
                    'opening_balance'  => $customer->opening_balance ?? 0,
                    'total_debit'      => $amount,
                    'total_credit'     => 0,
                    'closing_balance'  => $previousBalance + $amount, // Debit increases what customer owes
                ]);
            }

            // ─────────────────────────────────────────────────────────────
            // CREDIT SIDE — typically the CUSTOMER (reducing their receivable)
            // ─────────────────────────────────────────────────────────────
            if ($request->credit_party_type === 'customer') {
                $lastLedger = CustomerLedger::where('customer_id', $request->credit_party_id)
                    ->orderBy('id', 'desc')
                    ->first();

                $customer        = \App\Models\Customer::find($request->credit_party_id);
                $previousBalance = $lastLedger
                    ? (float) $lastLedger->closing_balance
                    : (float) ($customer->opening_balance ?? 0);

                CustomerLedger::create([
                    'customer_id'      => $request->credit_party_id,
                    'admin_or_user_id' => Auth::id(),
                    'previous_balance' => $previousBalance,
                    'opening_balance'  => $customer->opening_balance ?? 0,
                    'total_debit'      => 0,
                    'total_credit'     => $amount,
                    'closing_balance'  => $previousBalance - $amount, // Credit reduces receivable
                ]);

            } elseif ($request->credit_party_type === 'vendor') {
                $lastLedger = VendorLedger::where('vendor_id', $request->credit_party_id)
                    ->where('branch_id', $branchId)
                    ->orderBy('id', 'desc')
                    ->first();

                $vendor          = \App\Models\Vendor::find($request->credit_party_id);
                $previousBalance = $lastLedger
                    ? (float) $lastLedger->closing_balance
                    : (float) ($vendor->opening_balance ?? 0);

                VendorLedger::create([
                    'vendor_id'        => $request->credit_party_id,
                    'branch_id'        => $branchId,
                    'admin_or_user_id' => Auth::id(),
                    'transaction_date' => $request->voucher_date,
                    'description'      => "Journal Voucher #$jvid — Credit",
                    'opening_balance'  => $vendor->opening_balance ?? 0,
                    'previous_balance' => $previousBalance,
                    'debit_amount'     => 0,
                    'credit_amount'    => $amount,
                    'closing_balance'  => $previousBalance + $amount,
                ]);
            }

            DB::commit();
            return redirect()->route('journal.vouchers.index')
                ->with('success', "Journal Voucher #$jvid saved successfully! Both ledgers updated.");

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("JV Store Error: " . $e->getMessage());
            return back()->with('error', "Error saving Journal Voucher: " . $e->getMessage());
        }
    }

    /**
     * List all Journal Vouchers.
     */
    public function journal_vouchers_index()
    {
        $isSuperAdmin = Auth::user()->hasRole('super admin');
        $branchId     = Auth::user()->branch_id;

        $query = JournalVoucher::latest();

        if (!$isSuperAdmin && $branchId) {
            $query->where('branch_id', $branchId);
        }

        $vouchers = $query->get()->map(function ($jv) {
            $jv->debit_party_name  = JournalVoucher::resolvePartyName($jv->debit_party_type,  $jv->debit_party_id);
            $jv->credit_party_name = JournalVoucher::resolvePartyName($jv->credit_party_type, $jv->credit_party_id);
            return $jv;
        });

        return view('admin_panel.vochers.journal_vouchers.index', compact('vouchers'));
    }

    /**
     * Print a single Journal Voucher.
     */
    public function journal_voucher_print(int $id)
    {
        $jv = JournalVoucher::findOrFail($id);

        $debitPartyName  = JournalVoucher::resolvePartyName($jv->debit_party_type,  $jv->debit_party_id);
        $creditPartyName = JournalVoucher::resolvePartyName($jv->credit_party_type, $jv->credit_party_id);

        // Fetch phone / code for parties
        $debitPartyPhone = match ($jv->debit_party_type) {
            'vendor'   => \App\Models\Vendor::find($jv->debit_party_id)?->phone ?? '—',
            'customer' => \App\Models\Customer::find($jv->debit_party_id)?->mobile ?? '—',
            'account'  => \App\Models\Account::find($jv->debit_party_id)?->account_code ?? '—',
            default    => '—',
        };

        $creditPartyPhone = match ($jv->credit_party_type) {
            'vendor'   => \App\Models\Vendor::find($jv->credit_party_id)?->phone ?? '—',
            'customer' => \App\Models\Customer::find($jv->credit_party_id)?->mobile ?? '—',
            'account'  => \App\Models\Account::find($jv->credit_party_id)?->account_code ?? '—',
            default    => '—',
        };

        $branch = null;
        $branchIdForPrint = $jv->branch_id ?? (Auth::check() ? Auth::user()->branch_id : null);
        if ($branchIdForPrint) {
            $branch = DB::table('branches')->where('id', $branchIdForPrint)->first();
        }

        return view('admin_panel.vochers.journal_vouchers.print', compact(
            'jv', 'debitPartyName', 'creditPartyName', 'debitPartyPhone', 'creditPartyPhone', 'branch'
        ));
    }

    /**
     * Delete (soft-delete) a Journal Voucher.
     */
    public function journal_voucher_destroy(int $id)
    {
        $jv = JournalVoucher::findOrFail($id);
        $jv->delete();
        return back()->with('success', "Journal Voucher #{$jv->jvid} deleted.");
    }

    /**
     * AJAX — Get party list for Journal Voucher (customers + vendors + accounts)
     */
    public function getJournalPartyList(\Illuminate\Http\Request $request)
    {
        $type     = strtolower($request->query('type', ''));
        $isSuper  = Auth::check() && Auth::user()->hasRole('super admin');
        $branchId = ($isSuper && $request->filled('branch_id'))
            ? $request->branch_id
            : (Auth::check() ? Auth::user()->branch_id : null);

        if ($type === 'customer') {
            $query = \App\Models\Customer::with(['ledgers' => fn($q) => $q->latest()])
                ->whereIn('customer_type', ['credit', 'cash']);

            if (!$isSuper && $branchId) $query->where('branch_id', $branchId);
            if ($isSuper && $request->filled('branch_id')) $query->where('branch_id', $branchId);

            return response()->json($query->get()->map(function ($c) {
                $ledger  = $c->ledgers->first();
                $closing = $ledger ? (float) $ledger->closing_balance : (float) ($c->opening_balance ?? 0);
                return ['id' => $c->id, 'text' => $c->customer_name, 'mobile' => $c->mobile ?? '', 'closing_balance' => $closing];
            }));

        } elseif ($type === 'vendor') {
            $query = \App\Models\Vendor::query();
            if (!$isSuper && $branchId) $query->where('branch_id', $branchId);
            if ($isSuper && $request->filled('branch_id')) $query->where('branch_id', $branchId);

            return response()->json($query->get()->map(function ($v) use ($branchId) {
                $lastLedger = VendorLedger::where('vendor_id', $v->id)
                    ->where('branch_id', $branchId ?? $v->branch_id)
                    ->orderByDesc('id')->first();
                $closing = $lastLedger ? (float) $lastLedger->closing_balance : (float) ($v->opening_balance ?? 0);
                return ['id' => $v->id, 'text' => $v->name, 'mobile' => $v->phone ?? $v->contact ?? '', 'closing_balance' => $closing];
            }));
        }

        return response()->json([]);
    }
}

