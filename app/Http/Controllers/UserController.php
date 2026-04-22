<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

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

    // Assign new roles (by name)
    $user->syncRoles($request->roles ?? []);

    // If request is AJAX or expects JSON, return JSON so frontend JS (myAjax)
    // can handle response without a full redirect/HTML page returned.
    if ($request->ajax() || $request->wantsJson()) {
        return response()->json([
            'success' => 'User roles updated successfully!',
            // let frontend decide to reload or not; sending 'reload' true
            // will trigger the existing myAjax handler to reload the page.
            'reload' => true
        ]);
    }

    return back()->with('success', 'User roles updated successfully!');
}
}
