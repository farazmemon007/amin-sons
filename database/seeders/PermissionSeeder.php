<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Seed all standard permissions from config/permissions.php.
     *
     * Safe to run multiple times — uses firstOrCreate.
     * Ensures 'super admin' role has ALL permissions.
     */
    public function run(): void
    {
        // Reset cached roles/permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $modules = config('permissions');

        $allPermissionNames = [];

        foreach ($modules as $moduleKey => $module) {
            foreach ($module['permissions'] as $permName => $label) {
                Permission::firstOrCreate([
                    'name'       => $permName,
                    'guard_name' => 'web',
                ]);
                $allPermissionNames[] = $permName;
            }
        }

        // Ensure super admin role exists and has ALL permissions
        $superAdmin = Role::firstOrCreate([
            'name'       => 'super admin',
            'guard_name' => 'web',
        ]);

        $superAdmin->syncPermissions($allPermissionNames);

        $this->command->info('✅ PermissionSeeder: ' . count($allPermissionNames) . ' standard permissions seeded.');
        $this->command->info('✅ super admin role synced with all permissions.');
    }
}
