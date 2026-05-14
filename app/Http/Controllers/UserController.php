<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Branch;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
        public function index()
    {
        // dd("ok");
            $allowedBranchIds = [];
            if (Auth::check()) {
                $u = Auth::user();
                if ($u->hasRole('super admin')) {
                    $allowedBranchIds = Branch::pluck('id')->toArray();
                } else {
                    $allowedBranchIds[] = $u->branch_id;
                    $branchIds = Branch::pluck('id');
                    foreach ($branchIds as $bid) {
                        if ($u->can('branch.view.' . $bid)) {
                            $allowedBranchIds[] = $bid;
                        }
                    }
                    $allowedBranchIds = array_unique($allowedBranchIds);
                }
            }

            if (!empty($allowedBranchIds)) {
                $users = User::whereIn('branch_id', $allowedBranchIds)->where('email', '!=', 'admin@admin.com')->get();
            } else {
                // no branches allowed => empty collection
                $users = collect();
            }

            $allRoles = Role::all();

            return view('admin_panel.users.users', compact(['users', 'allRoles', 'allowedBranchIds']));
    }

    public function store(Request $request)
    {
        // dd("sda");
        $editId = $request->edit_id ?? null;
         $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|unique:users,email,'.$request->edit_id,
            'password' => 'required',
            'branch_id' => 'required',
        ]);

        if ($validator->fails()) {
            return ['errors' => $validator->errors()];
        }

      
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        // Step 3: Save or update logic
        if (!empty($editId)) {
            $user = User::find($editId);
            $msg = [
                'success' => 'User Updated Successfully',
                'reload' => true
            ];
        } else {
            $user = new User();
            $msg = [
                'success' => 'User Created Successfully',
                'redirect' => route('users.index')
            ];
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->branch_id = $request->branch_id;
        $user->save();

        // Assign roles if provided
        if ($request->has('roles') && is_array($request->roles)) {
            $user->syncRoles($request->roles);
        }

        return response()->json($msg);
        
    }

    /**
     * Display the specified resource.
     */
  
    /**
     * Remove the specified resource from storage.
     */
    public function delete(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');

    }

    public function updateRoles(Request $request)
    {
        $user = User::findOrFail($request->edit_id);
        $user->syncRoles($request->roles ?? []);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'User roles updated successfully!', 'reload' => true]);
        }
        return back()->with('success', 'User roles updated successfully!');
    }

    /**
     * ERP: Get all branches & warehouses for the "Assign Warehouses" modal.
     * Returns:
     *   - branches[] with their warehouses[]
     *   - assigned_entries[]  e.g. ["1_5", "2_8"]  (branchId_warehouseId)
     *   - incharge_entries[]  same format, for incharge status
     */
    public function getUserWarehouseAssignments(int $userId)
    {
        $user = User::with('warehouses')->findOrFail($userId);

        // Super Admin sees ALL branches; Branch Admin sees only their branch
        $currentUser = Auth::user();
        $branchQuery = Branch::with('warehouses');
        if (!$currentUser->hasRole('super admin')) {
            $branchQuery->where('id', $currentUser->branch_id);
        }
        $branches = $branchQuery->get()->map(function ($branch) {
            return [
                'id'         => (int) $branch->id,
                'name'       => $branch->branch_name ?? $branch->name ?? 'Branch ' . $branch->id,
                'warehouses' => $branch->warehouses->map(fn($w) => [
                    'id'   => (int) $w->id,
                    'name' => $w->warehouse_name,
                ])->values(),
            ];
        });

        // Build "branchId_warehouseId" entry strings for precise pre-selection
        $assignedEntries = [];
        $inchargeEntries = [];
        foreach ($user->warehouses as $w) {
            $pivotBranchId = (int) $w->pivot->branch_id;
            $entry = $pivotBranchId . '_' . $w->id;
            $assignedEntries[] = $entry;
            if ($w->pivot->is_incharge) {
                $inchargeEntries[] = $entry;
            }
        }

        return response()->json([
            'user_id'          => (int) $user->id,
            'user_name'        => $user->name,
            'branches'         => $branches,
            'assigned_entries' => $assignedEntries,   // ["1_5", "2_8"]
            'incharge_entries' => $inchargeEntries,   // ["1_5"]
        ]);
    }

    /**
     * ERP: Save user <-> warehouse assignments (many-to-many with is_incharge + branch_id).
     * Receives entries as "branchId_warehouseId" strings so the same warehouse in
     * different branches can be tracked independently.
     * Also auto-syncs Spatie branch-level permissions.
     */
    public function assignUserWarehouses(Request $request)
    {
        $request->validate([
            'user_id'          => 'required|exists:users,id',
            'entries'          => 'nullable|array',
            'incharge_entries' => 'nullable|array',
        ]);

        $user = User::findOrFail($request->user_id);

        $entries         = $request->entries         ?? [];
        $inchargeEntries = $request->incharge_entries ?? [];

        // ── 1. Build sync data from "branchId_warehouseId" pairs ──────────
        $syncData        = [];
        $branchIdsForAssigned = [];

        foreach ($entries as $entry) {
            // entry format: "1_5" => branch_id=1, warehouse_id=5
            $parts = explode('_', $entry, 2);
            if (count($parts) !== 2) continue;

            $branchId   = (int) $parts[0];
            $warehouseId = (int) $parts[1];

            $syncData[$warehouseId] = [
                'is_incharge' => in_array($entry, $inchargeEntries),
                'branch_id'   => $branchId,
                'notes'       => null,
            ];

            $branchIdsForAssigned[] = $branchId;
        }

        $user->warehouses()->sync($syncData);

        // ── 2. Auto-sync Spatie branch-level permissions ──────────────────
        // Grant  warehouse.stock.view.{branch_id} for branches with assigned warehouses.
        // Revoke warehouse.stock.view.{branch_id} for branches with no assigned warehouses
        // (never revoke the user's own home branch).

        $branchIdsForAssigned = array_unique($branchIdsForAssigned);
        $allBranchIds = Branch::pluck('id')->map(fn($id) => (int) $id)->toArray();

        foreach ($allBranchIds as $branchId) {
            $permName = 'warehouse.stock.view.' . $branchId;

            if (in_array($branchId, $branchIdsForAssigned)) {
                $perm = Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
                if (!$user->hasDirectPermission($permName)) {
                    $user->givePermissionTo($perm);
                }
            } else {
                // Never revoke the user's own home branch
                if ($branchId === (int) $user->branch_id) continue;

                $perm = Permission::where('name', $permName)->where('guard_name', 'web')->first();
                if ($perm && $user->hasDirectPermission($permName)) {
                    $user->revokePermissionTo($perm);
                }
            }
        }

        // Clear Spatie's permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'success' => "Warehouse assignments updated for {$user->name}.",
            'reload'  => true,
        ]);
    }
}
