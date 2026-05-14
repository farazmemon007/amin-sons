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
            BranchSeeder::class,
            \Database\Seeders\PermissionSeeder::class,
            CategorySeeder::class,
            WarehouseSeeder::class,
            \Database\Seeders\BranchWarehouseSeeder::class,
            // ProductSeeder::class,
            // WarehouseStockSeeder::class,
            // StockSeeder::class,
            // StockMovementSeeder::class,
            // ChartOfAccountSeeder::class,
             ProductTypeSeeder::class,
            CustomerSeeder::class,
            ModuleSeeder::class,
            BrandSeeder::class,
            UnitSeeder::class,
            // CustomerLedgerSeeder::class,
            WarehouseInchargePermissionSeeder::class,
        ]);

        // Create or get users
        $branchUser = User::firstOrCreate(
            ['email' => 'soban@soban.com'],
            [
                'name' => 'soban',
                'password' => Hash::make('soban'),
                'branch_id' => 2, // Assign to branch 1
            ]
        );

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'admin',
                'password' => Hash::make('admin'),
                'branch_id' => null, // ✅ ERP PROPER: Super admin has NO branch - independent from all branches
            ]
        );
        $SuperAdmin = User::firstOrCreate(
            ['email' => 'f@gmail.com'],
            [
                'name' => 'faraz memon',
                'password' => Hash::make('123'),
                'branch_id' => null, // ✅ ERP PROPER: Super admin has NO branch - independent from all branches
            ]
        );

        // Create or get super admin role
        $superAdminRole = Role::firstOrCreate(['name' => 'super admin']);

        // ✅ Create standard roles for regular users
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $staffRole = Role::firstOrCreate(['name' => 'staff']);

        // ✅ Get all permissions and assign to manager/staff roles
        // These roles get: view, create, edit permissions for core modules
        $coreViewPermissions = Permission::where('name', 'like', '%.view')->pluck('name')->toArray();
        $coreCreatePermissions = Permission::where('name', 'like', '%.create')->pluck('name')->toArray();
        $coreEditPermissions = Permission::where('name', 'like', '%.edit')->pluck('name')->toArray();
        
        $managerPermissions = array_merge($coreViewPermissions, $coreCreatePermissions, $coreEditPermissions);
        $staffPermissions = $coreViewPermissions; // Staff can only view

        // Add branch-specific permissions for managers (they manage their branch)
        $branchViewPermissions = Permission::where('name', 'like', 'warehouse-stocks-product.view.%')->pluck('name')->toArray();
        $managerPermissions = array_merge($managerPermissions, $branchViewPermissions);
        
        // Assign warehouse-specific permissions
        $warehousePermissions = Permission::where('name', 'like', 'warehouse%')->pluck('name')->toArray();
        $managerPermissions = array_merge($managerPermissions, $warehousePermissions);

        // De-duplicate and remove any null values
        $managerPermissions = array_filter(array_unique($managerPermissions));
        $staffPermissions = array_filter(array_unique($staffPermissions));

        $managerRole->syncPermissions($managerPermissions);
        $staffRole->syncPermissions($staffPermissions);

        // ✅ Assign super admin role to both admin users
        if ($adminUser) {
            $adminUser->assignRole($superAdminRole);
        }
        if ($SuperAdmin) {
            $SuperAdmin->assignRole($superAdminRole);
        }

        // ✅ Assign regular user to manager role (branch user has branch_id = 2)
        if ($branchUser) {
            $branchUser->assignRole($managerRole);
        }
    }
}
