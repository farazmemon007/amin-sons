<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * ============================================================
 * ERP: Warehouse Incharge (Store Incharge) Permission Seeder
 * ============================================================
 *
 * Run this seeder anytime you need to restore or update
 * the 'store incharge' role permissions:
 *
 *   php artisan db:seed --class=WarehouseInchargePermissionSeeder
 *
 * ── Business Logic ──────────────────────────────────────────
 * A Warehouse Incharge is responsible for:
 *   ✅ Receiving goods       → Inward Gatepass (create/edit/view)
 *   ✅ Dispatching goods     → Outward Gatepass (create/edit/view/print)
 *   ✅ Warehouse stock       → View, create & edit warehouse stock
 *   ✅ Stock transfers       → Transfer between warehouses
 *   ✅ Stock requests        → Request & approve inter-branch stock
 *   ✅ Purchase verification → View purchases to verify deliveries
 *   ✅ Sales awareness       → View sales/DC to prepare dispatches
 *   ✅ Inventory reports     → View stock & on-hand reports
 *   ✅ Pending tracking      → Customer & vendor remaining items
 *
 * Warehouse Incharge CANNOT:
 *   ❌ Create/edit/delete products
 *   ❌ Create/edit sales or invoices
 *   ❌ Manage customers, vendors, payments
 *   ❌ Manage roles, users, permissions
 *   ❌ Create purchases
 *   ❌ Create/delete branches or warehouses
 *   ❌ Access financial vouchers (receipts, payments, expenses)
 *   ❌ Delete any document (data integrity)
 * ============================================================
 */
class WarehouseInchargePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles/permissions (important after changes)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ─── Step 1: Find or create the role ─────────────────────────────
        $role = Role::firstOrCreate([
            'name'       => 'store incharge',
            'guard_name' => 'web',
        ]);
        $this->command->info("✅ Role: '{$role->name}' (ID: {$role->id})");

        // ─── Step 2: Define all required permissions ──────────────────────
        $permissions = [

            // ── Dashboard ─────────────────────────────────────────────────
            'view dashboard',

            // ── Products (VIEW ONLY) ───────────────────────────────────────
            'product.view',
            'view warehouse stock',

            // ── Warehouse ─────────────────────────────────────────────────
            'warehouse.view',
            'warehouse.stock.view',
            'warehouse.stock.create',
            'warehouse.stock.edit',
            'warehouse.order.view',
            'warehouse.orders.view',
            'warehouse.order.edit',

            // ── Inward Gatepass (RECEIVE GOODS — Core Duty) ───────────────
            'inward.gatepass.view',
            'inward.gatepass.create',
            'inward.gatepass.edit',
            'view inward gatepass',
            'create inward gatepass',

            // ── Outward Gatepass (DISPATCH GOODS — Core Duty) ─────────────
            'outward.gatepass.view',
            'outward.gatepass.create',
            'outward.gatepass.edit',
            'outward.gatepass.print',

            // ── Purchase Order ───────────────────────────────────────────
            'purchase.order.view',
            'purchase.order.create',
            'purchase.order.edit',

            // ── Purchase (VIEW ONLY — verify what should arrive) ───────────
            'purchase.view',
            'view purchase',

            // ── Sales (VIEW ONLY — know what to dispatch) ──────────────────
            'sale.view',
            'view sale',
            'sale.delivery.challan',   // View DC to prepare dispatch
            'sale.invoice',            // View invoice for reference
            'generate Dc.view',        // DC index page
            'find Dc.view',            // Find DC by number

            // ── Booking (VIEW ONLY — prepare items before dispatch) ────────
            'booking.view',
            'booking.invoice',

            // ── Stock Management ───────────────────────────────────────────
            'stock.transfer.view',
            'stock.transfer.create',   // Transfer stock between warehouses
            'stock.transfer.edit',
            'stock.request.view',
            'stock.request.create',    // Request stock from other branch
            'stock.request.approve',   // Approve incoming stock requests

            // ── Reports (own warehouse data only) ─────────────────────────
            'report.item.stock.view',
            'report.inventory.onhand.view',
            'view item stock report',
            'view inventory on hand',
            'report.stock.hold.view',
            'view reports',

            // ── Pending Tracking ───────────────────────────────────────────
            'customerremainingproducts.view',      // Pending dispatch items
            'customerremainingproducts.view.all',  // All pending dispatches
            // Vendor remaining uses 'purchase.view' (already listed above)
        ];

        // ─── Step 3: Create missing permissions & collect all ────────────
        $this->command->info("\nCreating/verifying permissions:");
        $permObjects = [];
        foreach ($permissions as $permName) {
            $perm = Permission::firstOrCreate([
                'name'       => $permName,
                'guard_name' => 'web',
            ]);
            $permObjects[] = $perm->name;
            $this->command->line("   ✓ {$perm->name}");
        }

        // ─── Step 4: Sync permissions to role ────────────────────────────
        $role->syncPermissions($permObjects);

        // ─── Step 5: Verify ───────────────────────────────────────────────
        $count = $role->fresh()->permissions()->count();
        $this->command->newLine();
        $this->command->info("✅ Total permissions assigned to '{$role->name}': {$count}");
        $this->command->info("✅ Cache cleared automatically by Spatie.");
    }
}
