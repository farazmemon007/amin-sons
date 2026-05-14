<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use App\Models\VendorLedger;
use App\Models\VendorPayment;
use App\Models\VendorBilty;
use App\Models\Purchase;
use App\Models\Brand;

class VendorController extends Controller
{
    // Show all vendors
    public function index(Request $request) 
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super admin');
        
        $query = Vendor::with('branch');

        if (!$isSuperAdmin) {
            // Regular users only see their own branch
            $query->where('branch_id', $user->branch_id ?? 0);
        } else {
            // Super Admin can filter by branch
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }
        }

        $vendors = $query->orderBy('name')->get();

        // Calculate real-time current balance for each vendor
        foreach ($vendors as $vendor) {
            $latestEntry = VendorLedger::where('vendor_id', $vendor->id)
                ->where('branch_id', $vendor->branch_id)
                ->orderBy('id', 'desc')
                ->first();
            
            // If no ledger entry exists, use the base opening balance
            $vendor->current_balance = $latestEntry ? $latestEntry->closing_balance : $vendor->opening_balance;
        }

        $branches = \App\Models\Branch::orderBy('name')->get();
        $allBrands = Brand::orderBy('name')->get();

        return view('admin_panel.vendors.index', compact('vendors', 'branches', 'isSuperAdmin', 'allBrands'));
    }

    // Store or update vendor information
    public function store(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super admin');

        if ($request->id) {
            $vendor = Vendor::findOrFail($request->id);
            $oldOpening = (float)$vendor->opening_balance;
            $newOpening = (float)($request->opening_balance ?? 0);

            $updateData = $request->all();
            
            // If not super admin, prevent changing branch
            if (!$isSuperAdmin) {
                unset($updateData['branch_id']);
            }
            
            // Handle Companies (Convert comma-separated string to array)
            if ($request->has('company_names_raw')) {
                $companies = explode(',', $request->company_names_raw);
                $updateData['company_names'] = array_filter(array_map('trim', $companies));
            }

            $vendor->update($updateData);

            // Handle Brands
            $vendor->brand_ids = $request->brand_ids ?? [];
            $vendor->save();

            // ✅ If opening balance changed, cascade the change through the ledger
            if ($oldOpening !== $newOpening) {
                $diff = $newOpening - $oldOpening;

                // Find the first ledger entry (the opening balance entry)
                $firstLedger = VendorLedger::where('vendor_id', $vendor->id)
                    ->orderBy('id', 'asc')
                    ->first();

                if ($firstLedger) {
                    // Update the first entry
                    $firstLedger->update([
                        'opening_balance' => $newOpening,
                        'credit_amount'  => $newOpening,
                        'closing_balance' => $newOpening
                    ]);

                    // Update all subsequent entries to maintain the running balance
                    $subsequentLedgers = VendorLedger::where('vendor_id', $vendor->id)
                        ->where('id', '>', $firstLedger->id)
                        ->get();

                    foreach ($subsequentLedgers as $ledger) {
                        $ledger->update([
                            'previous_balance' => $ledger->previous_balance + $diff,
                            'opening_balance'  => $ledger->opening_balance + $diff,
                            'closing_balance'  => $ledger->closing_balance + $diff
                        ]);
                    }
                }
            }
        } else {
            // Create a new vendor and ledger entry
            $data = $request->all();
            
            // Assign branch_id
            if ($isSuperAdmin && $request->filled('branch_id')) {
                $data['branch_id'] = $request->branch_id;
            } else {
                $data['branch_id'] = $user->branch_id ?? 0;
            }
            
            // Handle Companies (Convert comma-separated string to array)
            if ($request->filled('company_names_raw')) {
                $companies = explode(',', $request->company_names_raw);
                $data['company_names'] = array_filter(array_map('trim', $companies));
            } else {
                $data['company_names'] = [];
            }

            $vendor = Vendor::create($data);

            // Re-set these to be safe
            $vendor->company_names = $data['company_names'];
            $vendor->brand_ids = $request->brand_ids ?? [];
            $vendor->save();

            // Create ledger entry (branch-specific)
            VendorLedger::create([
                'vendor_id' => $vendor->id,
                'branch_id' => $vendor->branch_id,
                'admin_or_user_id' => Auth::id(),
                'opening_balance' => (float)($request->opening_balance ?? 0),
                'previous_balance' => 0,
                'credit_amount' => (float)($request->opening_balance ?? 0),
                'closing_balance' => (float)($request->opening_balance ?? 0),
            ]);
        }

        return redirect()->back()->with('success', 'Vendor saved successfully!');
    }

    // Soft delete vendor and related ledger entry
    public function delete($id) {
    // Find the vendor by id, along with the related ledger entry using the 'ledger' relationship
    $vendor = Vendor::with('ledger')->findOrFail($id);

    // The vendor's ledger will be automatically deleted due to cascading delete
    $vendor->delete(); // Soft delete vendor

    return back()->with('success', 'Deleted Successfully');
}


    // Show vendor ledger list (Balances summary)
    public function vendors_ledger(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $query = VendorLedger::with(['vendor', 'branch']);

            // --- Branch Filtering Logic ---
            if ($user->hasRole('super admin')) {
                // Super Admin can filter by branch if provided
                if ($request->filled('branch_id')) {
                    $query->where('branch_id', $request->branch_id);
                }
            } else {
                // Non-Super Admin is locked to their own branch
                $branchId = $user->branch_id ?? 0;
                $query->where('branch_id', $branchId);
            }

            // Filtering by Vendor and Date
            if ($request->filled('vendor_id')) {
                $query->where('vendor_id', $request->vendor_id);
            }

            if ($request->filled('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }

            if ($request->filled('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            $VendorLedgers = $query->orderBy('id', 'desc')->get();
            
            // Filter vendors dropdown by branch
            $vendorQuery = Vendor::query();
            if (!$user->hasRole('super admin')) {
                $vendorQuery->where('branch_id', $user->branch_id ?? 0);
            } elseif ($request->filled('branch_id')) {
                $vendorQuery->where('branch_id', $request->branch_id);
            }
            $vendors = $vendorQuery->get(); 

            $branches = \App\Models\Branch::all(); // For Super Admin dropdown

            return view('admin_panel.vendors.vendors_ledger', compact('VendorLedgers', 'vendors', 'branches'));
        } else {
            return redirect()->back();
        }
    }

    // Show all vendor payments
    public function vendor_payments(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super admin');
        
        $query = VendorPayment::with('vendor');

        if (!$isSuperAdmin) {
            $branchId = $user->branch_id ?? 0;
            $query->whereHas('vendor', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
            $vendors = Vendor::where('branch_id', $branchId)->orderBy('name')->get();
        } else {
            if ($request->filled('branch_id')) {
                $query->whereHas('vendor', function($q) use ($request) {
                    $q->where('branch_id', $request->branch_id);
                });
                $vendors = Vendor::where('branch_id', $request->branch_id)->orderBy('name')->get();
            } else {
                // By default for Super Admin, maybe show all or pick first branch
                $vendors = Vendor::orderBy('name')->get();
            }
        }

        $payments = $query->orderByDesc('payment_date')->get();
        $branches = \App\Models\Branch::orderBy('name')->get();

        return view('admin_panel.vendors.vendor_payments', compact('payments', 'vendors', 'branches', 'isSuperAdmin'));
    }

    // Store vendor payment and update ledger
    public function store_vendor_payment(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string',
            'note' => 'nullable|string',
            'adjustment_type' => 'required|in:plus,minus',
        ]);

        // Save the vendor payment
        VendorPayment::create([
            'vendor_id' => $request->vendor_id,
            'admin_or_user_id' => Auth::id(),
            'payment_date' => $request->payment_date,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'note' => $request->note,
        ]);

        // Append a new ledger record for this payment (append-only ledger)
        $branchId = Auth::user()->branch_id ?? 0;
        $latestLedger = VendorLedger::where('vendor_id', $request->vendor_id)
            ->where('branch_id', $branchId)
            ->orderBy('id', 'desc')
            ->first();

        $previousBalance = $latestLedger ? (float)$latestLedger->closing_balance : (float)(\App\Models\Vendor::find($request->vendor_id)->opening_balance ?? 0);
        $amount = (float)$request->amount;
        
        $newClosing = $request->adjustment_type === 'plus'
            ? $previousBalance + $amount
            : $previousBalance - $amount;

        VendorLedger::create([
            'vendor_id'        => $request->vendor_id,
            'branch_id'        => $branchId,
            'admin_or_user_id' => Auth::id(),
            'previous_balance' => $previousBalance,
            'opening_balance'  => $previousBalance,
            'debit_amount'     => $request->adjustment_type === 'minus' ? $amount : 0,
            'credit_amount'    => $request->adjustment_type === 'plus' ? $amount : 0,
            'closing_balance'  => $newClosing,
        ]);

        return redirect()->back()->with('success', 'Vendor payment recorded and ledger updated.');
    }

    // Show all vendor bilties
    public function vendor_bilties(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super admin');
        
        $query = VendorBilty::with(['vendor', 'purchase']);

        if (!$isSuperAdmin) {
            $branchId = $user->branch_id ?? 0;
            $query->whereHas('vendor', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
            $vendors = Vendor::where('branch_id', $branchId)->orderBy('name')->get();
            $purchases = Purchase::where('branch_id', $branchId)->orderByDesc('id')->get();
        } else {
            if ($request->filled('branch_id')) {
                $query->whereHas('vendor', function($q) use ($request) {
                    $q->where('branch_id', $request->branch_id);
                });
                $vendors = Vendor::where('branch_id', $request->branch_id)->orderBy('name')->get();
                $purchases = Purchase::where('branch_id', $request->branch_id)->orderByDesc('id')->get();
            } else {
                $vendors = Vendor::orderBy('name')->get();
                $purchases = Purchase::orderByDesc('id')->get();
            }
        }

        $bilties = $query->orderByDesc('id')->get();
        $branches = \App\Models\Branch::orderBy('name')->get();

        return view('admin_panel.vendors.vendor_bilties', compact('bilties', 'vendors', 'purchases', 'branches', 'isSuperAdmin'));
    }

    // Store vendor bilty information
    public function store_vendor_bilty(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'purchase_id' => 'nullable|exists:purchases,id',
            'bilty_no' => 'nullable|string',
            'vehicle_no' => 'nullable|string',
            'transporter_name' => 'nullable|string',
            'delivery_date' => 'nullable|date',
            'note' => 'nullable|string',
        ]);

        VendorBilty::create($request->all());

        return back()->with('success', 'Vendor bilty saved successfully.');
    }

    // Get vendor balance by vendor id
    public function getVendorBalance($id)
    {
        $ledger = VendorLedger::where('vendor_id', $id)->first();
        return response()->json([
            'closing_balance' => $ledger ? $ledger->closing_balance : 0
        ]);
    }

    /**
     * ✅ AJAX Helper: Fetch vendors for a specific branch
     */
    public function getVendorsByBranch($branchId)
    {
        $vendors = Vendor::where('branch_id', $branchId)->orderBy('name')->get(['id', 'name', 'phone', 'address']);
        return response()->json($vendors);
    }
}
