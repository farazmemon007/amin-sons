<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Complaint;
use App\Models\ComplaintHomeService;
use App\Models\ComplaintStatusLog;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Milon\Barcode\DNS1D;

class ComplaintController extends Controller
{
    // ─── Index ───────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user     = Auth::user();
        $isSuperAdmin = $user->hasRole('super admin');

        $query = Complaint::with(['branch', 'customer', 'createdByUser'])
            ->orderBy('id', 'desc');

        // Branch filter for non-super-admins
        if (!$isSuperAdmin) {
            $query->where('branch_id', $user->branch_id);
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('scenario_type')) {
            $query->where('scenario_type', $request->scenario_type);
        }
        if ($request->filled('branch_id') && $isSuperAdmin) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('complaint_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('complaint_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('complaint_no', 'like', "%$s%")
                  ->orWhere('customer_name', 'like', "%$s%")
                  ->orWhere('customer_mobile', 'like', "%$s%")
                  ->orWhere('product_name', 'like', "%$s%");
            });
        }

        $complaints = $query->paginate(20)->withQueryString();
        $branches   = $isSuperAdmin ? Branch::orderBy('name')->get() : collect();

        return view('admin_panel.complaints.index', compact('complaints', 'branches'));
    }

    // ─── Create ──────────────────────────────────────────────────

    public function create()
    {
        $branches  = Branch::orderBy('name')->get();
        $customers = Customer::orderBy('customer_name')->get();
        $products  = Product::orderBy('item_name')->get();
        $user      = Auth::user();

        return view('admin_panel.complaints.create', compact('branches', 'customers', 'products', 'user'));
    }

    // ─── Store ───────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'scenario_type'    => 'required|in:walk_in,remote,home_service',
            'branch_id'        => 'required|exists:branches,id',
            'customer_name'    => 'required|string|max:255',
            'customer_mobile'  => 'nullable|string|max:20',
            'customer_address' => 'nullable|string',
            'issue_description'=> 'required|string',
            'complaint_date'   => 'required|date',
            'product_name'     => 'nullable|string|max:255',
            'product_serial'   => 'nullable|string|max:100',
            'product_model'    => 'nullable|string|max:100',
            'photo_path'       => 'nullable|image|max:4096',
            'is_product_part'   => 'nullable',
            'product_part_name' => 'nullable|required_if:is_product_part,1|string|max:255',
            // Home service fields
            'technician_name'  => 'nullable|required_if:scenario_type,home_service|string|max:255',
            'visit_date'       => 'nullable|required_if:scenario_type,home_service|date',
            'visiting_charges' => 'nullable|numeric|min:0',
        ]);

        $branchId = $request->branch_id;

        // Generate complaint number
        $complaintNo = Complaint::generateComplaintNo($branchId);

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo_path')) {
            $photoPath = $request->file('photo_path')
                ->store('complaint-photos', 'public');
        }

        // Create complaint
        $complaint = Complaint::create([
            'complaint_no'      => $complaintNo,
            'branch_id'         => $branchId,
            'scenario_type'     => $request->scenario_type,
            'customer_id'       => $request->customer_id ?: null,
            'customer_name'     => $request->customer_name,
            'customer_mobile'   => $request->customer_mobile,
            'customer_address'  => $request->customer_address,
            'product_id'        => $request->product_id ?: null,
            'product_name'      => $request->product_name,
            'product_serial'    => $request->product_serial,
            'product_model'     => $request->product_model,
            'is_product_part'   => $request->has('is_product_part') ? (bool)$request->is_product_part : false,
            'product_part_name' => $request->product_part_name,
            'issue_description' => $request->issue_description,
            'complaint_date'    => $request->complaint_date,
            'photo_path'        => $photoPath,
            'status'            => 'pending',
            'created_by'        => Auth::id(),
        ]);

        // Generate barcode and save
        $this->generateAndSaveBarcode($complaint);

        // Log initial status
        ComplaintStatusLog::create([
            'complaint_id' => $complaint->id,
            'old_status'   => null,
            'new_status'   => 'pending',
            'notes'        => 'Complaint registered.',
            'changed_by'   => Auth::id(),
        ]);

        // If home_service scenario, create first visit record
        if ($request->scenario_type === 'home_service' && $request->filled('technician_name')) {
            ComplaintHomeService::create([
                'complaint_id'     => $complaint->id,
                'technician_name'  => $request->technician_name,
                'visit_date'       => $request->visit_date,
                'visit_time'       => $request->visit_time,
                'visiting_charges' => $request->visiting_charges ?? 0,
                'visit_notes'      => $request->visit_notes,
                'visit_status'     => 'scheduled',
                'created_by'       => Auth::id(),
            ]);

            // Set in_progress when home service is scheduled
            $complaint->update(['status' => 'in_progress']);
        }

        return redirect()->route('complaints.show', $complaint->id)
            ->with('success', "Complaint {$complaintNo} registered successfully!");
    }

    // ─── Show ────────────────────────────────────────────────────

    public function show($id)
    {
        $complaint = Complaint::with([
            'branch',
            'customer',
            'product',
            'createdByUser',
            'resolvedByUser',
            'homeServices.createdByUser',
            'statusLogs.changedByUser',
        ])->findOrFail($id);

        return view('admin_panel.complaints.show', compact('complaint'));
    }

    // ─── Edit ────────────────────────────────────────────────────

    public function edit($id)
    {
        $complaint = Complaint::findOrFail($id);
        $branches  = Branch::orderBy('name')->get();
        $customers = Customer::orderBy('customer_name')->get();
        $products  = Product::orderBy('item_name')->get();

        return view('admin_panel.complaints.edit', compact('complaint', 'branches', 'customers', 'products'));
    }

    // ─── Update ──────────────────────────────────────────────────

    public function update(Request $request, $id)
    {
        $complaint = Complaint::findOrFail($id);

        $validated = $request->validate([
            'customer_name'    => 'required|string|max:255',
            'customer_mobile'  => 'nullable|string|max:20',
            'customer_address' => 'nullable|string',
            'issue_description'=> 'required|string',
            'status'           => 'required|in:pending,in_progress,resolved,closed',
            'resolution_type'  => 'nullable|in:exchanged,repaired,refunded,pending_stock,none',
            'resolution_notes' => 'nullable|string',
            'resolved_date'    => 'nullable|date',
            'is_product_part'   => 'nullable',
            'product_part_name' => 'nullable|required_if:is_product_part,1|string|max:255',
        ]);

        $oldStatus = $complaint->status;

        $complaint->update([
            'customer_name'    => $request->customer_name,
            'customer_mobile'  => $request->customer_mobile,
            'customer_address' => $request->customer_address,
            'product_name'     => $request->product_name,
            'product_serial'   => $request->product_serial,
            'product_model'    => $request->product_model,
            'is_product_part'   => $request->has('is_product_part') ? (bool)$request->is_product_part : false,
            'product_part_name' => $request->product_part_name,
            'issue_description'=> $request->issue_description,
            'status'           => $request->status,
            'resolution_type'  => $request->resolution_type,
            'resolution_notes' => $request->resolution_notes,
            'resolved_date'    => $request->resolved_date,
            'resolved_by'      => in_array($request->status, ['resolved', 'closed']) ? Auth::id() : $complaint->resolved_by,
        ]);

        // Log status change
        if ($oldStatus !== $request->status) {
            ComplaintStatusLog::create([
                'complaint_id' => $complaint->id,
                'old_status'   => $oldStatus,
                'new_status'   => $request->status,
                'notes'        => $request->status_note ?? 'Status updated.',
                'changed_by'   => Auth::id(),
            ]);
        }

        return redirect()->route('complaints.show', $complaint->id)
            ->with('success', 'Complaint updated successfully!');
    }

    // ─── Destroy ─────────────────────────────────────────────────

    public function destroy($id)
    {
        $complaint = Complaint::findOrFail($id);
        $complaint->delete();

        return redirect()->route('complaints.index')
            ->with('success', 'Complaint deleted.');
    }

    // ─── Print Slip (Customer Receipt) ───────────────────────────

    public function printSlip($id)
    {
        $complaint = Complaint::with(['branch', 'customer', 'product'])->findOrFail($id);
        return view('admin_panel.complaints.print_slip', compact('complaint'));
    }

    // ─── Print Tag (Product Sticker) ─────────────────────────────

    public function printTag($id)
    {
        $complaint = Complaint::with(['branch'])->findOrFail($id);
        return view('admin_panel.complaints.print_tag', compact('complaint'));
    }

    // ─── Add Home Service ─────────────────────────────────────────

    public function addHomeService($id)
    {
        $complaint = Complaint::findOrFail($id);
        return view('admin_panel.complaints.partials.home_service_form', compact('complaint'));
    }

    public function storeHomeService(Request $request, $id)
    {
        $complaint = Complaint::findOrFail($id);

        $request->validate([
            'technician_name'  => 'required|string|max:255',
            'visit_date'       => 'required|date',
            'visiting_charges' => 'nullable|numeric|min:0',
        ]);

        ComplaintHomeService::create([
            'complaint_id'     => $complaint->id,
            'technician_name'  => $request->technician_name,
            'visit_date'       => $request->visit_date,
            'visit_time'       => $request->visit_time,
            'visiting_charges' => $request->visiting_charges ?? 0,
            'charges_paid'     => $request->has('charges_paid'),
            'visit_notes'      => $request->visit_notes,
            'visit_status'     => $request->visit_status ?? 'scheduled',
            'created_by'       => Auth::id(),
        ]);

        // Update complaint status to in_progress if still pending
        if ($complaint->status === 'pending') {
            $complaint->update(['status' => 'in_progress']);
            ComplaintStatusLog::create([
                'complaint_id' => $complaint->id,
                'old_status'   => 'pending',
                'new_status'   => 'in_progress',
                'notes'        => 'Home service visit scheduled.',
                'changed_by'   => Auth::id(),
            ]);
        }

        return redirect()->route('complaints.show', $complaint->id)
            ->with('success', 'Home service visit added successfully!');
    }

    // ─── Update Home Service Status ──────────────────────────────

    public function updateHomeService(Request $request, $id)
    {
        $service = ComplaintHomeService::findOrFail($id);
        $service->update([
            'visit_status'  => $request->visit_status,
            'visit_notes'   => $request->visit_notes,
            'charges_paid'  => $request->has('charges_paid'),
        ]);

        return redirect()->route('complaints.show', $service->complaint_id)
            ->with('success', 'Visit updated.');
    }

    // ─── AJAX: Change Status ──────────────────────────────────────

    public function changeStatus(Request $request, $id)
    {
        $complaint = Complaint::findOrFail($id);
        $oldStatus = $complaint->status;

        $request->validate([
            'status'           => 'required|in:pending,in_progress,resolved,closed',
            'resolution_type'  => 'nullable|in:exchanged,repaired,refunded,pending_stock,none',
            'resolution_notes' => 'nullable|string',
        ]);

        $complaint->update([
            'status'           => $request->status,
            'resolution_type'  => $request->resolution_type ?? $complaint->resolution_type,
            'resolution_notes' => $request->resolution_notes ?? $complaint->resolution_notes,
            'resolved_date'    => in_array($request->status, ['resolved', 'closed']) ? now() : $complaint->resolved_date,
            'resolved_by'      => in_array($request->status, ['resolved', 'closed']) ? Auth::id() : $complaint->resolved_by,
        ]);

        ComplaintStatusLog::create([
            'complaint_id' => $complaint->id,
            'old_status'   => $oldStatus,
            'new_status'   => $request->status,
            'notes'        => $request->notes ?? "Status changed to {$request->status}.",
            'changed_by'   => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
            'status'  => $request->status,
        ]);
    }

    // ─── WhatsApp Share Link ──────────────────────────────────────

    public function whatsappShare($id)
    {
        $complaint  = Complaint::with(['branch'])->findOrFail($id);
        $branchName = $complaint->branch ? $complaint->branch->name : 'N/A';
        $productName = $complaint->product_name ?? '-';
        $mobile      = $complaint->customer_mobile ?? '-';
        $date        = $complaint->complaint_date->format('d M Y');

        $msg  = "*Complaint Registered - Ameen & Sons*\n\n";
        $msg .= "Complaint No: {$complaint->complaint_no}\n";
        $msg .= "Date: {$date}\n";
        $msg .= "Customer: {$complaint->customer_name}\n";
        $msg .= "Mobile: {$mobile}\n";
        $msg .= "Product: {$productName}\n";
        $msg .= "Issue: {$complaint->issue_description}\n";
        $msg .= "Branch: {$branchName}\n";
        $msg .= "\nPlease keep your complaint number for tracking.";

        $encoded = urlencode($msg);
        $link    = "https://wa.me/?text={$encoded}";

        return response()->json(['link' => $link]);
    }

    // ─── Customer Search (AJAX) ───────────────────────────────────

    public function searchCustomers(Request $request)
    {
        $term = $request->get('q', '');
        $customers = Customer::where('customer_name', 'like', "%{$term}%")
            ->orWhere('mobile', 'like', "%{$term}%")
            ->select('id', 'customer_name', 'mobile', 'address')
            ->limit(20)
            ->get();

        return response()->json($customers);
    }

    // ─── Barcode Generation ───────────────────────────────────────

    private function generateAndSaveBarcode(Complaint $complaint): void
    {
        try {
            $d = new DNS1D();
            $png = $d->getBarcodePNG($complaint->complaint_no, 'C128', 2, 60, [0, 0, 0], true);

            $dir      = storage_path('app/public/complaint-barcodes');
            if (!file_exists($dir)) {
                mkdir($dir, 0775, true);
            }
            $filename = 'complaint_' . $complaint->id . '.png';
            file_put_contents("{$dir}/{$filename}", base64_decode($png));

            $complaint->update(['barcode_path' => 'complaint-barcodes/' . $filename]);
        } catch (\Exception $e) {
            // Barcode generation failed silently — complaint is still saved
        }
    }
}
