# 📋 Permission & Role Management System

## Overview
This Laravel application uses **Spatie Permission** package to manage role-based access control (RBAC). All permissions are stored in the database and can be assigned to roles, which are then assigned to users.

---

## 🔐 Permission Categories

### 1. **Dashboard & General**
- `view dashboard` - Access to main dashboard

### 2. **Product Management**
- `product.view` - View products
- `product.create` - Create new products
- `product.edit` - Edit products
- `product.delete` - Delete products
- `product.barcode` - Generate product barcodes
- `product.assembly` - View assembly information

### 3. **Product Discounts**
- `product.discount.view` - View discount products
- `product.discount.create` - Create discounts
- `product.discount.edit` - Edit discounts
- `product.discount.delete` - Delete discounts
- `product.discount.barcode` - Barcode management for discounts

### 4. **Category & Subcategory**
- `category.view` - View categories
- `category.create` - Create categories
- `category.edit` - Edit categories
- `category.delete` - Delete categories
- `subcategory.view` - View subcategories
- `subcategory.create` - Create subcategories
- `subcategory.edit` - Edit subcategories
- `subcategory.delete` - Delete subcategories

### 5. **Brand & Unit**
- `brand.view` - View brands
- `brand.create` - Create brands
- `brand.edit` - Edit brands
- `brand.delete` - Delete brands
- `unit.view` - View units
- `unit.create` - Create units
- `unit.edit` - Edit units
- `unit.delete` - Delete units

### 6. **Purchase Management**
- `purchase.view` - View purchases
- `purchase.create` - Create purchases
- `purchase.edit` - Edit purchases
- `purchase.delete` - Delete purchases
- `purchase.invoice` - View/print purchase invoices
- `purchase.return.view` - View purchase returns
- `purchase.return.create` - Create purchase returns
- `purchase.return.edit` - Edit purchase returns
- `purchase.return.delete` - Delete purchase returns

### 7. **Inward Gatepass**
- `inward.gatepass.view` - View inward gatepasses
- `inward.gatepass.create` - Create inward gatepasses
- `inward.gatepass.edit` - Edit inward gatepasses
- `inward.gatepass.delete` - Delete inward gatepasses

### 8. **Warehouse & Stock Management**
- `warehouse.view` - View warehouses
- `warehouse.create` - Create warehouses
- `warehouse.edit` - Edit warehouses
- `warehouse.delete` - Delete warehouses
- `warehouse.stock.view` - View warehouse stock
- `warehouse.stock.create` - Create warehouse stock
- `warehouse.stock.edit` - Edit warehouse stock
- `warehouse.stock.delete` - Delete warehouse stock
- `stock.transfer.view` - View stock transfers
- `stock.transfer.create` - Create stock transfers
- `stock.transfer.edit` - Edit stock transfers
- `stock.transfer.delete` - Delete stock transfers
- `stock.adjust` - Adjust stock levels

### 9. **Vendor Management**
- `vendor.view` - View vendors
- `vendor.create` - Create vendors
- `vendor.edit` - Edit vendors
- `vendor.delete` - Delete vendors
- `vendor.payments.view` - View vendor payments
- `vendor.payments.create` - Create vendor payments
- `vendor.payments.delete` - Delete vendor payments
- `vendor.bilties.view` - View vendor bilties
- `vendor.bilties.create` - Create vendor bilties
- `vendor.bilties.delete` - Delete vendor bilties

### 10. **Sales Management**
- `sale.view` - View sales
- `sale.create` - Create sales
- `sale.edit` - Edit sales
- `sale.delete` - Delete sales
- `sale.invoice` - View/print sale invoices
- `sale.delivery.challan` - Delivery challan
- `sale.receipt` - Sales receipt
- `sale.return.view` - View sale returns
- `sale.return.create` - Create sale returns

### 11. **Customer Management**
- `customer.view` - View customers
- `customer.create` - Create customers
- `customer.edit` - Edit customers
- `customer.delete` - Delete customers
- `customer.ledger` - View customer ledger
- `customer.payments.view` - View customer payments
- `customer.payments.create` - Create customer payments
- `customer.payments.delete` - Delete customer payments
- `customer.toggle.status` - Toggle customer status

### 12. **Sales Officer & Zone**
- `sales.officer.view` - View sales officers
- `sales.officer.create` - Create sales officers
- `sales.officer.edit` - Edit sales officers
- `sales.officer.delete` - Delete sales officers
- `zone.view` - View zones
- `zone.create` - Create zones
- `zone.edit` - Edit zones
- `zone.delete` - Delete zones

### 13. **Booking System**
- `booking.view` - View bookings
- `booking.create` - Create bookings
- `booking.edit` - Edit bookings
- `booking.delete` - Delete bookings
- `booking.receipt` - Booking receipt

