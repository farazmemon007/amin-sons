<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WarehouseStock;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\StockOnhand;
use Illuminate\Support\Facades\DB;

class WarehouseStockController extends Controller
{

///////////
public function getByWarehouse($warehouseId)
{
    $products = WarehouseStock::with('product')
        ->where('warehouse_id', $warehouseId)
        ->get()
        ->map(function ($row) {
            return [
                'id'   => $row->product->id,
                'name' => $row->product->item_name,
                'qty'  => $row->quantity,
            ];
        });

    return response()->json($products);
}

//////////////////




    public function index() {
        $stocks = WarehouseStock::with('warehouse', 'product')->get();
        return view('admin_panel.warehouses.warehouse_stocks.index', compact('stocks'));
    }

    public function create() {
        $warehouses = Warehouse::all();
        $products = Product::all();
        // Total on-hand quantities per product (from view) if available
        $onhand = StockOnhand::pluck('onhand_qty', 'product_id')->toArray();

        // Already allocated quantities across warehouses
        $allocated = WarehouseStock::groupBy('product_id')
            ->selectRaw('product_id, SUM(quantity) as total_alloc')
            ->pluck('total_alloc', 'product_id')
            ->toArray();

        $remainingByProduct = [];
        foreach ($products as $p) {
            $total = $onhand[$p->id] ?? ($p->stock->qty ?? 0);
            $used = $allocated[$p->id] ?? 0;
            $remainingByProduct[$p->id] = max(0, $total - $used);
        }

        return view('admin_panel.warehouses.warehouse_stocks.create', compact('warehouses', 'products', 'remainingByProduct'));
    }

    public function store(Request $request) {
        $request->validate([
            'warehouse_id' => 'required',
            'product_id' => 'required',
            'quantity' => 'required|integer|min:0'
        ]);

        WarehouseStock::create($request->all());
        return redirect()->route('warehouse_stocks.index')->with('success', 'Stock added successfully.');
    }

    public function edit(WarehouseStock $warehouseStock) {
        $warehouses = Warehouse::all();
        $products = Product::all();
        return view('admin_panel.warehouses.warehouse_stocks.edit', compact('warehouseStock', 'warehouses', 'products'));
    }

    public function update(Request $request, WarehouseStock $warehouseStock) {
        $request->validate([
            'warehouse_id' => 'required',
            'product_id' => 'required',
            'quantity' => 'required|integer|min:0'
        ]);

        $warehouseStock->update($request->all());
        return redirect()->route('warehouse_stocks.index')->with('success', 'Stock updated successfully.');
    }

    public function destroy(WarehouseStock $warehouseStock) {
        $warehouseStock->delete();
        return redirect()->route('warehouse_stocks.index')->with('success', 'Stock deleted successfully.');
    }
}

