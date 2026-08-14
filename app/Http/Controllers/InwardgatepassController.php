<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\InwardGatepass;
use App\Models\InwardGatepassItem;
use App\Models\Stock;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Warehouse;
use App\Models\Vendor;
use App\Models\Purchase;
use App\Models\VendorRemaining;
use App\Models\Unit;
use App\Models\VendorLedger;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class InwardgatepassController extends Controller
{
    public function pdf($id)
    {
        $gatepass = InwardGatepass::with(['branch','warehouse','vendor','items.product'])->findOrFail($id);
        $pdf = Pdf::loadView('admin_panel.inward.pdf', compact('gatepass'));
        return $pdf->download('gatepass_'.$gatepass->id.'.pdf');
    }

    // LIST - Show pending items for each gatepass (ERP standard)
    public function index()
    {
        $gatepasses = InwardGatepass::with('items.product','branch','warehouse','vendor')->latest()->get();
        
        // For each gatepass, calculate pending items from related purchase and determine display status
        $gatepasses = $gatepasses->map(function ($gp) {
            if ($gp->purchase_id) {
                $gp->pending_count = VendorRemaining::where('purchase_id', $gp->purchase_id)
                    ->pending()
                    ->sum('remaining_qty');
                
                // ✅ ERP Standard: Determine display status based on completion
                // If all items received (no pending), show "Completed" regardless of DB status
                if ($gp->pending_count == 0) {
                    $gp->display_status = 'completed';
                } else {
                    // Otherwise show "Pending" for active purchases
                    $gp->display_status = $gp->status == 'cancelled' ? 'cancelled' : 'pending';
                }
            } else {
                $gp->pending_count = 0;
                // Non-purchase gatepass - use DB status
                $gp->display_status = $gp->status;
            }
            return $gp;
        });
        
        return view('admin_panel.inward.index', compact('gatepasses'));
    }

    // CREATE FORM
    public function create()
    {
        $branches   = Branch::orderBy('name')->get();
        $isSuperAdmin = Auth::user()->hasRole('super admin');
        
        $userBranchId = Auth::user()->branch_id ?? 1;
        $warehouses = Warehouse::whereHas('branches', function($q) use ($userBranchId) {
            $q->where('branch_id', $userBranchId);
        })->orderBy('warehouse_name')->get();
        
        $vendors    = Vendor::where('branch_id', $userBranchId)->orderBy('name')->get();
        $purchase   = null;
        $vendorRemaining = collect();
        
        $allProducts = Product::with(['brand', 'unit'])->orderBy('item_name')->get();
        $allUnits = Unit::orderBy('name')->get();
        $po = null;
        $existingColors = $this->getExistingColors();

        return view('admin_panel.inward.create', compact('branches','warehouses','vendors','purchase','vendorRemaining','isSuperAdmin', 'allProducts', 'allUnits', 'po', 'existingColors'));
    }

    public function createFromPurchase($purchaseId)
    {
        $purchase = Purchase::with([
            'items.product.brand',
            'items.product.unit',
            'vendor',
            'branch',
            'warehouse'
        ])->findOrFail($purchaseId);
        
        $vendorRemaining = VendorRemaining::where('purchase_id', $purchaseId)
            ->get()
            ->keyBy('product_id');
        
        $branches   = Branch::orderBy('name')->get();
        $vendors    = Vendor::where('branch_id', $purchase->branch_id)->orderBy('name')->get();
        $isSuperAdmin = Auth::user()->hasRole('super admin');
        
        $warehouses = Warehouse::whereHas('branches', function($q) use ($purchase) {
            $q->where('branch_id', $purchase->branch_id);
        })->orderBy('warehouse_name')->get();

        $allUnits = Unit::orderBy('name')->get();
        $po = null;
        $existingColors = $this->getExistingColors();

        // Filter products by vendor if possible
        $brandIds = $purchase->vendor->brand_ids ?? [];
        if (!empty($brandIds)) {
            $allProducts = Product::with(['brand', 'unit'])
                ->whereIn('brand_id', $brandIds)
                ->orderBy('item_name')
                ->get();
        } else {
            $allProducts = Product::with(['brand', 'unit'])->orderBy('item_name')->get();
        }

        return view('admin_panel.inward.create', compact('branches','warehouses','vendors','purchase','vendorRemaining','isSuperAdmin','allProducts','allUnits', 'po', 'existingColors'));
    }

    public function createFromPO($poId)
    {
        $po = \App\Models\PurchaseOrder::with(['items.product.brand', 'items.product.unit', 'vendor', 'branch'])->findOrFail($poId);
        
        // Security Check: Warehouse Level
        $user = Auth::user();
        if (!$user->hasRole('super admin') && !$user->hasRole('admin')) {
            if (!in_array($po->warehouse_id, $user->assignedWarehouseIds())) {
                return redirect()->route('InwardGatepass.home')->with('error', 'Unauthorized access to this Purchase Order.');
            }
        }
        
        $branches = Branch::orderBy('name')->get();
        $vendors = Vendor::where('branch_id', $po->branch_id)->orderBy('name')->get();
        $isSuperAdmin = Auth::user()->hasRole('super admin');
        
        $warehouses = Warehouse::whereHas('branches', function($q) use ($po) {
            $q->where('branch_id', $po->branch_id);
        })->orderBy('warehouse_name')->get();

        $allUnits = Unit::orderBy('name')->get();
        $existingColors = $this->getExistingColors();

        // Filter products by vendor brands
        $brandIds = $po->vendor->brand_ids ?? [];
        if (!empty($brandIds)) {
            $allProducts = Product::with(['brand', 'unit'])
                ->whereIn('brand_id', $brandIds)
                ->orderBy('item_name')
                ->get();
        } else {
            $allProducts = Product::with(['brand', 'unit'])->orderBy('item_name')->get();
        }

        return view('admin_panel.inward.create', [
            'branches' => $branches,
            'warehouses' => $warehouses,
            'vendors' => $vendors,
            'po' => $po,
            'purchase' => null,
            'vendorRemaining' => collect(),
            'isSuperAdmin' => $isSuperAdmin,
            'allProducts' => $allProducts,
            'allUnits' => $allUnits,
            'existingColors' => $existingColors
        ]);
    }

    public function store(Request $request)
    {
        Log::info('INWARD GATEPASS STORE PAYLOAD:', $request->all());
        
        $request->validate([
            'branch_id'      => 'required|exists:branches,id',
            'warehouse_id'   => 'required|exists:warehouses,id',
            'vendor_id'      => 'required|exists:vendors,id',
            'purchase_id'    => 'nullable|exists:purchases,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'gatepass_date'  => 'required|date',
            'items'          => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.received_qty' => 'required|numeric|min:0',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.line_total' => 'nullable|numeric|min:0',
        ]);

        $purchaseId = $request->purchase_id;
        $poId = $request->purchase_order_id;
        $itemsData = $request->input('items', []);

        // Validate quantities against ordered amounts
        foreach ($itemsData as $item) {
            $pid = $item['product_id'];
            $receivedQty = (float)$item['received_qty'];

            if ($purchaseId) {
                $purchaseItem = PurchaseItem::where('purchase_id', $purchaseId)->where('product_id', $pid)->first();
                if (!$purchaseItem) return back()->with('error', "Product ID {$pid} not found in this purchase.");
                
                $vendorRemaining = VendorRemaining::where('purchase_id', $purchaseId)->where('product_id', $pid)->first();
                $alreadyReceived = $vendorRemaining->received_qty ?? 0;
                if (($alreadyReceived + $receivedQty) > $purchaseItem->qty) {
                    $product = Product::find($pid);
                    return back()->with('error', "❌ {$product->item_name}: Cannot exceed ordered qty.");
                }
            }

            if ($poId) {
                if (isset($item['colors']) && is_array($item['colors'])) {
                    // Validate each color individually
                    foreach ($item['colors'] as $cIdx => $color) {
                        $cQty = (float)($item['color_qtys'][$cIdx] ?? 0);
                        if ($cQty <= 0) continue;

                        $poItem = \App\Models\PurchaseOrderItem::where('purchase_order_id', $poId)
                            ->where('product_id', $pid)
                            ->where('color', $color)
                            ->first();
                        
                        if (!$poItem) {
                            return back()->with('error', "❌ Product ID {$pid} with color '{$color}' not found in this PO.");
                        }
                        
                        if (($poItem->received_qty + $cQty) > $poItem->qty) {
                            $product = Product::find($pid);
                            return back()->with('error', "❌ {$product->item_name} ({$color}): Cannot exceed PO ordered qty ({$poItem->qty}).");
                        }
                    }
                } else {
                    // Standard validation for items without color breakdown
                    $poItem = \App\Models\PurchaseOrderItem::where('purchase_order_id', $poId)
                        ->where('product_id', $pid)
                        ->whereNull('color')
                        ->first();

                    if (!$poItem) {
                        // Fallback: Check total if specifically null color not found
                        $totalOrdered = \App\Models\PurchaseOrderItem::where('purchase_order_id', $poId)
                            ->where('product_id', $pid)
                            ->sum('qty');
                        $totalReceived = \App\Models\PurchaseOrderItem::where('purchase_order_id', $poId)
                            ->where('product_id', $pid)
                            ->sum('received_qty');
                        
                        if (($totalReceived + $receivedQty) > $totalOrdered) {
                            $product = Product::find($pid);
                            return back()->with('error', "❌ {$product->item_name}: Cannot exceed total PO ordered qty.");
                        }
                    } else {
                        if (($poItem->received_qty + $receivedQty) > $poItem->qty) {
                            $product = Product::find($pid);
                            return back()->with('error', "❌ {$product->item_name}: Cannot exceed PO ordered qty.");
                        }
                    }
                }
            }
        }

        DB::transaction(function () use ($request, $itemsData, $purchaseId, $poId) {
            $gatepass = InwardGatepass::create([
                'branch_id'      => $request->branch_id,
                'warehouse_id'   => $request->warehouse_id,
                'vendor_id'      => $request->vendor_id,
                'purchase_id'    => $purchaseId,
                'purchase_order_id' => $poId,
                'gatepass_date'  => $request->gatepass_date,
                'note'           => $request->note,
                'transport_name' => $request->transport_name,
                'bilty_no'       => $request->bilty_no,
                'vehicle_type'   => $request->vehicle_type,
                'vehicle_no'     => $request->vehicle_no,
                'driver_name'    => $request->driver_name,
                'driver_no'      => $request->driver_no,
                'dispatch_date'  => $request->dispatch_date,
                'delivery_challan_no' => $request->delivery_challan_no,
                'freight_charges'=> $request->freight_charges,
                'freight_vendor_id' => $request->freight_vendor_id,
                'created_by'     => auth()->id(),
                'status'         => 'pending',
            ]);

            $now = now();
            foreach ($itemsData as $item) {
                $pid = (int)$item['product_id'];
                
                // Handle Color Breakdown
                if (isset($item['colors']) && is_array($item['colors'])) {
                    foreach ($item['colors'] as $cIdx => $color) {
                        $cQty = (float)($item['color_qtys'][$cIdx] ?? 0);
                        if ($cQty <= 0) continue;

                        $this->createInwardItem($gatepass, $pid, $cQty, $item, $color);
                        $this->processStockMovement($gatepass, $pid, $cQty, $request->branch_id, $request->warehouse_id, $purchaseId, $poId, $request->vendor_id, $color);
                    }
                } else {
                    // Standard single row
                    $receivedQty = (float)$item['received_qty'];
                    if ($receivedQty <= 0) continue;

                    $this->createInwardItem($gatepass, $pid, $receivedQty, $item);
                    $this->processStockMovement($gatepass, $pid, $receivedQty, $request->branch_id, $request->warehouse_id, $purchaseId, $poId, $request->vendor_id, null);
                }
            }

            // --- FREIGHT CHARGES AUDIT / PAYABLE LOGIC ---
            if ($request->freight_charges > 0 && $request->freight_vendor_id) {
                // 1. Credit the Freight Vendor Ledger (Liability increases)
                $lastVendorLedger = \App\Models\VendorLedger::where('vendor_id', $request->freight_vendor_id)
                    ->where('branch_id', $request->branch_id)
                    ->orderBy('id', 'desc')
                    ->first();
                $previousBalance = $lastVendorLedger ? (float)$lastVendorLedger->closing_balance : (float)(\App\Models\Vendor::find($request->freight_vendor_id)->opening_balance ?? 0);
                
                \App\Models\VendorLedger::create([
                    'vendor_id'        => $request->freight_vendor_id,
                    'branch_id'        => $request->branch_id,
                    'admin_or_user_id' => auth()->id(),
                    'transaction_date' => $request->gatepass_date,
                    'description'      => "Freight Charges for Inward Gatepass #{$gatepass->id}",
                    'opening_balance'  => $previousBalance,
                    'previous_balance' => $previousBalance,
                    'debit_amount'     => 0,
                    'credit_amount'    => $request->freight_charges, // We owe them this amount
                    'closing_balance'  => $previousBalance + $request->freight_charges,
                ]);

                // 2. Debit the Freight Inward Expense Account
                $freightExpenseAcc = \App\Models\Account::where('account_code', 'EXP-FRT-001')->first();
                if ($freightExpenseAcc) {
                    $freightExpenseAcc->opening_balance += $request->freight_charges;
                    $freightExpenseAcc->save();
                }
            }

        });

        return redirect()->route('InwardGatepass.home')->with('success','Inward Gatepass Created Successfully');
    }

    private function createInwardItem($gatepass, $pid, $qty, $itemData, $color = null)
    {
        InwardGatepassItem::create([
            'inward_gatepass_id' => $gatepass->id,
            'product_id'         => $pid,
            'qty'                => $qty,
            'color'              => $color,
            'packing_type'       => $itemData['packing_type'] ?? null,
            'packing_qty'        => $itemData['packing_qty'] ?? null,
            'item_per_piece'     => $itemData['item_per_piece'] ?? null,
            'loose_piece'        => $itemData['loose_piece'] ?? null,
            'unit'               => $itemData['unit'] ?? null,
        ]);
    }

    private function processStockMovement($gatepass, $pid, $qty, $branchId, $warehouseId, $purchaseId, $poId, $vendorId, $color = null)
    {
        $now = now();
        DB::table('stock_movements')->insert([
            'product_id' => $pid,
            'branch_id'  => (int)$branchId,
            'type'       => 'in',
            'qty'        => $qty,
            'ref_type'   => 'INWARD',
            'ref_id'     => $gatepass->id,
            'note'       => 'Inward gatepass',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->upsertStocks($pid, +$qty, (int)$branchId, (int)$warehouseId);

        if ($purchaseId) {
            $this->handleVendorRemaining($purchaseId, $pid, $qty, (int)$vendorId, (int)$warehouseId);
        }

        if ($poId) {
            $this->handlePOReceipt($poId, $pid, $qty, $color);
        }
    }

    private function handlePOReceipt($poId, $productId, $receivedQty, $color = null)
    {
        $query = \App\Models\PurchaseOrderItem::where('purchase_order_id', $poId)->where('product_id', $productId);
        
        if ($color) {
            $query->where('color', $color);
        } else {
            $query->whereNull('color');
        }

        $item = $query->first();
        if ($item) {
            $item->increment('received_qty', $receivedQty);
        }
        
        $po = \App\Models\PurchaseOrder::with('items')->find($poId);
        $totalOrdered = $po->items->sum('qty');
        $totalReceived = $po->items->sum('received_qty');

        if ($totalReceived >= $totalOrdered) {
            $po->update(['status' => 'received']);
        } elseif ($totalReceived > 0) {
            $po->update(['status' => 'partially_received']);
        }
    }


    /**
     * Handle vendor remaining tracking for partial deliveries
     */
    private function handleVendorRemaining(
        int $purchaseId,
        int $productId,
        float $receivedQty,
        int $vendorId,
        int $warehouseId
    ): void {
        // Get the original ordered qty from purchase_items
        $purchaseItem = DB::table('purchase_items')
            ->where('purchase_id', $purchaseId)
            ->where('product_id', $productId)
            ->first();

        if (!$purchaseItem) return;

        $orderedQty = $purchaseItem->qty;

        // Check if vendor_remaining record exists
        $existing = VendorRemaining::where('purchase_id', $purchaseId)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            // Update existing record
            $newReceivedQty = $existing->received_qty + $receivedQty;
            $newRemainingQty = $orderedQty - $newReceivedQty;
            $newStatus = $newRemainingQty <= 0 ? 'completed' : ($newReceivedQty > 0 ? 'partial' : 'pending');

            $existing->update([
                'received_qty'  => $newReceivedQty,
                'remaining_qty' => max(0, $newRemainingQty),
                'status'        => $newStatus,
                'warehouse_id'  => $warehouseId,
                'vendor_id'     => $vendorId,
            ]);
        } else {
            // Create new record
            $remainingQty = $orderedQty - $receivedQty;
            $status = $remainingQty <= 0 ? 'completed' : 'pending';

            VendorRemaining::create([
                'purchase_id'   => $purchaseId,
                'vendor_id'     => $vendorId,
                'product_id'    => $productId,
                'warehouse_id'  => $warehouseId,
                'ordered_qty'   => $orderedQty,
                'received_qty'  => $receivedQty,
                'remaining_qty' => max(0, $remainingQty),
                'status'        => $status,
            ]);
        }
    }

    // SHOW - International ERP Standard with Pending Deliveries
    public function show($id)
    {
        $gatepass = InwardGatepass::with('items.product','branch','warehouse','vendor')->findOrFail($id);
        
        // Get pending items from same purchase (ERP standard: show remaining items)
        $pendingItems = [];
        if ($gatepass->purchase_id) {
            $pendingItems = VendorRemaining::with(['product', 'warehouse'])
                ->where('purchase_id', $gatepass->purchase_id)
                ->pending()
                ->get();
            
            // ✅ ERP Standard: Calculate display status based on completion
            $pendingCount = $pendingItems->sum('remaining_qty');
            if ($pendingCount == 0) {
                $gatepass->display_status = 'completed';
            } else {
                $gatepass->display_status = $gatepass->status == 'cancelled' ? 'cancelled' : 'pending';
            }
        } else {
            $gatepass->display_status = $gatepass->status;
        }
        
        return view('admin_panel.inward.show', compact('gatepass', 'pendingItems'));
    }

    // EDIT FORM
    public function edit($id)
    {
        $gatepass   = InwardGatepass::with(['items.product.brand', 'items.product.unit'])->findOrFail($id);
        
        // Map purchase items prices to gatepass items if a purchase is linked
        $purchase = null;
        $purchaseItemsMap = collect();
        if ($gatepass->purchase_id) {
            $purchase = Purchase::with('items')->find($gatepass->purchase_id);
            if ($purchase) {
                $purchaseItemsMap = $purchase->items->groupBy(fn($i) => $i->product_id . '_' . ($i->color ?? ''));
            }
        }

        foreach ($gatepass->items as $item) {
            $key = $item->product_id . '_' . ($item->color ?? '');
            if ($purchase && $purchaseItemsMap->has($key)) {
                $item->unit_price = (float)$purchaseItemsMap->get($key)->first()->price;
            } else {
                $item->unit_price = (float)($item->product->wholesale_price ?? 0);
            }
        }

        $branches   = Branch::orderBy('name')->get();
        $isSuperAdmin = Auth::user()->hasRole('super admin');
        
        $warehouses = Warehouse::whereHas('branches', function($q) use ($gatepass) {
            $q->where('branch_id', $gatepass->branch_id);
        })->orderBy('warehouse_name')->get();
        
        $vendors    = Vendor::orderBy('name')->get();
        $allUnits = Unit::orderBy('name')->get();
        $po = null;
        $existingColors = $this->getExistingColors();
        $allProducts = Product::with(['brand', 'unit'])->orderBy('item_name')->get();

        return view('admin_panel.inward.edit', compact('gatepass','branches','warehouses','vendors','isSuperAdmin', 'allUnits', 'po', 'existingColors', 'allProducts', 'purchase'));
    }

    // UPDATE (delta movements + stocks)
    public function update(Request $request, $id)
    {
        $request->validate([
            'branch_id'      => 'required|exists:branches,id',
            'warehouse_id'   => 'required|exists:warehouses,id',
            'vendor_id'      => 'required|exists:vendors,id',
            'gatepass_date'  => 'required|date',
            'items'          => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.received_qty' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($request, $id) {
            $gatepass = InwardGatepass::with('items')->findOrFail($id);
            $oldBranch = (int)$gatepass->branch_id;
            $oldWh     = (int)$gatepass->warehouse_id;

            // Map of old totals for deltas (grouped by product + color)
            $oldMap = $gatepass->items->map(function($i) {
                return [
                    'key' => $i->product_id . '_' . ($i->color ?? ''),
                    'pid' => $i->product_id,
                    'color' => $i->color,
                    'qty' => (float)$i->qty
                ];
            })->groupBy('key')->map(fn($g) => [
                'pid' => $g[0]['pid'],
                'color' => $g[0]['color'],
                'qty' => (float)$g->sum('qty')
            ]);

            // Map of new totals (grouped by product + color)
            $itemsData = $request->input('items', []);
            $newItemsList = [];
            foreach ($itemsData as $item) {
                $pid = (int)$item['product_id'];
                if (isset($item['colors']) && is_array($item['colors'])) {
                    foreach ($item['colors'] as $cIdx => $color) {
                        $cQty = (float)($item['color_qtys'][$cIdx] ?? 0);
                        if ($cQty <= 0) continue;
                        $newItemsList[] = ['pid' => $pid, 'color' => $color, 'qty' => $cQty];
                    }
                } else {
                    $newItemsList[] = ['pid' => $pid, 'color' => null, 'qty' => (float)$item['received_qty']];
                }
            }

            $newMap = collect($newItemsList)->map(function($i) {
                return [
                    'key' => $i['pid'] . '_' . ($i['color'] ?? ''),
                    'pid' => $i['pid'],
                    'color' => $i['color'],
                    'qty' => (float)$i['qty']
                ];
            })->groupBy('key')->map(fn($g) => [
                'pid' => $g[0]['pid'],
                'color' => $g[0]['color'],
                'qty' => (float)$g->sum('qty')
            ]);

            // header update
            $gatepass->update([
                'branch_id'      => $request->branch_id,
                'warehouse_id'   => $request->warehouse_id,
                'vendor_id'      => $request->vendor_id,
                'gatepass_date'  => $request->gatepass_date,
                'note'           => $request->note,
                'transport_name' => $request->transport_name,
                'bilty_no'       => $request->bilty_no,
                'vehicle_type'   => $request->vehicle_type,
                'vehicle_no'     => $request->vehicle_no,
                'driver_name'    => $request->driver_name,
                'driver_no'      => $request->driver_no,
                'dispatch_date'  => $request->dispatch_date,
                'delivery_challan_no' => $request->delivery_challan_no,
                'freight_charges'=> $request->freight_charges,
            ]);

            // replace items
            InwardGatepassItem::where('inward_gatepass_id', $gatepass->id)->delete();
            foreach ($itemsData as $item) {
                $pid = (int)$item['product_id'];
                if (isset($item['colors']) && is_array($item['colors'])) {
                    foreach ($item['colors'] as $cIdx => $color) {
                        $cQty = (float)($item['color_qtys'][$cIdx] ?? 0);
                        if ($cQty <= 0) continue;
                        $this->createInwardItem($gatepass, $pid, $cQty, $item, $color);
                    }
                } else {
                    $this->createInwardItem($gatepass, $pid, (float)$item['received_qty'], $item);
                }
            }

            // process deltas for stock and tracking
            $allKeys = $oldMap->keys()->merge($newMap->keys())->unique();
            $now = now();
            foreach ($allKeys as $key) {
                $oldData = $oldMap[$key] ?? ['pid' => explode('_', $key)[0], 'color' => explode('_', $key)[1] ?: null, 'qty' => 0];
                $newData = $newMap[$key] ?? ['pid' => explode('_', $key)[0], 'color' => explode('_', $key)[1] ?: null, 'qty' => 0];
                
                $pid   = (int)$oldData['pid'];
                $color = $oldData['color'];
                $oldQ  = (float)$oldData['qty'];
                $newQ  = (float)$newData['qty'];
                
                $delta = $newQ - $oldQ;

                // ═══════════════════════════════════════════════════════════
                // STEP 1: Rollback old stock from old branch & warehouse
                // ═══════════════════════════════════════════════════════════
                if ($oldQ > 0) {
                    DB::table('stock_movements')->insert([
                        'product_id' => $pid,
                        'branch_id'  => $oldBranch,
                        'type'       => 'out',
                        'qty'        => $oldQ,
                        'ref_type'   => 'INWARD_EDIT_REMOVE',
                        'ref_id'     => $gatepass->id,
                        'note'       => 'Deduct old inward gatepass stock',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    $this->upsertStocks($pid, -$oldQ, $oldBranch, $oldWh);
                }

                // ═══════════════════════════════════════════════════════════
                // STEP 2: Apply new stock to new branch & warehouse
                // ═══════════════════════════════════════════════════════════
                if ($newQ > 0) {
                    DB::table('stock_movements')->insert([
                        'product_id' => $pid,
                        'branch_id'  => (int)$request->branch_id,
                        'type'       => 'in',
                        'qty'        => $newQ,
                        'ref_type'   => 'INWARD_EDIT_ADD',
                        'ref_id'     => $gatepass->id,
                        'note'       => 'Add new inward gatepass stock',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    $this->upsertStocks($pid, +$newQ, (int)$request->branch_id, (int)$request->warehouse_id);
                }

                // ═══════════════════════════════════════════════════════════
                // STEP 3: Handle tracking delta for Vendor/PO
                // ═══════════════════════════════════════════════════════════
                if ($gatepass->purchase_id && $delta != 0) {
                    $this->handleVendorRemaining((int)$gatepass->purchase_id, $pid, $delta, (int)$request->vendor_id, (int)$request->warehouse_id);
                }
                
                if ($gatepass->purchase_order_id && $delta != 0) {
                    $this->handlePOReceipt((int)$gatepass->purchase_order_id, $pid, $delta, $color);
                }
            }

            if ($gatepass->purchase_id) {
                $this->syncPurchaseWithGatepass($gatepass);
            }
        });

        return redirect()->route('InwardGatepass.home')->with('success','Inward Gatepass Updated Successfully');
    }

    // DELETE (reverse movements + stocks)
    public function destroy($id)
{
    DB::transaction(function () use ($id) {
        $gatepass = InwardGatepass::with('items')->findOrFail($id);
        $now = now();
        $movs = [];

        foreach ($gatepass->items as $item) {
            // log reverse movement
            $movs[] = [
                'product_id' => (int)$item->product_id,
                'branch_id'  => (int)$gatepass->branch_id,  // ✅ ERP STANDARD: Track branch
                'type'       => 'out',
                'qty'        => (float)$item->qty,
                'ref_type'   => 'INWARD_DELETE',
                'ref_id'     => $gatepass->id,
                'note'       => 'Delete inward (reverse)',
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Direct stock rollback - update both warehouse_stocks and stocks tables
            
            // STEP 1: Rollback warehouse_stocks
            $warehouseStock = DB::table('warehouse_stocks')
                ->where('product_id', $item->product_id)
                ->where('branch_id', $gatepass->branch_id)
                ->where('warehouse_id', $gatepass->warehouse_id)
                ->first();

            if ($warehouseStock) {
                $newQty = max(0, $warehouseStock->quantity - $item->qty);
                DB::table('warehouse_stocks')
                    ->where('id', $warehouseStock->id)
                    ->update([
                        'quantity'   => $newQty,
                        'updated_at' => $now,
                    ]);
            }

            // STEP 2: Rollback stocks (also deduct - branch total only)
            $stock = DB::table('stocks')
                ->where('product_id', $item->product_id)
                ->where('branch_id', $gatepass->branch_id)
                ->first();

            if ($stock) {
                $newStockQty = max(0, $stock->qty - $item->qty);
                DB::table('stocks')
                    ->where('id', $stock->id)
                    ->update([
                        'qty'        => $newStockQty,
                        'updated_at' => $now,
                    ]);
            }
        }

        if (!empty($movs)) {
            DB::table('stock_movements')->insert($movs);
        }

        InwardGatepassItem::where('inward_gatepass_id', $gatepass->id)->delete();
        $gatepass->delete();
    });

    return redirect()->route('InwardGatepass.home')
                     ->with('success','Inward Gatepass Deleted Successfully');
}

    // PRODUCT SEARCH (grouped where fix)
    public function searchProducts(Request $request)
    {
        $q = $request->get('q','');
        $vendorId = $request->get('vendor_id');

        $query = Product::with(['brand', 'unit']);
        
        // Filter by vendor brands if vendor_id is provided
        if ($vendorId) {
            $vendor = Vendor::find($vendorId);
            if ($vendor && !empty($vendor->brand_ids)) {
                $query->whereIn('brand_id', $vendor->brand_ids);
            } else if ($vendor) {
                // If vendor has NO brands assigned, they see NO products
                $query->whereRaw('1=0');
            }
        }

        $products = $query
            ->where(function($x) use ($q){
                $x->where('item_name','like',"%{$q}%")
                  ->orWhere('item_code','like',"%{$q}%");
            })
            ->limit(20)
            ->get();

        // Add ownership information
        $userBranchId = Auth::check() ? Auth::user()->branch_id : null;
        $productsWithOwnership = $products->map(function($p) use ($userBranchId) {
            return [
                'id' => $p->id,
                'item_name' => $p->item_name,
                'item_code' => $p->item_code,
                'brand_name' => $p->brand->name ?? '',
                'unit_name' => $p->unit->name ?? 'unit',
                'price' => $p->price,
                'retail_price' => $p->retail_price ?? $p->price,
                'branch_id' => $p->branch_id,
                'is_owner' => ($userBranchId && $p->branch_id == $userBranchId),
            ];
        });

        return response()->json($productsWithOwnership);
    }

    // THERMAL PRINT - Compact receipt format for 80mm thermal printer
    public function thermal($id)
    {
        $gatepass = InwardGatepass::with(['branch','warehouse','vendor','items.product'])->findOrFail($id);
        return view('admin_panel.inward.thermal', compact('gatepass'));
    }

    // --- small helper (same as ProductController) ---
    /**
     * Update both warehouse_stocks and stocks tables (ERP standard - dual sync)
     * 
     * warehouse_stocks = Detail table (warehouse-specific inventory)
     * stocks = Summary table (branch-total inventory)
     * 
     * When qty changes: old + new = total (additive)
     */
    private function upsertStocks(int $productId, float $qtyDelta, int $branchId, int $warehouseId): void
    {
        // ═══════════════════════════════════════════════════════════
        // STEP 1: Update warehouse_stocks (detail table)
        // ═══════════════════════════════════════════════════════════
        $affectedWarehouse = DB::table('warehouse_stocks')
            ->where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->where('warehouse_id', $warehouseId)
            ->update([
                'quantity'   => DB::raw('quantity + '.((int)$qtyDelta)),
                'updated_at' => now(),
            ]);

        // Create warehouse_stocks record if doesn't exist
        if ($affectedWarehouse === 0) {
            DB::table('warehouse_stocks')->insert([
                'branch_id'    => $branchId,
                'warehouse_id' => $warehouseId,
                'product_id'   => $productId,
                'quantity'     => (int)$qtyDelta,
                'price'        => null,
                'remarks'      => 'Inward gatepass stock',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // ═══════════════════════════════════════════════════════════
        // STEP 2: Update stocks (summary table - branch total only)
        // Note: stocks table aggregates ALL warehouses in branch
        // Columns: branch_id, product_id, qty (NO warehouse_id)
        // ═══════════════════════════════════════════════════════════
        $affectedStocks = DB::table('stocks')
            ->where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->update([
                'qty'        => DB::raw('qty + '.((int)$qtyDelta)),
                'updated_at' => now(),
            ]);

        // Create stocks record if doesn't exist
        if ($affectedStocks === 0) {
            DB::table('stocks')->insert([
                'branch_id'    => $branchId,
                'product_id'   => $productId,
                'qty'          => (int)$qtyDelta,
                'reserved_qty' => 0,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }

    private function getExistingColors()
    {
        $raw = DB::table('purchase_order_items')->whereNotNull('color')->where('color', '!=', '')->distinct()->pluck('color')
            ->merge(DB::table('inward_gatepass_items')->whereNotNull('color')->where('color', '!=', '')->distinct()->pluck('color'))
            ->merge(DB::table('products')->whereNotNull('color')->where('color', '!=', '')->distinct()->pluck('color'))
            ->unique()->toArray();

        $flattened = [];
        foreach ($raw as $c) {
            if (str_starts_with($c, '[') && str_ends_with($c, ']')) {
                $decoded = json_decode($c, true);
                if (is_array($decoded)) {
                    $flattened = array_merge($flattened, $decoded);
                    continue;
                }
            }
            $flattened[] = $c;
        }
        return array_values(array_unique(array_filter($flattened)));
    }

    /**
     * ✅ ERP STANDARD: Sync linked Purchase Invoice with Gatepass changes
     * 
     * @param InwardGatepass $gatepass
     * @return void
     */
    private function syncPurchaseWithGatepass(InwardGatepass $gatepass)
    {
        $purchase = Purchase::with('items')->find($gatepass->purchase_id);
        if (!$purchase) return;

        // Sync header parameters if changed
        $purchase->branch_id = $gatepass->branch_id;
        $purchase->warehouse_id = $gatepass->warehouse_id;
        $purchase->vendor_id = $gatepass->vendor_id;

        // Get gatepass items grouped by product + color
        $gpItems = InwardGatepassItem::where('inward_gatepass_id', $gatepass->id)
            ->get()
            ->groupBy(fn($i) => $i->product_id . '_' . ($i->color ?? ''))
            ->map(fn($rows) => [
                'pid' => $rows[0]->product_id,
                'color' => $rows[0]->color,
                'qty' => (float)$rows->sum('qty')
            ]);

        $existingPurchaseItems = $purchase->items->keyBy(fn($i) => $i->product_id . '_' . ($i->color ?? ''));
        
        // 1. Update or Create Purchase Items
        foreach ($gpItems as $key => $data) {
            $pid = $data['pid'];
            $color = $data['color'];
            $qty = $data['qty'];

            if ($existingPurchaseItems->has($key)) {
                $pItem = $existingPurchaseItems->get($key);
                $pItem->qty = $qty;
                $pItem->warehouse_id = $purchase->warehouse_id;
                $pItem->line_total = ($pItem->price * $qty) - ($pItem->item_discount ?? 0);
                $pItem->save();

                // Sync tracking for existing item
                \App\Models\VendorRemaining::updateOrCreate(
                    ['purchase_id' => $purchase->id, 'product_id' => $pid],
                    [
                        'ordered_qty'   => $qty,
                        'received_qty'  => $qty,
                        'remaining_qty' => 0,
                        'status'        => 'completed',
                        'warehouse_id'  => $purchase->warehouse_id,
                        'vendor_id'     => $purchase->vendor_id
                    ]
                );
            } else {
                // New item added to gatepass, add to purchase too
                $product = Product::find($pid);
                $price = $product ? (float)($product->wholesale_price ?? 0) : 0;
                
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id'  => $pid,
                    'qty'         => $qty,
                    'price'       => $price,
                    'line_total'  => ($price * $qty),
                    'warehouse_id'=> $purchase->warehouse_id,
                    'color'       => $color,
                ]);

                // Also create VendorRemaining tracking for this new item
                \App\Models\VendorRemaining::updateOrCreate(
                    ['purchase_id' => $purchase->id, 'product_id' => $pid],
                    [
                        'vendor_id'     => $purchase->vendor_id,
                        'ordered_qty'   => $qty,
                        'received_qty'  => $qty,
                        'remaining_qty' => 0,
                        'status'        => 'completed',
                        'warehouse_id'  => $purchase->warehouse_id
                    ]
                );
            }
        }

        // 2. Remove items from Purchase that are no longer in Gatepass
        foreach ($existingPurchaseItems as $key => $pItem) {
            if (!$gpItems->has($key)) {
                $pid = $pItem->product_id;
                // Delete tracking record first
                \App\Models\VendorRemaining::where('purchase_id', $purchase->id)
                    ->where('product_id', $pid)
                    ->delete();
                
                $pItem->delete();
            }
        }

        // 3. Recalculate Purchase Header
        $purchase->load('items'); // reload to get updated items
        $subtotal = (float)$purchase->items->sum('line_total');
        
        $purchase->subtotal = $subtotal;
        $purchase->net_amount = $subtotal + (float)$purchase->extra_cost - (float)$purchase->discount;
        $purchase->due_amount = $purchase->net_amount - (float)$purchase->paid_amount;
        $purchase->save();

        // 4. ✅ ERP STANDARD: Sync Vendor Ledger
        $ledgerEntry = VendorLedger::where('reference_id', $purchase->id)
            ->where('transaction_type', 'purchase')
            ->first();

        if (!$ledgerEntry) {
            $ledgerEntry = VendorLedger::where('vendor_id', $purchase->vendor_id)
                ->where('description', 'LIKE', "%#{$purchase->invoice_no}%")
                ->orderBy('id', 'desc')
                ->first();
        }

        if ($ledgerEntry) {
            $oldVendorId = $ledgerEntry->vendor_id;
            $newVendorId = $purchase->vendor_id;
            
            $ledgerEntry->vendor_id = $newVendorId;
            $ledgerEntry->branch_id = $purchase->branch_id;
            $ledgerEntry->credit_amount = $purchase->net_amount;
            $ledgerEntry->description = "Purchase Invoice #{$purchase->invoice_no}";
            $ledgerEntry->save();

            // Recalculate for the old vendor if the vendor changed
            if ($oldVendorId != $newVendorId) {
                $this->recalculateLedgerBalances($oldVendorId);
            }
            
            // Recalculate for the current/new vendor
            $this->recalculateLedgerBalances($newVendorId);

            \Log::info("Synced Purchase Ledger for {$purchase->invoice_no}.");
        } else {
            // Create a new ledger entry if somehow it didn't exist
            \App\Services\VendorLedgerService::recordPurchase(
                vendorId: $purchase->vendor_id,
                amount: $purchase->net_amount,
                purchaseId: $purchase->id,
                description: "Purchase Invoice #{$purchase->invoice_no}"
            );
        }
    }

    /**
     * Recalculate running balances for all ledger transactions of a given vendor
     * 
     * @param int $vendorId
     * @return void
     */
    private function recalculateLedgerBalances(int $vendorId): void
    {
        $entries = VendorLedger::where('vendor_id', $vendorId)
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $runningBalance = 0;
        foreach ($entries as $entry) {
            $entry->opening_balance = $runningBalance;
            $entry->previous_balance = $runningBalance;
            
            $runningBalance += (float)($entry->credit_amount ?? 0) - (float)($entry->debit_amount ?? 0);
            
            $entry->closing_balance = $runningBalance;
            $entry->running_balance = $runningBalance;
            $entry->save();
        }
    }
}
