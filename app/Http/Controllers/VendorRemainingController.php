<?php

namespace App\Http\Controllers;

use App\Models\VendorRemaining;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorRemainingController extends Controller
{
    /**
     * Display a listing of all pending vendor deliveries
     */
    public function index()
    {
        // Get pending vendor deliveries grouped by vendor
        $remainingItems = VendorRemaining::with(['vendor', 'product', 'purchase', 'warehouse'])
            ->pending()
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        // Summary statistics
        $totalRemaining = VendorRemaining::pending()->sum('remaining_qty');
        $totalVendors = VendorRemaining::pending()->distinct('vendor_id')->count('vendor_id');
        $totalPurchases = VendorRemaining::pending()->distinct('purchase_id')->count('purchase_id');

        return view('admin_panel.vendor_remaining.index', compact(
            'remainingItems',
            'totalRemaining',
            'totalVendors',
            'totalPurchases'
        ));
    }

    /**
     * Display a specific pending vendor delivery
     */
    public function show($id)
    {
        $remaining = VendorRemaining::with(['vendor', 'product', 'purchase', 'warehouse'])
            ->findOrFail($id);

        return view('admin_panel.vendor_remaining.show', compact('remaining'));
    }

    /**
     * Get pending deliveries for a specific vendor (AJAX)
     */
    public function getPendingForVendor($vendorId)
    {
        $pending = VendorRemaining::where('vendor_id', $vendorId)
            ->pending()
            ->with(['product', 'purchase', 'warehouse'])
            ->get();

        return response()->json($pending);
    }

    /**
     * Get pending deliveries for a specific purchase (AJAX)
     */
    public function getPendingForPurchase($purchaseId)
    {
        $pending = VendorRemaining::where('purchase_id', $purchaseId)
            ->pending()
            ->with(['vendor', 'product', 'warehouse'])
            ->get();

        return response()->json($pending);
    }

    /**
     * Mark a vendor remaining item as completed
     */
    public function markCompleted($id)
    {
        $remaining = VendorRemaining::findOrFail($id);
        $remaining->update([
            'remaining_qty' => 0,
            'status'        => 'completed',
        ]);

        return redirect()->route('vendor-remaining.show', $remaining->id)
                         ->with('success', 'Item marked as completed');
    }

    /**
     * Delete a vendor remaining item
     */
    public function delete($id)
    {
        $remaining = VendorRemaining::findOrFail($id);
        $purchaseId = $remaining->purchase_id;
        $remaining->delete();

        return redirect()->route('vendor-remaining.index')
                         ->with('success', 'Pending item removed');
    }

    /**
     * Create new inward gatepass from pending item
     */
    public function createGatepass($id)
    {
        $remaining = VendorRemaining::with(['purchase', 'product', 'vendor', 'warehouse'])
            ->findOrFail($id);

        // Pre-fill the inward gatepass creation form
        return redirect()->route('inward-gatepass.from-purchase', $remaining->purchase_id)
                         ->with('message', 'Create gatepass for remaining ' . $remaining->remaining_qty . ' units');
    }
}
