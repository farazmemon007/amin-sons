<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Models\Branch;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Clear cache ─────────────────────────────────────────────────
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // ─── Disable FK checks for clean truncate ────────────────────────
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('model_has_permissions')->truncate();
        DB::table('role_has_permissions')->truncate();
        DB::table('permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $guard = 'web';

        // =====================================================================
        // STANDARD PERMISSIONS (module.action format — apply to user's OWN branch)
        //
        // Convention:
        //   product.view   = user can view products in THEIR assigned branch
        //   branch:{id}:product.view = cross-branch access to branch {id}
        //                              (granted explicitly to specific users)
        // =====================================================================
        $permissions = [

            // ── Dashboard ─────────────────────────────────────────────────
            'view dashboard',

            // ── DC / Delivery Challans ────────────────────────────────────
            'generate Dc.view',     // Create DC index page
            'find Dc.view',         // Find DC by number

            // ── Management (top-level guard) ──────────────────────────────
            'management.view',

            // ── Products ──────────────────────────────────────────────────
            'product.view',
            'product.create',
            'product.edit',
            'product.delete',
            'product.barcode',
            'product.assembly',

            // ── Product Discounts ─────────────────────────────────────────
            'product.discount.view',
            'product.discount.create',
            'product.discount.edit',
            'product.discount.delete',
            'product.discount.barcode',

            // ── Categories ────────────────────────────────────────────────
            'category.view',
            'category.create',
            'category.edit',
            'category.delete',

            // ── Sub-Categories ────────────────────────────────────────────
            'subcategory.view',
            'subcategory.create',
            'subcategory.edit',
            'subcategory.delete',

            // ── Brands ────────────────────────────────────────────────────
            'brand.view',
            'brand.create',
            'brand.edit',
            'brand.delete',

            // ── Units ─────────────────────────────────────────────────────
            'unit.view',
            'unit.create',
            'unit.edit',
            'unit.delete',

            // ── Purchase Management ───────────────────────────────────────
            'purchase.view',
            'purchase.create',
            'purchase.edit',
            'purchase.delete',
            'purchase.invoice',
            'purchase.return.view',
            'purchase.return.create',
            'purchase.return.edit',
            'purchase.return.delete',

            // ── Purchase Orders ───────────────────────────────────────────
            'purchase.order.view',
            'purchase.order.create',
            'purchase.order.edit',
            'purchase.order.delete',

            // ── Inward Gatepass ───────────────────────────────────────────
            'inward.gatepass.view',
            'inward.gatepass.create',
            'inward.gatepass.edit',
            'inward.gatepass.delete',

            // ── Outward Gatepass ──────────────────────────────────────────
            'outward.gatepass.view',
            'outward.gatepass.create',
            'outward.gatepass.edit',
            'outward.gatepass.delete',
            'outward.gatepass.print',

            // ── Warehouses ────────────────────────────────────────────────
            'warehouse.view',
            'warehouse.create',
            'warehouse.edit',
            'warehouse.delete',
            'warehouse.manage',          // Assign warehouses to branches/users

            // ── Warehouse Stock ───────────────────────────────────────────
            'warehouse.stock.view',
            'warehouse.stock.create',
            'warehouse.stock.edit',
            'warehouse.stock.delete',

            // ── Warehouse Orders ──────────────────────────────────────────
            'warehouse.orders.view',     // Index listing
            'warehouse.order.view',      // Single order view
            'warehouse.order.edit',      // Edit order

            // ── Stock Transfers ───────────────────────────────────────────
            'stock.transfer.view',
            'stock.transfer.create',
            'stock.transfer.edit',
            'stock.transfer.delete',
            'stock.adjust',              // Adjust/write-off stock quantities

            // ── Inter-Branch Stock Requests ───────────────────────────────
            'stock.request.view',
            'stock.request.create',
            'stock.request.approve',
            'stock.request.reject',

            // ── Inter-Branch Vouchers ─────────────────────────────────────
            'inter.branch.voucher.view',
            'inter.branch.voucher.create',
            'inter.branch.voucher.delete',

            // ── Branch Ledger ─────────────────────────────────────────────
            'branch.ledger.view',        // View own-branch ledger
            'branch.ledger.report',      // Export/print branch ledger
            'branch.account.view',       // View branch accounts

            // ── Vendors ───────────────────────────────────────────────────
            'vendor.view',
            'vendor.create',
            'vendor.edit',
            'vendor.delete',
            'vendor.ledger',             // View vendor ledger
            'vendor.ledger.branch.view', // View vendor ledger filtered by branch
            'vendor.payments.view',
            'vendor.payments.create',
            'vendor.payments.delete',
            'vendor.bilties.view',
            'vendor.bilties.create',
            'vendor.bilties.delete',

            // ── Sales ─────────────────────────────────────────────────────
            'sale.view',
            'sale.create',
            'sale.edit',
            'sale.delete',
            'sale.invoice',
            'sale.delivery.challan',
            'sale.receipt',
            'sale.return.view',
            'sale.return.create',
            'sale.return.edit',
            'sale.return.delete',

            // ── Customers ─────────────────────────────────────────────────
            'customer.view',
            'customer.create',
            'customer.edit',
            'customer.delete',
            'customer.ledger',
            'customer.payments.view',
            'customer.payments.create',
            'customer.payments.delete',
            'customer.toggle.status',
            'customerremainingproducts.view',      // Own-branch pending deliveries
            'customerremainingproducts.view.all',  // All branches pending deliveries

            // ── Sales Officers ────────────────────────────────────────────
            'sales.officer.view',
            'sales.officer.create',
            'sales.officer.edit',
            'sales.officer.delete',

            // ── Zones ─────────────────────────────────────────────────────
            'zone.view',
            'zone.create',
            'zone.edit',
            'zone.delete',

            // ── Bookings ──────────────────────────────────────────────────
            'booking.view',
            'booking.create',
            'booking.edit',
            'booking.delete',
            'booking.receipt',
            'booking.invoice',

            // ── Vouchers (parent gate) ────────────────────────────────────
            'voucher.view',

            // ── Receipts Voucher ──────────────────────────────────────────
            'receipts.voucher.view',
            'receipts.voucher.create',
            'receipts.voucher.delete',
            'receipts.voucher.print',

            // ── Payment Voucher ───────────────────────────────────────────
            'payment.voucher.view',
            'payment.voucher.create',
            'payment.voucher.delete',
            'payment.voucher.print',

            // ── Expense Voucher ───────────────────────────────────────────
            'expense.voucher.view',
            'expense.voucher.create',
            'expense.voucher.delete',
            'expense.voucher.print',

            // ── Journal Voucher ───────────────────────────────────────────
            'journal.voucher.view',
            'journal.voucher.create',
            'journal.voucher.delete',

            // ── Chart of Accounts ─────────────────────────────────────────
            'chart.of.accounts.view',
            'chart.of.accounts.create',
            'chart.of.accounts.edit',
            'chart.of.accounts.delete',

            // ── Narrations ────────────────────────────────────────────────
            'narration.view',
            'narration.create',
            'narration.delete',

            // ── Complaints ────────────────────────────────────────────────
            'complaint.view',
            'complaint.create',
            'complaint.edit',
            'complaint.delete',
            'complaint.print',
            'complaint.home_service',

            // ── Reports ───────────────────────────────────────────────────
            // Each permission = own-branch report access
            // Cross-branch access = branch:{id}:report.*.view (generated below)
            'report.item.stock.view',
            'report.purchase.view',
            'report.sale.view',
            'report.customer.ledger.view',
            'report.vendor.ledger.view',
            'report.assembly.view',
            'report.inventory.onhand.view',
            'report.stock.hold.view',

            // ── User Management ───────────────────────────────────────────
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',

            // ── Role Management ───────────────────────────────────────────
            'role.view',
            'role.create',
            'role.edit',
            'role.delete',
            'role.permission.update',

            // ── Permission Management ─────────────────────────────────────
            'permission.view',
            'permission.create',
            'permission.delete',

            // ── Branch Management ─────────────────────────────────────────
            'branch.view',
            'branch.create',
            'branch.edit',
            'branch.delete',
        ];

        // =====================================================================
        // PER-BRANCH CROSS-ACCESS PERMISSIONS
        //
        // Format: branch:{branch_id}:module.action
        //
        // Purpose: Grant a user from Branch A explicit access to data of Branch B.
        // These are NEVER auto-granted — must be assigned manually per user/role.
        //
        // Examples:
        //   branch:2:product.view    → User can view Branch 2's products
        //   branch:2:sale.view       → User can view Branch 2's sales
        //   branch:2:purchase.view   → User can view Branch 2's purchases
        // =====================================================================
        $crossBranchPermissions = [
            'product.view',
            'sale.view',
            'purchase.view',
            'purchase.create',
            'purchase.edit',
            'purchase.delete',
            'report.sale.view',
            'report.item.stock.view',
            'report.customer.ledger.view',
            'report.vendor.ledger.view',
            'report.purchase.view',
            'warehouse.stock.view',
            'customer.view',
            'customer.ledger',
        ];

        foreach (Branch::pluck('id') as $branchId) {
            foreach ($crossBranchPermissions as $perm) {
                $permissions[] = "branch:{$branchId}:{$perm}";
            }
        }

        // ─── Deduplicate ──────────────────────────────────────────────────
        $permissions = array_values(array_unique(array_filter($permissions)));

        // ─── Create all permissions ───────────────────────────────────────
        foreach ($permissions as $permName) {
            Permission::firstOrCreate([
                'name'       => $permName,
                'guard_name' => $guard,
            ]);
        }

        $this->command->info('✅ Created ' . count($permissions) . ' permissions.');

        // ─── Super Admin gets ALL permissions ────────────────────────────
        $superAdmin = Role::firstOrCreate([
            'name'       => 'super admin',
            'guard_name' => $guard,
        ]);

        $allPermissions = Permission::where('guard_name', $guard)->pluck('name')->toArray();
        $superAdmin->syncPermissions($allPermissions);

        $this->command->info('✅ Super admin synced with all ' . count($allPermissions) . ' permissions.');
    }
}
