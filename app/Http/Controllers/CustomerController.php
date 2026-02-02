<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\CustomerPayment;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{

//////////////
  // 🔹 Load customers list by type
 public function saleindex(Request $request)
{
    // For the sales UI we prefer returning active customers with key fields.
    // The UI has used different labels historically ("Main Customer" vs "customer").
    // To avoid mismatches return active customers and include `customer_type`.
    $customers = Customer::where('status', 'active')
        ->select('id', 'customer_id', 'customer_name', 'mobile', 'address', 'opening_balance', 'customer_type')
        ->orderBy('customer_name')
        ->get();

    return response()->json($customers);
}

    // 🔹 Single customer detail
    public function show($id)
    {
        return Customer::with('latestLedger')->findOrFail($id);
    }


    ////////////





    public function index()
    {
        $customers = Customer::with('latestLedger')->latest()->get();
        return view('admin_panel.customers.index', compact('customers'));
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
        $latestId = 'CUST-' . str_pad(Customer::max('id') + 1, 4, '0', STR_PAD_LEFT);
        return view('admin_panel.customers.create', compact('latestId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
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
        ]);

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
        ]);

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
            $CustomerLedgers = CustomerLedger::with('customer')
                ->where('admin_or_user_id', $userId)
                ->orderBy('id','desc')
                ->get();
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
