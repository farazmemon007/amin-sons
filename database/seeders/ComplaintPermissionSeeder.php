<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ComplaintPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'complaint.view',
            'complaint.create',
            'complaint.edit',
            'complaint.delete',
            'complaint.print',
            'complaint.home_service',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Assign all complaint permissions to super admin
        $superAdmin = Role::where('name', 'super admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
            $this->command->info('✅ Complaint permissions assigned to super admin!');
        }

        // Also assign to 'admin' role if it exists
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo($permissions);
            $this->command->info('✅ Complaint permissions assigned to admin!');
        }

        $this->command->info('✅ All ' . count($permissions) . ' complaint permissions created successfully!');
    }
}
