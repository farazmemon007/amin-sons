# Ameen & Sons ERP - AI Coding Agent Instructions

## 🏗️ Architecture Overview

This is a **Laravel 11** ERP system with **Spatie Permission** package for role-based access control (RBAC).

### Key Components:
- **Models** (`app/Models/`): 40+ models representing business entities (Product, Sale, Purchase, Customer, Vendor, etc.)
- **Controllers** (`app/Http/Controllers/`): CRUD operations for each module
- **Routes** (`routes/web.php`): All routes with permission middleware protection
- **Permissions** (`database/seeders/PermissionSeeder.php`): 150+ permissions organized by module
- **Blade Templates** (`resources/views/`): UI with `@can` directives for permission checks

### Permission System (Critical):
- **Guard**: `web` (default)
- **Package**: `spatie/laravel-permission`
- **Database Tables**: `permissions`, `roles`, `role_has_permissions`, `model_has_permissions`
- **Current Setup**: 150+ permissions pre-seeded, "super admin" role has all permissions

---

## 🔐 Permission Patterns

### 1. **Route-Level Protection** (Recommended)
Apply middleware directly to routes in `routes/web.php`:

```php
// Single permission check
Route::get('/products', [ProductController::class, 'index'])
    ->middleware('permission:product.view')
    ->name('products.index');

// Multiple permissions (OR logic)
Route::post('/products', [ProductController::class, 'store'])
    ->middleware('permission:product.create|product.edit')
    ->name('products.store');

// For entire route group
Route::prefix('admin')->middleware('permission:admin.access')->group(function () {
    Route::resource('users', UserController::class);
});
```

### 2. **Controller-Level Protection** (Defensive)
Add checks inside controller methods as safeguard:

```php
public function store(Request $request)
{
    // This throws 403 if user lacks permission
    $this->authorize('view', 'product.create');
    
    // Proceed with creation logic
    $product = Product::create($request->validated());
    return redirect()->back()->with('success', 'Product created');
}
```

### 3. **Blade Template Protection** (UI Layer)
Hide/show buttons based on permissions using `@can` directive:

```blade
@can('product.create')
    <button class="btn btn-primary">Add Product</button>
@endcan

<!-- Multiple permissions -->
@canany(['product.edit', 'product.delete'])
    <div class="actions">...</div>
@endcanany

<!-- With fallback -->
@can('product.delete')
    <form method="POST">...</form>
@else
    <p>Not authorized</p>
@endcan
```

---

## 📋 Permission Naming Convention

All permissions follow: `resource.action` format

### Common Resources:
- `product.*` - Product management (view, create, edit, delete, barcode, assembly)
- `purchase.*` - Purchase orders (view, create, edit, delete, invoice, return.*)
- `sale.*` - Sales transactions (view, create, edit, delete, invoice, delivery.challan, receipt, return.*)
- `customer.*` - Customer data (view, create, edit, delete, ledger, payments.*, toggle.status)
- `warehouse.*` - Warehouse management (view, create, edit, delete, stock.*)
- `stock.transfer.*` - Stock transfers
- `voucher.*` - Accounting vouchers (receipts, payment, expense, journal)
- `report.*` - Reports (item.stock, purchase, sale, customer.ledger, inventory.onhand)
- `zone.*`, `vendor.*`, `category.*`, `brand.*`, `unit.*`, etc.

### Actions:
- `view` - Read access
- `create` - Create new records
- `edit` - Update existing records
- `delete` - Delete records
- `invoice`, `barcode`, `print` - Special operations

---

## 🚀 Common Development Tasks

### Adding a New Permission
1. **Add to PermissionSeeder** (`database/seeders/PermissionSeeder.php`):
```php
$permissions = [
    // ... existing permissions
    'report.custom.view',
    'report.custom.export',
];
```

2. **Run seeder** (or migrate fresh):
```bash
php artisan db:seed --class=PermissionSeeder
```

3. **Apply to route/controller** - see patterns above

### Adding Route Protection
In `routes/web.php`, add `->middleware('permission:...')`:

```php
Route::resource('invoices', InvoiceController::class)
    ->middleware('permission:invoice.view');
```

**Key pattern used in this codebase:**
```php
// View routes (no special action)
Route::get('/items', [ItemController::class, 'index'])
    ->middleware('permission:item.view');

// Create/Edit routes (combined with OR)
Route::post('/items', [ItemController::class, 'store'])
    ->middleware('permission:item.create|item.edit');

// Delete routes
Route::delete('/items/{id}', [ItemController::class, 'destroy'])
    ->middleware('permission:item.delete');
```

