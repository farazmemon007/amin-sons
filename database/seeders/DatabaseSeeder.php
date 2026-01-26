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

        // Define permissions
        $permissions = [
            'create product',
            'edit product',
            'delete product',
            'view product',

            // dotted-style permissions (used in views / @can checks)
            'product.view',
            'product.create',
            'product.edit',
            'product.delete',
            'product.barcode',
            'product.assembly',
            'product.discount.view',
            'product.discount.create',
            'product.discount.barcode',

            // Purchase dotted permissions
            'purchase.view',
            'purchase.create',
            'purchase.edit',
            'purchase.delete',
            'purchase.invoice',
            'purchase.return.view',
            'purchase.return.create',
            'purchase.return',

            'create role',
            'update role',

            'view dashboard',
            'view discount',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles if they don't exist
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $branchRole = Role::firstOrCreate(['name' => 'branch']);

        // Assign permissions to roles
        $adminRole->syncPermissions($permissions);
        $branchRole->syncPermissions($permissions);

        // Assign roles to users
        if ($adminUser) {
            $adminUser->assignRole($adminRole);
        }
        if ($branchUser) {
            $branchUser->assignRole($branchRole);
        }
    }
}
