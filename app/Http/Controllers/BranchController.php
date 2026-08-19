<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;


class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // If the current user is super admin, show all branches and users.
        // Otherwise, hide any branch that has a user with the 'super admin' role
        // (so non-super-admins cannot see the super-admin branch).
        if (Auth::check() && Auth::user()->hasRole('super admin')) {
            $branches = Branch::with('userhasmany')->get();
            $users = User::where('email', '!=', 'f@gamil.com')->get();
        } else {
            $branches = Branch::with('userhasmany')
                ->whereDoesntHave('userhasmany', function ($q) {
                    $q->whereHas('roles', function ($q2) {
                        $q2->where('name', 'super admin');
                    });
                })->get();

            $users = User::where('email', '!=', 'f@gamil.com')
                ->whereDoesntHave('roles', function ($q) {
                    $q->where('name', 'super admin');
                })->get();
        }

        return view('admin_panel.branch.branch', compact('branches', 'users'));
    }

   // STORE (Create) - Super Admin Only
    public function store(Request $request)
    {
        if (!Auth::check() || !Auth::user()->hasRole('super admin')) {
            return response()->json([
                'error' => 'Unauthorized. Only Super Admin has permission to create branches.'
            ], 403);
        }

        $request->validate([
            'name'    => 'required|unique:branches,name',
            'address' => 'required',
            'number'  => 'required',
            'status'  => 'nullable|in:active,inactive',
        ]);

        Branch::create(array_merge($request->only('name','address','number'), [
            'status' => $request->status ?? 'active'
        ]));

        return response()->json([
            'success'  => 'Branch Created Successfully',
            'reload'   => true
        ]);
    }

    // UPDATE - Super Admin Only
    public function update(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->hasRole('super admin')) {
            return response()->json([
                'error' => 'Unauthorized. Only Super Admin has permission to edit branches.'
            ], 403);
        }

        $branch = Branch::findOrFail($id);

        $request->validate([
            'name'    => 'required|unique:branches,name,' . $branch->id,
            'address' => 'required',
            'number'  => 'required',
            'status'  => 'nullable|in:active,inactive',
        ]);

        $branch->update(array_merge($request->only('name','address','number'), [
            'status' => $request->status ?? 'active'
        ]));

        return response()->json([
            'success' => 'Branch Updated Successfully',
            'reload'  => true
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(string $id)
    {
        if (!Auth::check() || !Auth::user()->hasRole('super admin')) {
            return redirect()->back()->with('error', 'Unauthorized. Only Super Admin has permission to delete branches.');
        }

        $branch = Branch::findOrFail($id);
        $branch->delete();

        return redirect()->route('branch.index')->with('success', 'Branch deleted successfully.');
    }
}
