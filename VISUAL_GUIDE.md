# 🎯 Permission System - Visual Setup Guide

## Complete Flow Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                  PERMISSION SYSTEM ARCHITECTURE                      │
└─────────────────────────────────────────────────────────────────────┘

                            DATABASE
                          ┌─────────┐
                          │  Users  │
                          └────┬────┘
                               │
                        ┌──────┴──────┐
                        │             │
                    ┌───▼────┐   ┌───▼────────┐
                    │ Roles  │   │Permissions │
                    └───┬────┘   └────┬───────┘
                        │             │
                    ┌───┴─────────────┘
                    │
              ┌─────▼──────┐
              │ role_has_  │
              │permissions │
              └─────┬──────┘
                    │
        ┌───────────┼───────────┐
        │           │           │
    ┌───▼──┐  ┌────▼───┐  ┌───▼──┐
    │@can  │  │ Blade  │  │Route │
    │check │  │ render │  │check │
    └──────┘  └────────┘  └──────┘
```

## Data Flow

```
USER LOGS IN
    │
    ▼
SYSTEM LOADS USER ROLES
    │
    ▼
SYSTEM LOADS ROLE PERMISSIONS
    │
    ▼
PERMISSION CACHED FOR PERFORMANCE
    │
    ▼
BLADE TEMPLATES EVALUATE @can DIRECTIVES
    │
    ▼
UI ELEMENTS SHOWN/HIDDEN BASED ON PERMISSIONS
```

## Setup Process

```
STEP 1: Run Seeder
┌─────────────────────────────────────────┐
│ php artisan db:seed                     │
│ --class=PermissionSeeder                │
└────┬────────────────────────────────────┘
     │
     ▼
Creates 150+ Permissions in Database
     │
     ▼
Creates "super admin" Role
     │
     ▼
Assigns all Permissions to super admin


STEP 2: Create Roles
┌─────────────────────────────────────────┐
│ Admin Panel → User Management → Roles   │
│ Click "Create New Role"                 │
└────┬────────────────────────────────────┘
     │
     ▼
Enter Role Name (e.g., "Sales Manager")
     │
     ▼
Select Permissions for this Role
     │
     ▼
Save Role


STEP 3: Assign Roles to Users
┌─────────────────────────────────────────┐
│ Admin Panel → User Management → Users   │
│ Click Edit on User                      │
└────┬────────────────────────────────────┘
     │
     ▼
Select Role from dropdown
     │
     ▼
Save


STEP 4: Test
┌─────────────────────────────────────────┐
│ Logout and Login as that User           │
└────┬────────────────────────────────────┘
     │
     ▼
Verify Menu items show/hide correctly
     │
     ▼
Verify Buttons appear based on permissions
```

## Permission Assignment Matrix

```
┌──────────────────┬─────────┬─────────┬──────────┬──────────┐
│ Role             │ Create  │  View   │  Edit    │  Delete  │
├──────────────────┼─────────┼─────────┼──────────┼──────────┤
│ Super Admin      │   ✅    │   ✅    │    ✅    │    ✅    │
│ Admin            │   ✅    │   ✅    │    ✅    │    ⚠️     │
│ Sales Manager    │  ✅*    │   ✅    │    ✅*   │    ⚠️*   │
│ Purchase Manager │  ✅*    │   ✅    │    ✅*   │    ⚠️*   │
│ Warehouse Mgr    │  ✅*    │   ✅    │    ✅*   │    ⚠️*   │
│ Accountant       │  ✅*    │   ✅    │    ✅*   │    ⚠️*   │
│ Sales Officer    │  ✅**   │   ✅    │    ✅    │     ❌   │
└──────────────────┴─────────┴─────────┴──────────┴──────────┘

Legend:
  ✅ = Full access
  ✅* = Limited to module
  ✅** = Limited to own records
  ⚠️ = Requires approval
  ❌ = No access