### 14. **Voucher Management**
- `voucher.view` - View all vouchers
- `receipts.voucher.view` - View receipts vouchers
- `receipts.voucher.create` - Create receipts vouchers
- `receipts.voucher.delete` - Delete receipts vouchers
- `receipts.voucher.print` - Print receipts vouchers
- `payment.voucher.view` - View payment vouchers
- `payment.voucher.create` - Create payment vouchers
- `payment.voucher.delete` - Delete payment vouchers
- `payment.voucher.print` - Print payment vouchers
- `expense.voucher.view` - View expense vouchers
- `expense.voucher.create` - Create expense vouchers
- `expense.voucher.delete` - Delete expense vouchers
- `expense.voucher.print` - Print expense vouchers
- `journal.voucher.view` - View journal vouchers
- `journal.voucher.create` - Create journal vouchers
- `journal.voucher.delete` - Delete journal vouchers

### 15. **Chart of Accounts & Narration**
- `chart.of.accounts.view` - View chart of accounts
- `chart.of.accounts.create` - Create accounts
- `chart.of.accounts.edit` - Edit accounts
- `chart.of.accounts.delete` - Delete accounts
- `narration.view` - View narrations
- `narration.create` - Create narrations
- `narration.delete` - Delete narrations

### 16. **Reporting**
- `report.item.stock.view` - Item stock report
- `report.purchase.view` - Purchase report
- `report.sale.view` - Sale report
- `report.customer.ledger.view` - Customer ledger report
- `report.assembly.view` - Assembly report
- `report.inventory.onhand.view` - Inventory on-hand report

### 17. **User Management**
- `user.view` - View users
- `user.create` - Create users
- `user.edit` - Edit users
- `user.delete` - Delete users

### 18. **Role Management**
- `role.view` - View roles
- `role.create` - Create roles
- `role.edit` - Edit roles
- `role.delete` - Delete roles
- `role.permission.update` - Assign permissions to roles

### 19. **Permission Management**
- `permission.view` - View permissions
- `permission.create` - Create permissions
- `permission.delete` - Delete permissions

### 20. **Branch Management**
- `branch.view` - View branches
- `branch.create` - Create branches
- `branch.edit` - Edit branches
- `branch.delete` - Delete branches

---

## 🎯 How Permissions Work in Blade Files

### Using @can Directive
```blade
@can('product.view')
    <!-- Content visible only if user has 'product.view' permission -->
    <a href="{{ route('product') }}">Products</a>
@endcan
```

### Using @cannot Directive
```blade
@cannot('product.delete')
    <!-- Content hidden if user has 'product.delete' permission -->
    <span class="text-muted">Delete not allowed</span>
@endcannot
```

### Checking Multiple Permissions
```blade
@canany(['product.create', 'product.edit'])
    <!-- Show if user has ANY of these permissions -->
@endcanany
```

---

## 🔧 How to Assign Permissions to Users

### Via Admin Panel
1. Go to **User Management → Roles**
2. Create a new role (e.g., "Sales Manager")
3. Select permissions to assign to this role
4. Go to **User Management → Users**
5. Assign the role to specific users

### Via Code/Seeder
```php
$user = User::find(1);
$user->assignRole('sales-manager');
// or
$user->givePermissionTo('product.view');
```

---

## 📁 Files Modified for Permission System

### 1. **Database Seeder**
- `database/seeders/PermissionSeeder.php` - Contains all permission definitions

### 2. **Blade Templates Updated with @can Directives**
- `resources/views/admin_panel/layout/app.blade.php` - Main navigation menu
- `resources/views/admin_panel/zone/index.blade.php` - Zone management
- `resources/views/admin_panel/warehouses/index.blade.php` - Warehouse management
- `resources/views/admin_panel/warehouses/warehouse_stocks/index.blade.php` - Stock management
- `resources/views/admin_panel/warehouses/stock_transfers/index.blade.php` - Stock transfers
- `resources/views/admin_panel/vochers/all_recepit_vochers.blade.php` - Receipts vouchers
- `resources/views/admin_panel/vochers/payment_vochers/all_payment_vochers.blade.php` - Payment vouchers
- `resources/views/admin_panel/vochers/expense_vochers/all_expense_vochers.blade.php` - Expense vouchers

---

## 🚀 Running the Seeder

To populate all permissions in the database:

```bash
php artisan db:seed --class=PermissionSeeder
```

This will:
1. Clear existing permissions and roles
2. Create all permission definitions
3. Assign all permissions to the "super admin" role

---

## 💡 Best Practices

1. **Always use @can in views** - Never show buttons/links for actions user can't perform
2. **Use permission names consistently** - Follow the pattern: `resource.action` (e.g., `product.view`)
3. **Check in Controllers** - Use `authorize()` or `Gate::authorize()`
4. **Cache Permissions** - Spatie Permission caches permissions, remember to clear when adding new ones
5. **Test Permissions** - Always test different user roles

---

## 🔄 Clearing Permission Cache

When you add new permissions, clear the cache:

```bash
php artisan cache:forget spatie.permission.cache
```

---

## 📞 Support

For issues with permissions or role assignments, check:
1. That the permission exists in the database
2. That the user has the correct role assigned
3. That the role has the required permission
4. Clear the cache if permissions were recently added

---

**Last Updated:** January 2025  
**Permission Package:** Spatie/Laravel-Permission
