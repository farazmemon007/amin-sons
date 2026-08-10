<?php
namespace App\Http\Controllers\Concerns;

use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

/**
 * BranchScope Trait
 * ─────────────────
 * Centralises the "which branches can this user see?" logic used by all
 * controllers that query branch-filtered data.
 *
 * Permission naming convention (v2):
 *   Standard permission : module.action
 *     → applies to the user's OWN branch (controlled by users.branch_id)
 *
 *   Cross-branch permission : branch:{branch_id}:module.action
 *     → grants access to a SPECIFIC other branch's data
 *     → must be explicitly assigned to a user/role; never auto-granted
 *
 * Usage:
 *   $allowed = $this->allowedBranches('product.view');
 *   // Returns: [own_branch_id, ...any branch IDs the user has cross-branch permission for]
 */
trait BranchScope
{
    /**
     * Return an array of branch IDs that the authenticated user is allowed
     * to view, for a given permission.
     *
     * @param  string  $permission  e.g. 'product.view', 'warehouse.stock.view'
     * @return int[]
     */
    protected function allowedBranches(string $permission): array
    {
        if (!Auth::check()) return [];

        $user = Auth::user();

        // Super admin sees everything
        if ($user->hasRole('super admin')) {
            return Branch::pluck('id')->map(fn($id) => (int) $id)->toArray();
        }

        $allowed = [];

        // Always include the user's own home branch
        if ($user->branch_id) {
            $allowed[] = (int) $user->branch_id;
        }

        // Check for explicit cross-branch permissions: branch:{id}:{permission}
        Branch::pluck('id')->each(function ($bid) use ($user, $permission, &$allowed) {
            $bid = (int) $bid;
            if ($bid === (int) $user->branch_id) return; // own branch already added

            if ($user->can("branch:{$bid}:{$permission}")) {
                $allowed[] = $bid;
            }
        });

        return array_unique($allowed);
    }
}
