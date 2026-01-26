<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run other seeders (including permissions)
        $this->call([
            \Database\Seeders\PermissionSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,

            WarehouseSeeder::class,
        ]);

        // Create or get users
        $branchUser = User::firstOrCreate(
            ['email' => 'soban@soban.com'],
            [
                'name' => 'soban',
                'password' => Hash::make('soban'),
            ]
        );

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'admin',
                'password' => Hash::make('admin'),
            ]
        );
        $SuperAdmin = User::firstOrCreate(
            ['email' => 'f@gmail.com'],
            [
                'name' => 'faraz memon',
                'password' => Hash::make('123'),
            ]
        );

        // Create or get super admin role
        $superAdminRole = Role::firstOrCreate(['name' => 'super admin']);

        // Assign super admin role to faraz memon user
        if ($SuperAdmin) {
            $SuperAdmin->assignRole($superAdminRole);
        }
    }
}
