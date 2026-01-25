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

        // Requested permissions (deduplicated)
        $permissions = [
            'create product',
            'edit product',
            'delete product',
            'product.view',
            'product.create',
            'product.edit',
            'product.delete',
            'product.barcode',
            'product.assembly',
            'product.discount.view',
            'product.discount.create',
            'product.discount.barcode',
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

        // Ensure unique values just in case
        $permissions = array_values(array_unique($permissions));

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => $guard,
            ]);
        }

        $superAdmin = Role::firstOrCreate([
            'name' => 'super admin',
            'guard_name' => $guard,
        ]);

        // Sync by names (array of names is supported)
        $superAdmin->syncPermissions($permissions);
    }
}
