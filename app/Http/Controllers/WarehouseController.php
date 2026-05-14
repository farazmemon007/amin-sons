<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WarehouseStock;
use App\Models\Warehouse;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Concerns\BranchScope;
use App\Http\Controllers\Concerns\WarehouseScope;

class WarehouseController extends Controller
{
    use BranchScope, WarehouseScope;

    // Return warehouses for a given product_id
    public function getWarehouses(Request $request)
    {
        $productId = $request->input('product_id');

        $warehouseStocks = WarehouseStock::with('stockWarehouse')
            ->where('product_id', $productId)
            ->get();

        $response = $warehouseStocks->map(function ($ws) {
            return [
                'warehouse_id'   => $ws->warehouse_id,
                'warehouse_name' => optional($ws->stockWarehouse)->warehouse_name,
                'stock'          => $ws->quantity,
            ];
        });

        return response()->json($response);
    }

    // Return warehouses for a given branch
    public function warehousesByBranch(Request $request)
    {
        $branchId = $request->input('branch_id');
        
        if (!$branchId) {
            return response()->json([]);
        }

        $warehouses = Warehouse::whereHas('branches', function($q) use ($branchId) {
            $q->where('branches.id', $branchId);
        })->orderBy('warehouse_name')->get(['warehouses.id', 'warehouse_name']);

        return response()->json($warehouses);
    }

    public function index()
    {
        if (auth()->check() && auth()->user()->hasRole('super admin')) {
            $warehouses = Warehouse::with(['user', 'branches', 'assignedUsers'])->get();
            $branches   = Branch::all();
        } else {
            $allowedBranchIds = $this->allowedBranches('warehouse.view');
            $warehouses = Warehouse::with(['user', 'branches', 'assignedUsers'])
                ->whereHas('branches', function ($q) use ($allowedBranchIds) {
                    $q->whereIn('branches.id', $allowedBranchIds);
                })->get();
            $branches = Branch::whereIn('id', $allowedBranchIds)->get();
        }

        // Load users for the assign staff modal
        $users = User::where('email', '!=', 'admin@admin.com')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'branch_id']);

        return view('admin_panel.warehouses.index', compact('warehouses', 'branches', 'users'));
    }

    public function store(Request $request)
    {
        $data = $request->only(['warehouse_name', 'location', 'remarks', 'creater_id']);

        if ($request->id) {
            $warehouse = Warehouse::findOrFail($request->id);
            $warehouse->update($data);
        } else {
            $warehouse = Warehouse::create($data);
        }

        // Determine branch mapping
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

    /**
     * ERP: Assign users (staff/incharge) to a warehouse.
     * Super Admin can assign users across branches.
     */
    public function assignUsers(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'user_ids'     => 'nullable|array',
            'user_ids.*'   => 'exists:users,id',
            'incharge_id'  => 'nullable|exists:users,id',
        ]);

        $warehouse = Warehouse::findOrFail($request->warehouse_id);

        // Build sync data: pivot includes is_incharge flag
        $syncData = [];
        foreach (($request->user_ids ?? []) as $userId) {
            $syncData[$userId] = [
                'is_incharge' => ($request->incharge_id == $userId),
                'notes'       => null,
            ];
        }

        // If incharge_id is provided but not in user_ids, add them
        if ($request->incharge_id && !isset($syncData[$request->incharge_id])) {
            $syncData[$request->incharge_id] = ['is_incharge' => true, 'notes' => null];
        }

        $warehouse->assignedUsers()->sync($syncData);

        return response()->json([
            'success' => 'Warehouse staff assignment updated successfully.',
            'reload'  => true,
        ]);
    }

    /**
     * Return users assigned to a warehouse (for modal AJAX load).
     */
    public function getWarehouseUsers(int $warehouseId)
    {
        $warehouse = Warehouse::with(['assignedUsers', 'branches'])->findOrFail($warehouseId);

        $assignedUserIds   = $warehouse->assignedUsers->pluck('id')->toArray();
        $inchargeUserId    = $warehouse->assignedUsers
                                ->where('pivot.is_incharge', true)
                                ->first()?->id;

        // For Super Admin: show all users; for Branch Admin: show branch users
        $user = Auth::user();
        if ($user->hasRole('super admin')) {
            $users = User::where('email', '!=', 'admin@admin.com')
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'branch_id']);
        } else {
            $users = User::where('branch_id', $user->branch_id)
                ->where('email', '!=', 'admin@admin.com')
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'branch_id']);
        }

        return response()->json([
            'warehouse_id'    => $warehouse->id,
            'warehouse_name'  => $warehouse->warehouse_name,
            'users'           => $users,
            'assigned_ids'    => $assignedUserIds,
            'incharge_id'     => $inchargeUserId,
        ]);
    }

    public function delete($id)
    {
        Warehouse::findOrFail($id)->delete();
        return back()->with('success', 'Deleted Successfully');
    }
}
