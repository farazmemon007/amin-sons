<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Run order:
     *   1. BranchSeeder          — Create branches first (PermissionSeeder needs branch IDs)
     *   2. PermissionSeeder      — Create all permissions + assign to super admin
     *   3. RolePermissionSeeder  — Create roles + assign curated permission sets
     *   4. ModuleSeeder          — Module records for the UI
     */
    public function run(): void
    {
        $this->call([
            BranchSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            ModuleSeeder::class,
            // ── Uncomment as needed ──────────────────────────────────────
            // CategorySeeder::class,
            // WarehouseSeeder::class,
            // BranchWarehouseSeeder::class,
            // ProductSeeder::class,
            // WarehouseStockSeeder::class,
            // StockSeeder::class,
            // StockMovementSeeder::class,
            // ChartOfAccountSeeder::class,
            // ProductTypeSeeder::class,
            // CustomerSeeder::class,
            // BrandSeeder::class,
            // UnitSeeder::class,
        ]);

        // ── Seed default users ────────────────────────────────────────────
        $superAdminRole   = Role::firstOrCreate(['name' => 'super admin']);
        $branchManagerRole = Role::firstOrCreate(['name' => 'branch manager']);

        // Super Admin (no branch — system-wide access)
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name'      => 'admin',
                'password'  => Hash::make('admin'),
                'branch_id' => null,
            ]
        );

        // Super Admin (developer account — no branch)
        $devUser = User::firstOrCreate(
            ['email' => 'f@gmail.com'],
            [
                'name'      => 'faraz memon',
                'password'  => Hash::make('123'),
                'branch_id' => null,
            ]
        );

        // Branch User (assigned to branch 2)
        $branchUser = User::firstOrCreate(
            ['email' => 'soban@soban.com'],
            [
                'name'      => 'soban',
                'password'  => Hash::make('soban'),
                'branch_id' => 2,
            ]
        );

        // ── Assign roles ──────────────────────────────────────────────────
        $adminUser->syncRoles([$superAdminRole]);
        $devUser->syncRoles([$superAdminRole]);
        $branchUser->syncRoles([$branchManagerRole]);

        $this->command->info('✅ Default users seeded and roles assigned.');
    }
}
