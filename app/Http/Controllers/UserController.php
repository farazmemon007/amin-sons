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
                    // Check cross-branch permissions: branch:{id}:user.view
                    $branchIds = Branch::pluck('id');
                    foreach ($branchIds as $bid) {
                        if ($u->can('branch:' . $bid . ':user.view')) {
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
        $editId = $request->edit_id ?? null;

        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $editId,
            'branch_id' => 'required',
        ];

        if (empty($editId)) {
            $rules['password'] = 'required|min:6';
        } else {
            $rules['password'] = 'nullable|min:6';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        // Save or update logic
        if (!empty($editId)) {
            $user = User::findOrFail($editId);
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
        if (!empty($request->password)) {
            $user->password = Hash::make($request->password);
        }
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
            // New format: branch:{id}:warehouse.stock.view
            $permName = "branch:{$branchId}:warehouse.stock.view";

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

    /**
     * ERP: Get cross-branch permission state for a user (super admin only).
     * Returns all branches (excluding user's own) with per-module permission state.
     */
    public function getCrossbranchPerms(int $userId)
    {
        abort_unless(Auth::user()->hasRole('super admin'), 403, 'Super Admin only.');

        $user = User::findOrFail($userId);

        // Load config modules
        $modules = config('permissions', []);

        // All branches except user's own
        $branches = Branch::where('id', '!=', $user->branch_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Build state: for each branch, for each module, which perms does user have?
        $branchData = $branches->map(function ($branch) use ($user, $modules) {
            $moduleData = [];
            foreach ($modules as $moduleKey => $module) {
                if (empty($module['cross_branch'])) continue; // only cross_branch modules

                $perms = [];
                foreach ($module['permissions'] as $permName => $label) {
                    $crossPermName = "branch:{$branch->id}:{$permName}";
                    $perms[] = [
                        'key'     => $permName,
                        'label'   => $label,
                        'checked' => $user->hasDirectPermission($crossPermName),
                    ];
                }

                $moduleData[] = [
                    'key'   => $moduleKey,
                    'label' => $module['label'],
                    'icon'  => $module['icon'],
                    'perms' => $perms,
                ];
            }

            return [
                'id'      => $branch->id,
                'name'    => $branch->name,
                'modules' => $moduleData,
            ];
        });

        return response()->json([
            'user_id'   => $user->id,
            'user_name' => $user->name,
            'own_branch_id' => $user->branch_id,
            'branches'  => $branchData,
        ]);
    }

    /**
     * ERP: Save cross-branch permissions for a user (super admin only).
     * Expects: { user_id, granted: ["branch:{id}:permission.name", ...] }
     */
    public function saveCrossbranchPerms(Request $request)
    {
        abort_unless(Auth::user()->hasRole('super admin'), 403, 'Super Admin only.');

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'granted' => 'nullable|array',
        ]);

        $user    = User::findOrFail($request->user_id);
        $modules = config('permissions', []);
        $granted = $request->granted ?? [];

        // Collect all cross-branch modules and their permissions
        $crossModulePerms = [];
        foreach ($modules as $moduleKey => $module) {
            if (empty($module['cross_branch'])) continue;
            foreach ($module['permissions'] as $permName => $label) {
                $crossModulePerms[] = $permName;
            }
        }

        // Get all branches except user's own
        $branches = Branch::where('id', '!=', $user->branch_id)->pluck('id');

        // For each branch × cross-perm, grant or revoke
        foreach ($branches as $branchId) {
            foreach ($crossModulePerms as $permName) {
                $crossPermName = "branch:{$branchId}:{$permName}";

                if (in_array($crossPermName, $granted)) {
                    // Grant: ensure permission exists and is given
                    $perm = Permission::firstOrCreate([
                        'name'       => $crossPermName,
                        'guard_name' => 'web',
                    ]);
                    if (!$user->hasDirectPermission($crossPermName)) {
                        $user->givePermissionTo($perm);
                    }
                } else {
                    // Revoke if user has it
                    $perm = Permission::where('name', $crossPermName)->where('guard_name', 'web')->first();
                    if ($perm && $user->hasDirectPermission($crossPermName)) {
                        $user->revokePermissionTo($perm);
                    }
                }
            }
        }

        // Clear Spatie's permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'success' => "Cross-branch permissions updated for {$user->name}.",
            'reload'  => true,
        ]);
    }
}

