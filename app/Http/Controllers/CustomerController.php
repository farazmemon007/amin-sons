<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\CustomerPayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{

//////////////
  // 🔹 Load customers list by type
 public function saleindex(Request $request)
{
    try {
        // Get type parameter and optional branch filter from request
        $type = $request->query('type');
        $branchParam = $request->query('branch_id');

        // Build base query - select ONLY actual database columns
        // closing_balance is an accessor (virtual attribute), not a real column
        $query = Customer::where('status', 'active')
            ->select('id', 'customer_id', 'customer_name', 'mobile', 'address', 'opening_balance', 'credit_limit', 'customer_type')
            ->orderBy('customer_name');

        $isSuper = Auth::check() && Auth::user()->hasRole('super admin');

        // Super-admin: require explicit branch selection — otherwise return empty
        if ($isSuper) {
            if (!empty($branchParam)) {
                $query->where('branch_id', (int)$branchParam);
            } else {
                return response()->json([]);
            }
        } else {
            // Restrict to branch for non-super-admin users
            if (Auth::check()) {
                $branchId = Auth::user()->branch_id ?? 0;
                $query->where('branch_id', $branchId);
            } else {
                return response()->json([]);
            }
        }

        // Filter by type if provided
        if (!empty($type)) {
            $query->where('customer_type', '=', $type);
        }

        // Get customers and append closing_balance accessor
        $customers = $query->get();

        return response()->json($customers);
    } catch (\Exception $e) {
        \Log::error('Error in saleindex', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        return response()->json(['error' => 'Error loading customers: ' . $e->getMessage()], 500);
    }
}

    // 🔹 Single customer detail — returns JSON for the Sale form AJAX call
    public function show($id)
    {
        $customer = Customer::findOrFail($id);

        // Get latest closing balance from ledger (accessor may not work over plain $.get without Accept header)
        $latestLedger = CustomerLedger::where('customer_id', $id)->latest('id')->first();
        $closingBalance = $latestLedger
            ? (float) $latestLedger->closing_balance
            : (float) ($customer->opening_balance ?? 0);

        return response()->json([
            'id'               => $customer->id,
            'customer_id'      => $customer->customer_id,
            'customer_name'    => $customer->customer_name,
            'mobile'           => $customer->mobile,
            'address'          => $customer->address,
            'credit_limit'     => $customer->credit_limit,
            'no_credit_limit'  => $customer->no_credit_limit,
            'credit_upto'      => $customer->credit_upto,
            'customer_type'    => $customer->customer_type,
            'opening_balance'  => (float) ($customer->opening_balance ?? 0),
            'closing_balance'  => $closingBalance,
            'remarks'          => $customer->remarks ?? '',
            'filer_type'       => $customer->filer_type,
            'status'           => $customer->status,
        ]);
    }


    ////////////





    public function index(Request $request)
    {
        $branch_id     = $request->get('branch_id');
        $customer_id   = $request->get('customer_id');
        $start_date    = $request->get('start_date');
        $end_date      = $request->get('end_date');
        $customer_type = $request->get('customer_type');

        $query = Customer::with(['latestLedger', 'branch'])->latest();

        // 🔹 ERP Branch Filtering
        if (Auth::check() && !Auth::user()->hasRole('super admin')) {
            $branchId = Auth::user()->branch_id ?? 0;
            $query->where('branch_id', $branchId);
        } else {
            // Super admin can filter by branch
            if (!empty($branch_id)) {
                $query->where('branch_id', $branch_id);
            }
        }

        // 🔹 Customer Selection Search
        if (!empty($customer_id)) {
            $query->where('id', $customer_id);
        }

        // 🔹 Customer Type Search
        if (!empty($customer_type)) {
            $query->where('customer_type', $customer_type);
        }

        // 🔹 Date Range Search
        if (!empty($start_date)) {
            $query->whereDate('created_at', '>=', $start_date);
        }
        if (!empty($end_date)) {
            $query->whereDate('created_at', '<=', $end_date);
        }

        $customers = $query->get();

        // Data for dropdowns
        $branches = [];
        if (Auth::user()->hasRole('super admin')) {
            $branches = \App\Models\Branch::all();
        }

        // All customers for the search select2 (filtered by branch if selected)
        $allCustomersQuery = Customer::select('id', 'customer_name', 'customer_id');
        if (Auth::check() && !Auth::user()->hasRole('super admin')) {
            $allCustomersQuery->where('branch_id', Auth::user()->branch_id ?? 0);
        } else {
            if (!empty($branch_id)) {
                $allCustomersQuery->where('branch_id', $branch_id);
            }
        }
        $allCustomers = $allCustomersQuery->get();

        return view('admin_panel.customers.index', compact('customers', 'branches', 'allCustomers'));
    }

    public function toggleStatus($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->status = $customer->status === 'active' ? 'inactive' : 'active';
        $customer->save();

        return redirect()->back()->with('success', 'Customer status updated.');
    }

    // Add this in CustomerController
    public function getCustomerLedger($id)
    {
        $ledger = CustomerLedger::where('customer_id', $id)->latest()->first();
        return response()->json([
            'closing_balance' => $ledger->closing_balance
        ]);
    }


    public function markInactive($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->status = 'inactive';
        $customer->save();

        return redirect()->route('customers.index')->with('success', 'Customer marked as inactive.');
    }

    public function inactiveCustomers()
    {
        $customers = Customer::where('status', 'inactive')->latest()->get();
        return view('admin_panel.customers.inactive', compact('customers'));
    }

    public function create()
    {
        $isSuper = auth()->user() && auth()->user()->hasRole('super admin');
        $branchId = $isSuper ? null : (auth()->user()->branch_id ?? null);

        $latestId = '';
        if ($branchId) {
            $prefix = 'CUST-' . str_pad($branchId, 2, '0', STR_PAD_LEFT) . '-';
            $latestCustomer = Customer::where('branch_id', $branchId)
                ->where('customer_id', 'like', $prefix . '%')
                ->get()
                ->map(function($c) use ($prefix) {
                    $subStr = substr($c->customer_id, strlen($prefix));
                    return is_numeric($subStr) ? (int)$subStr : 0;
                })
                ->max();

            $nextSeq = ($latestCustomer ?? 0) + 1;
            $latestId = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
        }

        return view('admin_panel.customers.create', compact('latestId'));
    }

    public function getNextCustomerId(Request $request)
    {
        $branchId = $request->query('branch_id');
        if (!$branchId) {
            return response()->json(['customer_id' => '']);
        }

        $prefix = 'CUST-' . str_pad($branchId, 2, '0', STR_PAD_LEFT) . '-';

        $latestCustomer = Customer::where('branch_id', $branchId)
            ->where('customer_id', 'like', $prefix . '%')
            ->get()
            ->map(function($c) use ($prefix) {
                $subStr = substr($c->customer_id, strlen($prefix));
                return is_numeric($subStr) ? (int)$subStr : 0;
            })
            ->max();

        $nextSeq = ($latestCustomer ?? 0) + 1;
        $nextCustomerId = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

        return response()->json(['customer_id' => $nextCustomerId]);
    }

    public function store(Request $request)
    {
        // return $request->all();
        // dd();

        $data = $request->validate([
            'branch_id'        => 'required',
            'customer_id'        => 'required|unique:customers',
            'customer_name'      => 'nullable',
            'customer_name_ur'   => 'nullable',
            'cnic'               => 'nullable',
            'filer_type'         => 'nullable',
            'zone'               => 'nullable',
            'contact_person'     => 'nullable',
            'mobile'             => 'nullable',
            'email_address'      => 'nullable|email',
            'contact_person_2'   => 'nullable',
            'mobile_2'           => 'nullable',
            'email_address_2'    => 'nullable|email',
            'opening_balance'    => 'nullable|numeric',
            'credit_upto'        => 'nullable|date',
            'credit_limit'       => 'nullable|numeric|min:0',
            'address'            => 'nullable',
            'customer_type'      => 'nullable',
            'no_credit_limit'    => 'nullable|boolean',
        ]);

        // Business logic: if no_credit_limit is true, set credit_limit to null
        // if credit_limit is provided, set no_credit_limit to false
        if ($request->boolean('no_credit_limit')) {
            $data['credit_limit'] = null;
        } elseif ($request->has('credit_limit') && $request->credit_limit !== null) {
            $data['no_credit_limit'] = false;
        }

        // Customer create
        $customer = Customer::create($data);

        // Ledger me entry agar opening balance dia gaya ho
        $opening = $data['opening_balance'] ?? 0;

        if ($opening > 0) {
            CustomerLedger::create([
                'customer_id'      => $customer->id,
                'admin_or_user_id' => Auth::id(),
                'previous_balance' => 0,
                'opening_balance'  => $opening,           // ✅ yahan set karna zaroori hai
                'closing_balance'  => $opening,
            ]);
        }

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }


    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        return view('admin_panel.customers.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        // Validate the input
        $data = $request->validate([
            'branch_id'      => 'nullable|integer',
            'customer_name'      => 'nullable|string',
            'customer_name_ur'   => 'nullable|string',
            'customer_type'      => 'nullable|string',
            'cnic'               => 'nullable|string',
            'filer_type'         => 'nullable|string',
            'mobile'             => 'nullable|string',
            'address'            => 'nullable|string',
            'address_details'    => 'nullable|string',
            'opening_balance'    => 'nullable|numeric|min:0',
            'credit_limit'       => 'nullable|numeric|min:0',
            'closing_balance'    => 'nullable|numeric',
            'no_credit_limit'    => 'nullable|boolean',
        ]);

        // Business logic: if no_credit_limit is true, set credit_limit to null
        // if credit_limit is provided, set no_credit_limit to false
        if ($request->boolean('no_credit_limit')) {
            $data['credit_limit'] = null;
        } elseif ($request->has('credit_limit') && $request->credit_limit !== null) {
            $data['no_credit_limit'] = false;
        }

        // Update customer
        $customer->update($data);

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }


    // customer ledger start

    // Customer Ledger View
    public function customer_ledger()
    {
        if (Auth::check()) {
            $userId = Auth::id();
            $query = CustomerLedger::with('customer');
                // ->where('admin_or_user_id', $userId);

            // If current user is not super admin, show only ledgers for customers in user's branch
            if (!Auth::user()->hasRole('super admin')) {
                $branchId = Auth::user()->branch_id ?? 0;
                $query->whereHas('customer', function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
            }

            $CustomerLedgers = $query->orderBy('id', 'desc')->get();
            //     echo "<pre>";
            // print_r($CustomerLedgers);
            //     dd();
            return view('admin_panel.customers.customer_ledger', compact('CustomerLedgers'));
        } else {
            return redirect()->back();
        }
    }
    // customer payment start


    // View all customer payments
    public function customer_payments()
    {
        $payments = CustomerPayment::with('customer')->orderByDesc('id')->get();
        $customers = Customer::all();
        return view('admin_panel.customers.customer_payments', compact('payments', 'customers'));
    }

    // Store a customer payment
    public function store_customer_payment(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0',
            'adjustment_type' => 'required|in:plus,minus',
            'payment_method' => 'nullable|string',
            'payment_date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        $userId = Auth::id();

        // Save the payment
        CustomerPayment::create([
            'customer_id'      => $request->customer_id,
            'admin_or_user_id' => $userId,
            'amount'           => $request->amount,
            'payment_method'   => $request->payment_method,
            'payment_date'     => $request->payment_date,
            'note'             => $request->note,
        ]);

        // Append a new ledger record for this payment (append-only ledger)
        $ledger = CustomerLedger::where('customer_id', $request->customer_id)->latest()->first();
        $previousBalance = $ledger ? $ledger->closing_balance : ($this->getCustomerOpeningBalance($request->customer_id));

        // For payments: 'plus' means add to balance, 'minus' means subtract (customer paid)
        $amount = (float)$request->amount;
        $newClosing = $request->adjustment_type === 'plus'
            ? $previousBalance + $amount
            : $previousBalance - $amount;

        CustomerLedger::create([
            'customer_id'      => $request->customer_id,
            'admin_or_user_id' => $userId,
            'previous_balance' => $previousBalance,
            'opening_balance'  => 0,
            'closing_balance'  => $newClosing,
            'description'      => ($request->note ?: 'Customer payment') . ' - ' . $request->payment_date,
        ]);

        return back()->with('success', 'Payment recorded and ledger updated.');
    }

    public function destroy_payment($id)
    {
        $payment = CustomerPayment::findOrFail($id);

        $customerId = $payment->customer_id;
        $amount     = $payment->amount;

        // Reverse payment by appending a ledger entry that increases customer's balance
        $latest = CustomerLedger::where('customer_id', $customerId)->latest()->first();
        $previousBalance = $latest ? $latest->closing_balance : $this->getCustomerOpeningBalance($customerId);
        $newClosing = $previousBalance + $amount;

        CustomerLedger::create([
            'customer_id'      => $customerId,
            'admin_or_user_id' => auth()->id(),
            'previous_balance' => $previousBalance,
            'opening_balance'  => 0,
            'closing_balance'  => $newClosing,
            'description'      => 'Reversed payment id: ' . $payment->id,
        ]);

        // Delete the payment entry
        $payment->delete();

        return redirect()->back()->with('success', 'Payment deleted and customer ledger updated successfully.');
    }

    // Helper: get opening balance from customer record (fallback)
    protected function getCustomerOpeningBalance($customerId)
    {
        $c = Customer::find($customerId);
        return $c ? (float)($c->opening_balance ?? 0) : 0;
    }


    public function getByType(Request $request)
    {
        $type = $request->get('type');

        $customers = Customer::where('customer_type', $type)->get(['id', 'customer_name']);

        return response()->json(['customers' => $customers]);
    }
}