```

## Blade Implementation Examples

### Example 1: Simple Button Protection
```blade
<!-- Only show if user has permission -->
@can('product.create')
    <a href="{{ route('product.create') }}" class="btn btn-primary">
        Add Product
    </a>
@endcan
```

### Example 2: Multiple Permissions
```blade
<!-- Show if user has ANY of these permissions -->
@canany(['product.edit', 'product.delete'])
    <div class="actions">
        <!-- Action buttons here -->
    </div>
@endcanany
```

### Example 3: Entire Feature Hidden
```blade
<!-- Hide entire section if no permission -->
@can('warehouse.view')
    <div class="warehouse-section">
        <h2>Warehouse Management</h2>
        <!-- Warehouse content -->
    </div>
@endcan
```

### Example 4: With Fallback
```blade
<!-- Show one thing OR another -->
@can('product.delete')
    <button class="btn btn-danger">Delete</button>
@else
    <span class="text-muted">Cannot delete</span>
@endcan
```

## Menu Structure After Implementation

```
Admin Panel Navigation
│
├─ Dashboard
│  ├─ View (Always visible to authenticated users)
│
├─ Management (if user has product.view)
│  │
│  ├─ Products & Categories
│  │  ├─ Products (product.view)
│  │  ├─ Discount Products (product.discount.view)
│  │  ├─ Categories (category.view)
│  │  ├─ Sub Categories (subcategory.view)
│  │  ├─ Brands (brand.view)
│  │  └─ Units (unit.view)
│  │
│  ├─ Purchase & Inventory
│  │  ├─ Inward Gatepass (inward.gatepass.view)
│  │  ├─ Purchase (purchase.view)
│  │  └─ Vendor (vendor.view)
│  │
│  ├─ Warehouse & Stock
│  │  ├─ Warehouse (warehouse.view)
│  │  ├─ Warehouse Stock (warehouse.stock.view)
│  │  └─ Stock Transfer (stock.transfer.view)
│  │
│  └─ Sales & Customers
│     ├─ Sales (sale.view)
│     ├─ Customers (customer.view)
│     ├─ Sales Officers (sales.officer.view)
│     └─ Zones (zone.view)
│
├─ Vouchers (if user has voucher.view)
│  ├─ Chart of Accounts (chart.of.accounts.view)
│  ├─ Narrations (narration.view)
│  ├─ Receipts Voucher (receipts.voucher.view)
│  ├─ Payment Voucher (payment.voucher.view)
│  ├─ Expense Voucher (expense.voucher.view)
│  └─ Journal Voucher (journal.voucher.view)
│
├─ Reports (if user has report.item.stock.view)
│  ├─ Item Stock Report
│  ├─ Purchase Report
│  ├─ Sale Report
│  ├─ Customer Ledger
│  ├─ Assembly Report
│  └─ Inventory On-hand
│
└─ User Management (if user has user.view)
   ├─ Users (user.view)
   ├─ Roles (role.view)
   ├─ Permissions (permission.view)
   └─ Branches (branch.view)
```

## Permission Naming Convention

All permissions follow a consistent pattern:

```
RESOURCE . ACTION

Examples:
├─ product . view
├─ product . create
├─ product . edit
├─ product . delete
│
├─ customer . view
├─ customer . create
├─ customer . edit
├─ customer . ledger
├─ customer . toggle.status
│
├─ report . sale . view
├─ report . purchase . view
├─ report . customer.ledger . view
│
├─ warehouse.stock . create
├─ warehouse.stock . edit
├─ warehouse.stock . delete
│
└─ chart.of.accounts . view
  chart.of.accounts . create
  chart.of.accounts . delete
```

## Testing Checklist

```
□ Seeder executed successfully
□ Permissions visible in database
□ Super admin role created
□ All 150+ permissions assigned to super admin

□ Login as Super Admin
  □ All menu items visible
  □ All buttons visible
  □ Can access all pages

