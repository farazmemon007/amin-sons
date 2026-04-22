<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BranchWarehouseController extends Controller
{
    // show management page
    public function index()
    {
        if (!Auth::check() || !Auth::user()->can('warehouse.manage')) {
            abort(403);
        }

        $branches = Branch::with('warehouses')->get();
        $warehouses = Warehouse::all();
        return view('admin_panel.branch_warehouse.index', compact('branches','warehouses'));
    }

    /**
     * ✅ Check products in warehouse for a specific branch
     * Returns products with stock quantity for that warehouse-branch combination
     */
    public function getWarehouseProducts($branchId, $warehouseId)
    {
        if (!Auth::check() || !Auth::user()->can('warehouse.manage')) {
            abort(403);
        }

        // Get all products in this warehouse for this branch
        $products = WarehouseStock::where('branch_id', $branchId)
            ->where('warehouse_id', $warehouseId)
            ->where('quantity', '>', 0)
            ->with(['product', 'warehouse'])
            ->get()
            ->map(function ($stock) {
                return [
                    'product_name' => optional($stock->product)->item_name ?? 'Unknown',
                    'product_code' => optional($stock->product)->item_code ?? 'N/A',
                    'quantity' => $stock->quantity,
                    'warehouse_name' => optional($stock->warehouse)->warehouse_name ?? 'Unknown',
                ];
            })
            ->values()
            ->toArray();

        return response()->json([
            'status' => 'success',
            'warehouse_name' => Warehouse::find($warehouseId)->warehouse_name ?? 'Unknown',
            'branch_name' => Branch::find($branchId)->name ?? 'Unknown',
            'products' => $products,
            'has_products' => count($products) > 0,
            'total_qty' => collect($products)->sum('quantity'),
        ]);
    }

    // update mapping for a branch
    public function update(Request $request, $branchId)
    {
        if (!Auth::check() || !Auth::user()->can('warehouse.manage')) {
            abort(403);
        }

        $branch = Branch::findOrFail($branchId);
        $data = $request->validate([
            'warehouse_ids' => 'nullable|array',
            'warehouse_ids.*' => 'integer|exists:warehouses,id',
        ]);

        $warehouseIds = $data['warehouse_ids'] ?? [];

        // log for debugging
        Log::info('BranchWarehouseController.update called', ['branch' => $branchId, 'payload' => $request->all(), 'validated' => $warehouseIds]);
        // sync and provide debug info in session for quick verification
        $branch->warehouses()->sync($warehouseIds);

        return redirect()->back()->with([
            'success' => 'Mappings updated.',
            'debug_selected' => implode(',', $warehouseIds),
        ]);
    }
}
