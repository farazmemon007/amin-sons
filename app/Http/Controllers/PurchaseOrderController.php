<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Concerns\BranchScope;

class PurchaseOrderController extends Controller
{
    use BranchScope;

    public function index(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super admin');
        $allowedBranches = $this->allowedBranches('purchase.order');

        $query = PurchaseOrder::with(['branch', 'vendor', 'items.product']);
        $assignedWarehouses = $user->assignedWarehouseIds();

        if (!$isSuperAdmin) {
            if (!empty($allowedBranches)) {
                $query->whereIn('branch_id', $allowedBranches);
            }
            // Strict warehouse restriction for non-admins (warehouse incharges)
            if (!$user->hasRole('admin')) {
                $query->whereIn('warehouse_id', $assignedWarehouses);
            }
        }

        // ✅ Search by PO Number
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where('po_number', 'LIKE', "%{$searchTerm}%");
        }

        $orders = $query->latest()->get();
        $showBranchColumn = $isSuperAdmin || (count($allowedBranches) > 1);

        return view('admin_panel.purchase_order.index', compact('orders', 'showBranchColumn', 'isSuperAdmin'));
    }

    public function create()
    {
        $user = Auth::user();
        $currentBranch = $user->branch_id ?? 1;
        $isSuperAdmin = $user->hasRole('super admin');

        $vendors = Vendor::where('branch_id', $currentBranch)->orderBy('name')->get();
        $branches = Branch::all();

        $branch = Branch::find($currentBranch);
        $currentBranchName = $branch->name ?? 'N/A';
        $nextPONumber = ((int)($branch->po_counter ?? 0)) + 1;
        $nextPO = 'PO-B' . $currentBranch . '-' . str_pad($nextPONumber, 4, '0', STR_PAD_LEFT);

        // Fetch all unique colors from products, handling both single strings and JSON arrays
        $rawColors = Product::whereNotNull('color')->where('color', '!=', '')->distinct()->pluck('color')->toArray();
        $existingColors = [];
        foreach ($rawColors as $c) {
            if (str_starts_with($c, '[') && str_ends_with($c, ']')) {
                $decoded = json_decode($c, true);
                if (is_array($decoded)) {
                    $existingColors = array_merge($existingColors, $decoded);
                    continue;
                }
            }
            $existingColors[] = $c;
        }
        $existingColors = array_values(array_unique(array_filter($existingColors)));

        $warehouses = \App\Models\Warehouse::whereHas('branches', function($q) use ($currentBranch) {
            $q->where('branch_id', $currentBranch);
        })->orderBy('warehouse_name')->get();

        return view('admin_panel.purchase_order.create', compact('vendors', 'branches', 'nextPO', 'currentBranch', 'currentBranchName', 'isSuperAdmin', 'existingColors', 'warehouses'));
    }

    /**
     * AJAX Helper: Fetch next PO number for a branch
     */
    public function getNextPONumber($branchId)
    {
        $branch = Branch::find($branchId);
        if (!$branch) return response()->json(['error' => 'Branch not found'], 404);

        $nextPONumber = ((int)($branch->po_counter ?? 0)) + 1;
        $nextPO = 'PO-B' . $branchId . '-' . str_pad($nextPONumber, 4, '0', STR_PAD_LEFT);

        return response()->json(['next_po' => $nextPO]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id'          => 'required|exists:branches,id',
            'warehouse_id'       => 'nullable|exists:warehouses,id',
            'vendor_id'          => 'required|exists:vendors,id',
            'order_date'         => 'required|date',
            'expected_date'      => 'required|date',
            'note'               => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.total_qty'  => 'required|numeric|min:0.01',
            'items.*.price'      => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $branchId = $request->branch_id;
            $branch = Branch::lockForUpdate()->find($branchId);
            $branch->po_counter = ((int)($branch->po_counter ?? 0)) + 1;
            $branch->save();

            $poNumber = 'PO-B' . $branchId . '-' . str_pad($branch->po_counter, 4, '0', STR_PAD_LEFT);

            $orderData = [
                'branch_id'     => $branchId,
                'vendor_id'     => $request->vendor_id,
                'po_number'     => $poNumber,
                'order_date'    => $request->order_date,
                'expected_date' => $request->expected_date,
                'note'          => $request->note,
                'created_by'    => Auth::id(),
                'status'        => 'pending',
                'total_amount'  => 0,
            ];

            if ($request->filled('warehouse_id')) {
                $orderData['warehouse_id'] = $request->warehouse_id;
            }

            $order = PurchaseOrder::create($orderData);

            $totalAmount = 0;

            foreach ($request->input('items', []) as $item) {
                $productId = $item['product_id'];
                $price     = (float)($item['price'] ?? 0);
                $colors    = array_filter($item['colors'] ?? [], fn($c) => trim($c) !== '');
                $colorQtys = $item['color_qtys'] ?? [];

                // If color breakdown exists — create one row per color
                if (!empty($colors)) {
                    foreach (array_values($colors) as $ci => $colorName) {
                        $qty = (float)($colorQtys[$ci] ?? 0);
                        if ($qty <= 0) continue;

                        $lineTotal    = $qty * $price;
                        $totalAmount += $lineTotal;

                        PurchaseOrderItem::create([
                            'purchase_order_id' => $order->id,
                            'product_id'        => $productId,
                            'qty'               => $qty,
                            'unit_price'        => $price,
                            'line_total'        => $lineTotal,
                            'unit'              => null,
                            'color'             => $colorName,
                        ]);
                    }
                } else {
                    // Standard single-row entry (no color breakdown)
                    $qty          = (float)($item['total_qty'] ?? 0);
                    $lineTotal    = $qty * $price;
                    $totalAmount += $lineTotal;

                    PurchaseOrderItem::create([
                        'purchase_order_id' => $order->id,
                        'product_id'        => $productId,
                        'qty'               => $qty,
                        'unit_price'        => $price,
                        'line_total'        => $lineTotal,
                        'unit'              => null,
                        'color'             => null,
                    ]);
                }
            }

            $order->update(['total_amount' => $totalAmount]);

            DB::commit();
            return redirect()->route('purchase_orders.index')->with('success', "Purchase Order {$poNumber} created successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating PO: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $order = PurchaseOrder::with('items.product.brand')->findOrFail($id);
        $branches = Branch::all();
        $isSuperAdmin = Auth::user()->hasRole('super admin');
        $vendors = Vendor::where('branch_id', $order->branch_id)->orderBy('name')->get();
        
        // Fetch all unique colors from products and PO items, handling JSON arrays
        $rawColors = Product::whereNotNull('color')->where('color', '!=', '')->distinct()->pluck('color')
            ->merge(PurchaseOrderItem::whereNotNull('color')->where('color', '!=', '')->distinct()->pluck('color'))
            ->unique()->toArray();
            
        $existingColors = [];
        foreach ($rawColors as $c) {
            if (str_starts_with($c, '[') && str_ends_with($c, ']')) {
                $decoded = json_decode($c, true);
                if (is_array($decoded)) {
                    $existingColors = array_merge($existingColors, $decoded);
                    continue;
                }
            }
            $existingColors[] = $c;
        }
        $existingColors = array_values(array_unique(array_filter($existingColors)));

        $warehouses = \App\Models\Warehouse::whereHas('branches', function($q) use ($order) {
            $q->where('branch_id', $order->branch_id);
        })->orderBy('warehouse_name')->get();

        return view('admin_panel.purchase_order.edit', compact('order', 'vendors', 'branches', 'isSuperAdmin', 'existingColors', 'warehouses'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'branch_id'          => 'required|exists:branches,id',
            'warehouse_id'       => 'required|exists:warehouses,id',
            'vendor_id'          => 'required|exists:vendors,id',
            'order_date'         => 'required|date',
            'expected_date'      => 'required|date',
            'note'               => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.price'      => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $order = PurchaseOrder::findOrFail($id);
            $updateData = [
                'branch_id'     => $request->branch_id,
                'vendor_id'     => $request->vendor_id,
                'order_date'    => $request->order_date,
                'expected_date' => $request->expected_date,
                'note'          => $request->note,
            ];

            if ($request->filled('warehouse_id')) {
                $updateData['warehouse_id'] = $request->warehouse_id;
            } else {
                $updateData['warehouse_id'] = null;
            }

            $order->update($updateData);

            // Delete old items and re-create
            $order->items()->delete();

            $totalAmount = 0;
            foreach ($request->input('items', []) as $item) {
                $productId = $item['product_id'];
                $price     = (float)($item['price'] ?? 0);
                
                if (isset($item['colors']) && is_array($item['colors'])) {
                    foreach ($item['colors'] as $ci => $colorName) {
                        $qty = (float)($item['color_qtys'][$ci] ?? 0);
                        if ($qty <= 0) continue;

                        $lineTotal    = $qty * $price;
                        $totalAmount += $lineTotal;

                        PurchaseOrderItem::create([
                            'purchase_order_id' => $order->id,
                            'product_id'        => $productId,
                            'qty'               => $qty,
                            'unit_price'        => $price,
                            'line_total'        => $lineTotal,
                            'color'             => $colorName,
                        ]);
                    }
                } else {
                    $qty          = (float)($item['total_qty'] ?? 0);
                    $lineTotal    = $qty * $price;
                    $totalAmount += $lineTotal;

                    PurchaseOrderItem::create([
                        'purchase_order_id' => $order->id,
                        'product_id'        => $productId,
                        'qty'               => $qty,
                        'unit_price'        => $price,
                        'line_total'        => $lineTotal,
                        'color'             => null,
                    ]);
                }
            }

            $order->update(['total_amount' => $totalAmount]);

            DB::commit();
            return redirect()->route('purchase_orders.index')->with('success', "Purchase Order #{$order->po_number} updated successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating PO: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $order = PurchaseOrder::with(['branch', 'vendor', 'items.product.brand', 'inwardGatepasses', 'creator'])->findOrFail($id);

        $brandNames = [];
        if ($order->vendor && !empty($order->vendor->brand_ids)) {
            $brandNames = \App\Models\Brand::whereIn('id', $order->vendor->brand_ids)->pluck('name')->toArray();
        }

        return view('admin_panel.purchase_order.show', compact('order', 'brandNames'));
    }

    public function print($id)
    {
        $order = PurchaseOrder::with(['branch', 'vendor', 'items.product.brand', 'creator'])->findOrFail($id);

        $brandNames = [];
        if ($order->vendor && !empty($order->vendor->brand_ids)) {
            $brandNames = \App\Models\Brand::whereIn('id', $order->vendor->brand_ids)->pluck('name')->toArray();
        }

        return view('admin_panel.purchase_order.print', compact('order', 'brandNames'));
    }

    public function destroy($id)
    {
        $order = PurchaseOrder::findOrFail($id);
        if ($order->inwardGatepasses()->exists()) {
            return back()->with('error', 'Cannot delete PO that has already been received via Gatepass.');
        }
        $order->delete();
        return redirect()->route('purchase_orders.index')->with('success', 'Purchase Order deleted successfully.');
    }

    public function convertToPurchase($id)
    {
        $order = PurchaseOrder::findOrFail($id);

        return redirect()->route('purchase.addLocal', ['po_id' => $id])
            ->with('success', "Converting PO #{$order->po_number} to Purchase Bill.");
    }

    /**
     * AJAX: Search PO by number and return its details for Inward Gatepass
     */
    public function searchByNumber(Request $request)
    {
        $q = $request->q;
        if (!$q) return response()->json(['error' => 'PO number required'], 400);

        // Try exact match or partial suffix match
        $query = PurchaseOrder::with(['items.product.brand', 'items.product.unit', 'vendor', 'branch'])
            ->where(function($qq) use ($q) {
                $qq->where('po_number', $q)
                   ->orWhere('po_number', 'LIKE', '%' . $q);
            });

        // Apply warehouse security
        $user = Auth::user();
        if (!$user->hasRole('super admin') && !$user->hasRole('admin')) {
            $query->whereIn('warehouse_id', $user->assignedWarehouseIds());
        }

        $po = $query->first();

        if (!$po) {
            return response()->json(['error' => 'Purchase Order not found'], 404);
        }

        return response()->json($po);
    }
}
