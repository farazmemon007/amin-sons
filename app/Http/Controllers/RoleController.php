<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
     public function index()
    {
        $roles = Role::with(['users.branch'])->get();

        // Only show standard (non-cross-branch) permissions — exclude branch:{id}:* dynamic ones
        $allPermissions = Permission::where('name', 'not like', 'branch:%:%')
            ->orderBy('name')
            ->get();

        // Pass config-based module definitions to the view for clean grouping
        $permissionModules = config('permissions', []);

        // Pass all branches for cross-branch assignment
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        
        // Filter cross-branch allowed modules
        $crossBranchModules = collect($permissionModules)
            ->filter(fn($m) => !empty($m['cross_branch']))
            ->toArray();

        return view('admin_panel.roles.role', compact([
            'roles', 
            'allPermissions', 
            'permissionModules', 
            'branches', 
            'crossBranchModules'
        ]));
    }


    public function store(Request $request)
    {
        $editId = $request->edit_id ?? null;
         $validator = Validator::make($request->all(), [
            'name' => 'required|unique:roles,name,'.$request->edit_id,
        ]);

        if ($validator->fails()) {
            return ['errors' => $validator->errors()];
        }

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        // Save or update logic
        if (!empty($editId)) {
            $role = Role::find($editId);
            $msg = [
                'success' => 'Role Updated Successfully',
                'reload' => true
            ];
        } else {
            $role = new Role();
            $msg = [
                'success' => 'Role Created Successfully',
                'redirect' => route('roles.index')
            ];
        }

        $role->name = $request->name;
        $role->save();

        return response()->json($msg);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(string $id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }

    public function updatePermissions(Request $request)
    {
        $role = Role::findOrFail($request->edit_id);
        $permissions = $request->permissions ?? [];

        // Ensure all submitted permissions (including dynamic branch:{id}:{perm}) exist in DB
        foreach ($permissions as $permName) {
            Permission::firstOrCreate([
                'name' => $permName,
                'guard_name' => 'web'
            ]);
        }

        // Sync permissions
        $role->syncPermissions($permissions);

        // Clear Spatie permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Role permissions updated successfully!']);
        }

        return back()->with('success', 'Role permissions updated successfully!');
    }
}
