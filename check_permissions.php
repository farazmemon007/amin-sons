<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel');

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Check database connection
try {
    echo "═══════════════════════════════════════════════════════════\n";
    echo "✅ Database Connected\n";
    echo "═══════════════════════════════════════════════════════════\n\n";

    // Check all users
    echo "📋 ALL USERS:\n";
    $users = User::all();
    foreach ($users as $user) {
        echo "ID: {$user->id} | Name: {$user->name} | Email: {$user->email}\n";
    }

    echo "\n═══════════════════════════════════════════════════════════\n";
    echo "🔍 PERMISSIONS CHECK (First User):\n";
    echo "═══════════════════════════════════════════════════════════\n\n";

    if ($users->count() > 0) {
        $user = $users->first();
        echo "User: {$user->name}\n";
        echo "Roles: " . ($user->roles->count() > 0 ? $user->roles->pluck('name')->join(', ') : "❌ NO ROLES") . "\n";
        echo "Permissions: " . ($user->permissions->count() > 0 ? $user->permissions->pluck('name')->join(', ') : "❌ NO PERMISSIONS") . "\n";

        echo "\n✅ Total Permissions in System: " . Permission::count() . "\n";
        echo "✅ Total Roles in System: " . Role::count() . "\n";
    }

    // Check super admin role
    echo "\n═══════════════════════════════════════════════════════════\n";
    echo "👑 SUPER ADMIN ROLE:\n";
    echo "═══════════════════════════════════════════════════════════\n\n";

    $superAdmin = Role::where('name', 'super admin')->first();
    if ($superAdmin) {
        echo "✅ Super Admin Role exists\n";
        echo "✅ Super Admin has " . $superAdmin->permissions->count() . " permissions\n";

        // Show users with super admin role
        $superAdminUsers = User::role('super admin')->get();
        if ($superAdminUsers->count() > 0) {
            echo "\n👥 Users with SUPER ADMIN role:\n";
            foreach ($superAdminUsers as $u) {
                echo "  - {$u->name} ({$u->email})\n";
            }
        } else {
            echo "❌ NO USERS with super admin role!\n";
        }
    } else {
        echo "❌ Super Admin role NOT found\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