### Creating a New Module
Example for "Transport" module:
1. **Create model** in `app/Models/Transport.php`
2. **Create controller** in `app/Http/Controllers/TransportController.php`
3. **Add permissions** to seeder:
   ```php
   'transport.view',
   'transport.create',
   'transport.edit',
   'transport.delete',
   ```
4. **Add routes** with middleware:
   ```php
   Route::resource('transport', TransportController::class)
       ->middleware([
           'index,show' => 'permission:transport.view',
           'create' => 'permission:transport.create',
           'store' => 'permission:transport.create|transport.edit',
           'update' => 'permission:transport.edit',
           'destroy' => 'permission:transport.delete',
       ]);
   ```
5. **Protect blade views** with `@can` directives

---

## 🛠️ Development Workflows

### Running Tests
```bash
php artisan test
php artisan test tests/Feature/PermissionTest.php
```

### Database Operations
```bash
# Fresh migration with seeders (includes permissions)
php artisan migrate:fresh --seed

# Only permission seeder
php artisan db:seed --class=PermissionSeeder

# Clear permission cache (after changes)
php artisan permission:cache-reset
```

### Debugging Permission Issues
Check user permissions in tinker:
```bash
php artisan tinker
> $user = User::find(1)
> $user->permissions
> $user->hasPermissionTo('product.create')
> $user->hasRole('super admin')
```

---

## ⚠️ Critical Implementation Rules

1. **Always use `permission:` middleware on routes**, not just blade checks
   - Blade is UI only - doesn't protect API/direct calls
   
2. **Permission names are case-sensitive** - use lowercase with dots
   - ✅ `product.create`
   - ❌ `Product.Create` or `productCreate`

3. **Test permission inheritance** - ensure roles are properly assigned:
   - Super admin should have all permissions
   - Other roles should have subset

4. **Cache permissions after bulk changes**:
   ```bash
   php artisan permission:cache-reset
   ```

5. **Don't hardcode permission checks** - use middleware/directives:
   - ❌ Bad: `if (auth()->user()->id === 1) { ... }`
   - ✅ Good: `->middleware('permission:admin.access')`

---

## 📦 Current Module Status

### ✅ Protected with Permissions:
- Product management (create, edit, delete, barcode, assembly)
- Purchase/Sales (all CRUD operations)
- Customer/Vendor management
- Warehouse & Stock operations
- All voucher types (receipts, payment, expense, journal)
- Reports (item stock, purchase, sale, customer ledger)
- User/Role/Permission management
- Zone, Sales Officer, Category, Brand, Unit management

### Routes Updated:
- 200+ routes now have permission middleware
- All CRUD operations protected
- Reporting routes protected
- Admin routes protected

### Controllers Updated:
- CategoryController: Added defensive `$this->authorize()` checks
- CustomerController: Added permission checks in index method
- Pattern: Add `$this->authorize('view', 'resource.action')` in each method as safeguard

---

## 🔗 Key Files Reference

| File | Purpose |
|------|---------|
| `database/seeders/PermissionSeeder.php` | Define all 150+ permissions |
| `routes/web.php` | Apply permission middleware to routes |
| `app/Http/Controllers/*.php` | Optional: defensive permission checks |
| `resources/views/**/*.blade.php` | Show/hide UI elements with `@can` |
| `app/Models/User.php` | User model (uses HasRoles trait) |
| `config/permission.php` | Spatie Permission configuration |

---

## 💡 Best Practices for This Codebase

1. **Always follow the pattern**: Route middleware → Controller checks → Blade directives
2. **For new features**: Add permissions to seeder FIRST, then apply to routes
3. **Test permission changes**: Use `php artisan db:seed --class=PermissionSeeder` to reset
4. **Keep permissions granular**: e.g., `sale.create` and `sale.edit` separate for fine control
5. **Use resource routes with middleware** for consistency:
   ```php
   Route::resource('items', ItemController::class)
       ->middleware('permission:item.view');
   ```

---

## 📞 Quick Reference

**Need to restrict a feature?**
1. Check if permission exists in PermissionSeeder
2. If not, add it and run seeder
3. Add `->middleware('permission:xxx')` to route
4. Add `@can('xxx')` to blade template
5. Optionally add `$this->authorize('view', 'xxx')` in controller

**Need to check permissions in code?**
```php
// Check if user has permission
auth()->user()->hasPermissionTo('product.create')

// Check if user has role
auth()->user()->hasRole('super admin')

// Get all permissions
auth()->user()->permissions

// Assign permission to role
$role->givePermissionTo('product.create')
```

**Need to debug?**
```php
// In controller or tinker
dd(auth()->user()->getAllPermissions());
dd(auth()->user()->roles);
```
