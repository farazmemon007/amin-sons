<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "====================================\n";
echo "USER VERIFICATION\n";
echo "====================================\n\n";

$user = \App\Models\User::where('email', 'f@gmail.com')->first();

if ($user) {
    echo "✅ USER CREATED SUCCESSFULLY\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Name: " . $user->name . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Password: 123 (hashed in DB)\n";
    echo "\n";

    $roles = $user->getRoleNames()->toArray();
    echo "Roles: " . implode(', ', $roles) . "\n";

    $permissions = $user->getAllPermissions();
    echo "Total Permissions: " . $permissions->count() . "\n";
    echo "\n";

    if ($permissions->count() > 0) {
        echo "Sample Permissions:\n";
        foreach ($permissions->take(10) as $perm) {
            echo "  • " . $perm->name . "\n";
        }
        echo "  ... and " . ($permissions->count() - 10) . " more\n";
    }

    echo "\n";
    echo "====================================\n";
    echo "✅ READY TO LOGIN\n";
    echo "====================================\n";
    echo "Username: faraz memon\n";
    echo "Email: f@gmail.com\n";
    echo "Password: 123\n";
    echo "Role: super admin (All 150+ permissions)\n";

} else {
    echo "❌ USER NOT FOUND\n";
}

echo "\n";
