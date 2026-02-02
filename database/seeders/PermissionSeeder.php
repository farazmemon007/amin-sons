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

        // Comprehensive permissions (deduplicated and organized)
        $permissions = [
            // Dashboard
            'view dashboard',

            // Product Management
            'product.view',
            'product.create',
            'product.edit',
            'product.delete',
            'product.barcode',
            'product.assembly',

            // Product Discount
            'product.discount.view',
            'product.discount.create',
            'product.discount.edit',
            'product.discount.delete',
            'product.discount.barcode',

            // Category & SubCategory
            'category.view',
            'category.create',
            'category.edit',
            'category.delete',
            'subcategory.view',
            'subcategory.create',
            'subcategory.edit',
            'subcategory.delete',

            // Brand
            'brand.view',
            'brand.create',
            'brand.edit',
            'brand.delete',

            // Unit
            'unit.view',
            'unit.create',
            'unit.edit',
            'unit.delete',

            // Purchase Management
            'purchase.view',
            'purchase.create',
            'purchase.edit',
            'purchase.delete',
            'purchase.invoice',
            'purchase.return.view',
            'purchase.return.create',
            'purchase.return.edit',
            'purchase.return.delete',

            // Inward Gatepass
            'inward.gatepass.view',
            'inward.gatepass.create',
            'inward.gatepass.edit',
            'inward.gatepass.delete',

            // Warehouse & Stock
            'warehouse.view',
            'warehouse.create',
            'warehouse.edit',
            'warehouse.delete',
            'warehouse.stock.view',
            'warehouse.stock.create',
            'warehouse.stock.edit',
            'warehouse.stock.delete',
            'stock.transfer.view',
            'stock.transfer.create',
            'stock.transfer.edit',
            'stock.transfer.delete',
            'stock.adjust',

            // Vendor
            'vendor.view',
            'vendor.create',
            'vendor.edit',
            'vendor.delete',
            'vendor.payments.view',
            'vendor.payments.create',
            'vendor.payments.delete',
            'vendor.bilties.view',
            'vendor.bilties.create',
            'vendor.bilties.delete',

            // Sales Management
            'sale.view',
            'sale.create',
            'sale.edit',
            'sale.delete',
            'sale.invoice',
            'sale.delivery.challan',
            'sale.receipt',
            'sale.return.view',
            'sale.return.create',

            // Customer
            'customer.view',
            'customer.create',
            'customer.edit',
            'customer.delete',
            'customer.ledger',
            'customer.payments.view',
            'customer.payments.create',
            'customer.payments.delete',
            'customer.toggle.status',

            // Sales Officer
            'sales.officer.view',
            'sales.officer.create',
            'sales.officer.edit',
            'sales.officer.delete',

            // Zone
            'zone.view',
            'zone.create',
            'zone.edit',
            'zone.delete',

            // Booking
            'booking.view',
            'booking.create',
            'booking.edit',
            'booking.delete',
            'booking.receipt',

            // Vouchers
            'voucher.view',
            'receipts.voucher.view',
            'receipts.voucher.create',
            'receipts.voucher.delete',
            'receipts.voucher.print',
            'payment.voucher.view',
            'payment.voucher.create',
            'payment.voucher.delete',
            'payment.voucher.print',
            'expense.voucher.view',
            'expense.voucher.create',
            'expense.voucher.delete',
            'expense.voucher.print',
            'journal.voucher.view',
            'journal.voucher.create',
            'journal.voucher.delete',

            // Chart of Accounts
            'chart.of.accounts.view',
            'chart.of.accounts.create',
            'chart.of.accounts.edit',
            'chart.of.accounts.delete',

            // Narration
            'narration.view',
            'narration.create',
            'narration.delete',

            // Reporting
            'report.item.stock.view',
            'report.purchase.view',
            'report.sale.view',
            'report.customer.ledger.view',
            'report.assembly.view',
            'report.inventory.onhand.view',

            // User Management
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',

            // Role Management
            'role.view',
            'role.create',
            'role.edit',
            'role.delete',
            'role.permission.update',

            // Permission Management
            'permission.view',
            'permission.create',
            'permission.delete',

            // Branch
            'branch.view',
            'branch.create',
            'branch.edit',
            'branch.delete',

            // Legacy permissions (keeping for backward compatibility)
            'create product',
            'edit product',
            'delete product',
            'create role',
            'update role',
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

        // Sync all permissions that exist in DB for the specified guard to super admin.
        // This is more robust than relying solely on the local $permissions array.
        $allPermissionNames = Permission::where('guard_name', $guard)->pluck('name')->toArray();
        $superAdmin->syncPermissions($allPermissionNames);
    }
}
