<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * ============================================================
 * RolePermissionSeeder — Curated per-role permission sets
 * ============================================================
 *
 * Run after PermissionSeeder:
 *   php artisan db:seed --class=RolePermissionSeeder
 *
 * Roles defined here:
 *   1. super admin     — Everything (set in PermissionSeeder)
 *   2. branch manager  — Full branch operations, no system admin
 *   3. store incharge  — Warehouse/inventory/gatepass focus
 *   4. sales staff     — Sales & customer focus
 *   5. accountant      — Vouchers, ledgers, reports focus
 *   6. manager         — Alias for branch manager (backward compat)
 *   7. staff           — Same as sales staff (backward compat)
 * ============================================================
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';

        // =====================================================================
        // ROLE: branch manager
        // Full branch-level operations. No system-level admin (users/roles/perms/branches).
        // =====================================================================
        $branchManagerPermissions = [
            // Dashboard
            'view dashboard',

            // DC
            'generate Dc.view',
            'find Dc.view',

            // Products (full)
            'product.view', 'product.create', 'product.edit', 'product.barcode', 'product.assembly',
            'product.discount.view', 'product.discount.create', 'product.discount.edit', 'product.discount.barcode',
            'category.view', 'category.create', 'category.edit',
            'subcategory.view', 'subcategory.create', 'subcategory.edit',
            'brand.view', 'brand.create', 'brand.edit',
            'unit.view', 'unit.create', 'unit.edit',

            // Purchase (full operations for own branch)
            'purchase.view', 'purchase.create', 'purchase.edit',
            'purchase.invoice',
            'purchase.return.view', 'purchase.return.create', 'purchase.return.edit',
            'purchase.order.view', 'purchase.order.create', 'purchase.order.edit',
            'inward.gatepass.view', 'inward.gatepass.create', 'inward.gatepass.edit',
            'outward.gatepass.view', 'outward.gatepass.create', 'outward.gatepass.edit', 'outward.gatepass.print',

            // Warehouse
            'warehouse.view', 'warehouse.create', 'warehouse.edit',
            'warehouse.stock.view', 'warehouse.stock.create', 'warehouse.stock.edit',
            'warehouse.orders.view', 'warehouse.order.view', 'warehouse.order.edit',
            'stock.transfer.view', 'stock.transfer.create', 'stock.transfer.edit',
            'stock.request.view', 'stock.request.create', 'stock.request.approve',

            // Vendors (full)
            'vendor.view', 'vendor.create', 'vendor.edit',
            'vendor.ledger', 'vendor.ledger.branch.view',
            'vendor.payments.view', 'vendor.payments.create',
            'vendor.bilties.view', 'vendor.bilties.create',

            // Sales (full)
            'sale.view', 'sale.create', 'sale.edit',
            'sale.invoice', 'sale.delivery.challan', 'sale.receipt',
            'sale.return.view', 'sale.return.create',

            // Customers (full)
            'customer.view', 'customer.create', 'customer.edit',
            'customer.ledger', 'customer.payments.view', 'customer.payments.create',
            'customer.toggle.status',
            'customerremainingproducts.view',

            // Sales Officers & Zones (manage own branch)
            'sales.officer.view', 'sales.officer.create', 'sales.officer.edit',
            'zone.view', 'zone.create', 'zone.edit',

            // Bookings
            'booking.view', 'booking.create', 'booking.edit',
            'booking.receipt', 'booking.invoice',

            // Vouchers (full access for branch manager)
            'voucher.view',
            'receipts.voucher.view', 'receipts.voucher.create', 'receipts.voucher.print',
            'payment.voucher.view', 'payment.voucher.create', 'payment.voucher.print',
            'expense.voucher.view', 'expense.voucher.create', 'expense.voucher.print',
            'journal.voucher.view', 'journal.voucher.create',
            'chart.of.accounts.view', 'chart.of.accounts.create', 'chart.of.accounts.edit',
            'narration.view', 'narration.create',

            // Complaints
            'complaint.view', 'complaint.create', 'complaint.edit', 'complaint.print',

            // Reports (own branch)
            'report.item.stock.view',
            'report.purchase.view',
            'report.sale.view',
            'report.customer.ledger.view',
            'report.vendor.ledger.view',
            'report.assembly.view',
            'report.inventory.onhand.view',
            'report.stock.hold.view',
            'branch.ledger.view', 'branch.ledger.report', 'branch.account.view',

            // Inter-branch (limited — can request/view but not create vouchers across branches)
            'inter.branch.voucher.view',
            'stock.request.view', 'stock.request.create', 'stock.request.approve',

            // User management for OWN branch (can view/create users within their branch)
            'user.view', 'user.create', 'user.edit',
        ];

        // =====================================================================
        // ROLE: store incharge
        // Focus: receiving goods, dispatching, warehouse stock management
        // =====================================================================
        $storeInchargePermissions = [
            // Dashboard
            'view dashboard',

            // DC
            'generate Dc.view',
            'find Dc.view',

            // Products (view only)
            'product.view',

            // Warehouse (core duty)
            'warehouse.view',
            'warehouse.stock.view', 'warehouse.stock.create', 'warehouse.stock.edit',
            'warehouse.orders.view', 'warehouse.order.view', 'warehouse.order.edit',

            // Inward Gatepass (receive goods — core duty)
            'inward.gatepass.view', 'inward.gatepass.create', 'inward.gatepass.edit',

            // Outward Gatepass (dispatch goods — core duty)
            'outward.gatepass.view', 'outward.gatepass.create', 'outward.gatepass.edit', 'outward.gatepass.print',

            // Purchase Orders (view to verify deliveries)
            'purchase.order.view', 'purchase.order.create', 'purchase.order.edit',
            'purchase.view', 'purchase.invoice',

            // Sales (view only — know what to dispatch)
            'sale.view', 'sale.delivery.challan', 'sale.invoice',
            'booking.view', 'booking.invoice',

            // Stock Management
            'stock.transfer.view', 'stock.transfer.create', 'stock.transfer.edit',
            'stock.request.view', 'stock.request.create', 'stock.request.approve',

            // Complaints (view damaged goods)
            'complaint.view', 'complaint.create', 'complaint.print',

            // Reports (own warehouse data only)
            'report.item.stock.view',
            'report.inventory.onhand.view',
            'report.stock.hold.view',

            // Customer remaining (pending dispatch tracking)
            'customerremainingproducts.view',
        ];

        // =====================================================================
        // ROLE: sales staff
        // Focus: creating sales, managing customers, delivery challans
        // =====================================================================
        $salesStaffPermissions = [
            // Dashboard
            'view dashboard',

            // DC
            'generate Dc.view',
            'find Dc.view',

            // Products (view only)
            'product.view',

            // Sales
            'sale.view', 'sale.create', 'sale.edit',
            'sale.invoice', 'sale.delivery.challan', 'sale.receipt',
            'sale.return.view', 'sale.return.create',

            // Outward Gatepass (create DC/challan)
            'outward.gatepass.view', 'outward.gatepass.create', 'outward.gatepass.print',

            // Customers
            'customer.view', 'customer.create', 'customer.edit',
            'customer.ledger', 'customer.payments.view', 'customer.payments.create',
            'customerremainingproducts.view',

            // Complaints (view)
            'complaint.view', 'complaint.create',

            // Bookings
            'booking.view', 'booking.create', 'booking.invoice',

            // Zones & Sales Officers (view)
            'zone.view',
            'sales.officer.view',

            // Reports (own branch sale reports only)
            'report.sale.view',
            'report.customer.ledger.view',
        ];

        // =====================================================================
        // ROLE: accountant
        // Focus: vouchers, ledgers, financial reports
        // =====================================================================
        $accountantPermissions = [
            // Dashboard
            'view dashboard',

            // Vouchers (full financial access)
            'voucher.view',
            'receipts.voucher.view', 'receipts.voucher.create', 'receipts.voucher.delete', 'receipts.voucher.print',
            'payment.voucher.view', 'payment.voucher.create', 'payment.voucher.delete', 'payment.voucher.print',
            'expense.voucher.view', 'expense.voucher.create', 'expense.voucher.delete', 'expense.voucher.print',
            'journal.voucher.view', 'journal.voucher.create', 'journal.voucher.delete',
            'chart.of.accounts.view', 'chart.of.accounts.create', 'chart.of.accounts.edit',
            'narration.view', 'narration.create',

            // Vendor Ledger
            'vendor.ledger', 'vendor.ledger.branch.view',
            'vendor.payments.view', 'vendor.payments.create',

            // Customer Ledger
            'customer.ledger', 'customer.payments.view', 'customer.payments.create',

            // Inter-Branch
            'inter.branch.voucher.view', 'inter.branch.voucher.create',
            'branch.ledger.view', 'branch.ledger.report', 'branch.account.view',

            // Reports (financial)
            'report.sale.view',
            'report.purchase.view',
            'report.customer.ledger.view',
            'report.vendor.ledger.view',
            'branch.ledger.view',

            // View products/sales/purchases for reference
            'product.view',
            'sale.view', 'sale.invoice',
            'purchase.view', 'purchase.invoice',
            'customer.view',
            'vendor.view',
        ];

        // =====================================================================
        // Create roles & assign permissions
        // =====================================================================
        $roleMap = [
            'branch manager'  => $branchManagerPermissions,
            'manager'         => $branchManagerPermissions,   // backward compat alias
            'store incharge'  => $storeInchargePermissions,
            'sales staff'     => $salesStaffPermissions,
            'staff'           => $salesStaffPermissions,       // backward compat alias
            'accountant'      => $accountantPermissions,
        ];

        foreach ($roleMap as $roleName => $permissionList) {
            $role = Role::firstOrCreate([
                'name'       => $roleName,
                'guard_name' => $guard,
            ]);

            // Filter only permissions that actually exist in the DB
            $existing = Permission::whereIn('name', $permissionList)
                ->where('guard_name', $guard)
                ->pluck('name')
                ->toArray();

            $role->syncPermissions($existing);

            $missing = array_diff($permissionList, $existing);
            if (!empty($missing)) {
                $this->command->warn("⚠️  Role [{$roleName}]: " . count($missing) . " permission(s) not found in DB:");
                foreach ($missing as $m) {
                    $this->command->line("   - {$m}");
                }
            }

            $this->command->info("✅ Role [{$roleName}]: " . count($existing) . " permissions assigned.");
        }

        $this->command->newLine();
        $this->command->info('✅ RolePermissionSeeder complete.');
    }
}
