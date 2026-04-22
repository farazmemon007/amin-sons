<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WarehouseStock;
use App\Models\Warehouse;
use App\Models\Branch;
use App\Http\Controllers\Concerns\BranchScope;

class WarehouseController extends Controller
{
    use BranchScope;

     // Return warehouses for a given product_id
public function getWarehouses(Request $request)
{
    $productId = $request->input('product_id');

    $warehouseStocks = WarehouseStock::with('stockWarehouse')
    ->where('product_id', $productId)
    ->get();
// $warehouseStocks = WarehouseStock::where('product_id', $productId)
//     ->get();
 
 $response = $warehouseStocks->map(function ($ws) {
    return [
        'warehouse_id'   => $ws->warehouse_id,
        'warehouse_name' => optional($ws->stockWarehouse)->warehouse_name,
        'stock'          => $ws->quantity,
    ];
});
//    echo "<pre>";
//     print_r($response);
//     echo "</pre>";
// dd();

    return response()->json($response);
}




    // VendorController.php aur WarehouseController.php same hoga
public function index() {
    if (auth()->check() && auth()->user()->hasRole('super admin')) {
        $warehouses = Warehouse::with('user','branches')->get();
        $branches = Branch::all();
        } else {
        $allowedBranchIds = $this->allowedBranches('warehouse.view');
        $warehouses = Warehouse::with('user','branches')->whereHas('branches', function ($q) use ($allowedBranchIds) {
            $q->whereIn('branches.id', $allowedBranchIds);
        })->get();
        $branches = Branch::whereIn('id', $allowedBranchIds)->get();
    }
    return view('admin_panel.warehouses.index', compact('warehouses','branches'));
}

public function store(Request $request) {
    $data = $request->only(['warehouse_name','location','remarks','creater_id']);

    if ($request->id) {
        $warehouse = Warehouse::findOrFail($request->id);
        $warehouse->update($data);
    } else {
        $warehouse = Warehouse::create($data);
    }

    // determine branch mapping
    $branchIds = $request->input('branch_id');
    if (empty($branchIds)) {
        if (auth()->check() && !auth()->user()->hasRole('super admin')) {
            $branchIds = [auth()->user()->branch_id];
        } else {
            $branchIds = [];
        }
    } elseif (!is_array($branchIds)) {
        $branchIds = [$branchIds];
    }

    if (!empty($branchIds)) {
        $warehouse->branches()->sync($branchIds);
    }

    return back()->with('success', 'Saved Successfully');
}

public function delete($id) {
    Warehouse::findOrFail($id)->delete();
    return back()->with('success', 'Deleted Successfully');
}

}
