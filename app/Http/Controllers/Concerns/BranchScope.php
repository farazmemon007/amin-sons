<?php
namespace App\Http\Controllers\Concerns;

use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

trait BranchScope
{
    /**
     * Return array of branch ids that the current user is allowed to view
     * based on a permission prefix. Super admin gets all branches.
     */
    protected function allowedBranches(string $permissionPrefix): array
    {
        $allowed = [];
        if (!Auth::check()) return $allowed;

        $user = Auth::user();
        if ($user->hasRole('super admin')) {
            return Branch::pluck('id')->toArray();
        }

        // always include own branch if present
        if ($user->branch_id) $allowed[] = $user->branch_id;

        $branchIds = Branch::pluck('id');
        foreach ($branchIds as $bid) {
            if ($user->can("{$permissionPrefix}.{$bid}")) {
                $allowed[] = $bid;
            }
        }
        return array_unique($allowed);
    }
}