□ Login as Sales Manager
  □ Only Sales related items visible
  □ Product menu visible (limited)
  □ Customer menu visible
  □ Warehouse menu hidden
  □ Voucher menu hidden

□ Login as Purchase Manager
  □ Only Purchase related items visible
  □ Product menu visible (limited)
  □ Purchase menu visible
  □ Vendor menu visible
  □ Sales menu hidden

□ Login as Warehouse Manager
  □ Warehouse menu visible
  □ Stock management visible
  □ Reports (inventory only) visible
  □ Sales menu hidden

□ Login as Accountant
  □ Vouchers menu visible
  □ Reports menu fully visible
  □ Products menu hidden
  □ Can't create products

□ Login as Sales Officer
  □ Very limited menu
  □ Can only see Sales and Customers
  □ Cannot create/delete
  □ Cannot access admin features
```

## Quick Commands

```bash
# Run Seeder
php artisan db:seed --class=PermissionSeeder

# Clear Cache
php artisan cache:forget spatie.permission.cache

# Check Permissions in DB (Laravel Tinker)
php artisan tinker
> Permission::count()
> Permission::pluck('name')

# Find User Permissions
php artisan tinker
> $user = User::find(1)
> $user->getPermissionsViaRoles()
> $user->getAllPermissions()

# Give Permission to User
php artisan tinker
> $user = User::find(1)
> $user->givePermissionTo('product.view')

# Revoke Permission
php artisan tinker
> $user = User::find(1)
> $user->revokePermissionTo('product.view')
```

## File Locations Reference

```
Project Root
│
├─ database/
│  └─ seeders/
│     └─ PermissionSeeder.php .............. Permission definitions
│
├─ resources/
│  └─ views/
│     └─ admin_panel/
│        ├─ layout/
│        │  └─ app.blade.php ............. Main navigation
│        ├─ zone/
│        │  └─ index.blade.php ........... Zone list
│        ├─ warehouses/
│        │  ├─ index.blade.php ........... Warehouse list
│        │  ├─ warehouse_stocks/
│        │  │  └─ index.blade.php ........ Stock list
│        │  └─ stock_transfers/
│        │     └─ index.blade.php ........ Transfers list
│        └─ vochers/
│           ├─ all_recepit_vochers.blade.php
│           ├─ payment_vochers/
│           │  └─ all_payment_vochers.blade.php
│           └─ expense_vochers/
│              └─ all_expense_vochers.blade.php
│
└─ Documentation/
   ├─ PERMISSIONS_SETUP.md ................ Complete reference
   ├─ PERMISSION_SYSTEM_SETUP.md ......... Quick start
   ├─ IMPLEMENTATION_SUMMARY.md .......... What changed
   ├─ QUICK_START_CHECKLIST.md ........... This checklist
   └─ VISUAL_GUIDE.md .................... This file
```

## Success Indicators

✅ **System is working correctly when:**
- Navigation menu items appear/disappear based on login user
- Create buttons only visible to authorized users
- Edit/Delete buttons protected
- Attempting to access restricted URLs shows "Unauthorized" error
- Different roles see different admin panels
- Cache is updated without manual intervention

## Need Help?

1. **Permissions not showing?**
   - Run: `php artisan db:seed --class=PermissionSeeder`

2. **Menu still shows restricted items?**
   - Clear cache: `php artisan cache:clear`
   - Hard refresh browser: `Ctrl+Shift+Del`

3. **Can't assign permissions?**
   - Check user has 'role.permission.update' permission
   - Login with Super Admin account

4. **Permissions working in menu but page still accessible?**
   - Add controller authorization: `$this->authorize('permission.name')`
   - Blade @can is UI-only, controller check is required for security

---

**Last Updated:** January 26, 2025  
**Status:** ✅ Complete and Ready  
**Package:** Spatie/Laravel-Permission
