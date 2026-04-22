<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WarehouseOrder;
use App\Models\WarehouseOrderItem;
use Illuminate\Support\Facades\DB;

class WarehouseOrderController extends Controller
{
    public function index()
    {
        $orders = WarehouseOrder::with('customer', 'itemsRelation')->orderByDesc('id')->paginate(25);
        return view('admin_panel.warehouses.warehouse_orders.index', compact('orders'));
    }

    public function edit($id)
    {
        $order = WarehouseOrder::with('itemsRelation')->findOrFail($id);

        // If normalized items are empty but JSON items exist, import them on first edit
        if ($order->itemsRelation->count() === 0 && !empty($order->items) && is_array($order->items)) {
            foreach ($order->items as $it) {
                WarehouseOrderItem::create([
                    'warehouse_order_id' => $order->id,
                    'sale_item_id' => $it['sale_item_id'] ?? null,
                    'product_id' => $it['product_id'] ?? null,
                    'product_name' => $it['product_name'] ?? ($it['name'] ?? null),
                    'item_code' => $it['item_code'] ?? null,
                    'qty' => $it['qty'] ?? 0,
                    'retail_price' => $it['retail_price'] ?? null,
                    'amount' => $it['amount'] ?? null,
                    'warehouse_id' => $it['warehouse_id'] ?? null,
                ]);
            }
            // reload
            $order->load('itemsRelation');
        }

        return view('admin_panel.warehouses.warehouse_orders.edit', compact('order'));
    }

    public function update(Request $request, $id)
    {
        $order = WarehouseOrder::findOrFail($id);

        $data = $request->validate([
            'status' => 'nullable|string',
            'remarks' => 'nullable|string',
            'product_id' => 'array',
            'product_id.*' => 'nullable|integer',
            'product_name' => 'array',
            'item_code' => 'array',
            'qty' => 'array',
            'retail_price' => 'array',
            'amount' => 'array',
        ]);

        $order->status = $data['status'] ?? $order->status;
        $order->remarks = $data['remarks'] ?? $order->remarks;
        $order->updated_by = auth()->id();
        $order->save();

        // Replace items
        DB::transaction(function() use ($order, $data) {
            $order->itemsRelation()->delete();

            $productIds = $data['product_id'] ?? [];
            foreach ($productIds as $i => $pid) {
                if (empty($pid) && empty($data['product_name'][$i] ?? null)) continue;

                WarehouseOrderItem::create([
                    'warehouse_order_id' => $order->id,
                    'product_id' => $pid ?: null,
                    'product_name' => $data['product_name'][$i] ?? null,
                    'item_code' => $data['item_code'][$i] ?? null,
                    'qty' => (float) ($data['qty'][$i] ?? 0),
                    'retail_price' => $data['retail_price'][$i] ?? null,
                    'amount' => $data['amount'][$i] ?? null,
                    'warehouse_id' => $order->warehouse_id,
                ]);
            }

            // keep JSON in sync
            $order->items = $order->itemsRelation()->get()->map(function($r){
                return [
                    'sale_item_id' => $r->sale_item_id,
                    'product_id' => $r->product_id,
                    'product_name' => $r->product_name,
                    'item_code' => $r->item_code,
                    'qty' => (float) $r->qty,
                    'retail_price' => (float) $r->retail_price,
                    'amount' => (float) $r->amount,
                    'warehouse_id' => $r->warehouse_id,
                ];
            })->toArray();
            $order->save();
        });

        return redirect()->route('admin.warehouse_orders.index')->with('success','Warehouse order updated');
    }
}
