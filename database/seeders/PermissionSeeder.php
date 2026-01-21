<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 🔴 Clear cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 🔴 Disable FK checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('model_has_permissions')->truncate();
        DB::table('role_has_permissions')->truncate();
        DB::table('permissions')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $guard = 'web';

        $permissions = [
            'create product',
            'edit product',
            'delete product',
            'view product',

            'create role',
            'update role',

            'view dashboard',
            'view discount',
            'view category',
            'view subcategory',
            'view brand',
            'view unit',
            'edit stock',

            'view inward gatepass',
            'create inward gatepass',
            'view purchase',
            'view vendor',
            'view warehouse',
            'view warehouse stock',
            'view stock transfer',
            'view sale',
            'view customer',
            'view sales officer',
            'view zone',

            'view vouchers',
            'view chart of accounts',
            'view narration',
            'view receipts voucher',
            'view payment voucher',
            'view expense voucher',
            'view journal voucher',

            'view reports',
            'view item stock report',
            'view purchase report',
            'view sale report',
            'view customer ledger report',
            'view assembly report',
            'view inventory on hand',

            'view user',
            'view role',
            'view permissions',
            'view branch',
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission,
                'guard_name' => $guard,
            ]);
        }

        $superAdmin = Role::firstOrCreate([
            'name' => 'super admin',
            'guard_name' => $guard,
        ]);

        $superAdmin->syncPermissions($permissions);
    }
}
