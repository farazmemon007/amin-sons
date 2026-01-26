# How to Use Permissions - Practical Examples

## Setup Prerequisites
```bash
# 1. Seed all permissions
php artisan db:seed --class=PermissionSeeder

# 2. This creates:
# - 150+ permissions in database
# - "super admin" role with all permissions
```

---

## Scenario 1: Creating a New Role with Limited Permissions

### Step 1: Create Role (Manual or Code)
```php
// Via Tinker or Code
$role = Role::create(['name' => 'sales_manager', 'guard_name' => 'web']);

// OR via Admin Panel -> User Management -> Roles -> Create
```

### Step 2: Assign Permissions to Role
```php
// Via Code
$role->givePermissionTo([
    'sale.view',
    'sale.create',
    'sale.edit',
    'sale.invoice',
    'customer.view',
    'customer.ledger',
    'product.view',
    'report.sale.view',
]);

// OR via Admin Panel -> Roles -> [Role Name] -> Select Permissions
```

### Step 3: Assign Role to User
```php
// Via Code
$user = User::find(1);
$user->assignRole('sales_manager');

// OR via Admin Panel -> Users -> [User] -> Assign Role
```

### Step 4: Test
- Login as this user
- They can view/create sales, view customers
- They CANNOT access purchase, vendor, warehouse features
- They CANNOT delete sales

---

## Scenario 2: Protecting a New Feature (Add Transport Module)

### Step 1: Create Model & Controller
```bash
php artisan make:model Transport -m -c
```

### Step 2: Add Permissions to Seeder
Edit `database/seeders/PermissionSeeder.php`:
```php
$permissions = [
    // ... existing permissions
    'transport.view',
    'transport.create',
    'transport.edit',
    'transport.delete',
    'transport.print',  // optional special action
];
```

### Step 3: Run Seeder
```bash
php artisan db:seed --class=PermissionSeeder
```

### Step 4: Protect Routes
In `routes/web.php`:
```php
Route::middleware('auth')->group(function () {
    // View all transports
    Route::get('/transport', [TransportController::class, 'index'])
        ->middleware('permission:transport.view')
        ->name('transport.index');
    
    // Create form
    Route::get('/transport/create', [TransportController::class, 'create'])
        ->middleware('permission:transport.create')
        ->name('transport.create');
    
    // Store (combined permission)
    Route::post('/transport', [TransportController::class, 'store'])
        ->middleware('permission:transport.create|transport.edit')
        ->name('transport.store');
    
    // Edit
    Route::get('/transport/{id}/edit', [TransportController::class, 'edit'])
        ->middleware('permission:transport.edit')
        ->name('transport.edit');
    
    // Update
    Route::put('/transport/{id}', [TransportController::class, 'update'])
        ->middleware('permission:transport.edit')
        ->name('transport.update');
    
    // Delete
    Route::delete('/transport/{id}', [TransportController::class, 'destroy'])
        ->middleware('permission:transport.delete')
        ->name('transport.destroy');
    
    // Print (optional)
    Route::get('/transport/{id}/print', [TransportController::class, 'print'])
        ->middleware('permission:transport.print')
        ->name('transport.print');
});
```

### Step 5: Add Controller Checks (Optional but Recommended)
In `app/Http/Controllers/TransportController.php`:
```php
class TransportController extends Controller
{
    public function index()
    {
        $this->authorize('view', 'transport.view');
        $transports = Transport::all();
        return view('admin_panel.transport.index', compact('transports'));
    }
    
    public function store(Request $request)
    {
        $this->authorize('view', 'transport.create');
        // ... creation logic
    }
}
```

### Step 6: Add Blade Protection (Optional but Recommended)
In `resources/views/admin_panel/transport/index.blade.php`:
```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Transports</h1>
    
    <!-- Create button -->
    @can('transport.create')
        <a href="{{ route('transport.create') }}" class="btn btn-primary">
            Add Transport
        </a>
    @endcan
    
    <!-- Table -->
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Route</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transports as $transport)
            <tr>
                <td>{{ $transport->name }}</td>
                <td>{{ $transport->route }}</td>
                <td>
                    @can('transport.edit')
                        <a href="{{ route('transport.edit', $transport) }}" class="btn btn-sm btn-warning">
                            Edit
                        </a>
                    @endcan
                    
                    @can('transport.delete')
                        <form method="POST" action="{{ route('transport.destroy', $transport) }}" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" 
                                onclick="return confirm('Are you sure?')">
                                Delete
                            </button>
                        </form>
                    @endcan
                    
                    @can('transport.print')
                        <a href="{{ route('transport.print', $transport) }}" class="btn btn-sm btn-info">
                            Print
                        </a>
                    @endcan
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
```

### Step 7: Create Role Assignment
Assign `transport.*` permissions to appropriate roles:
```php
$role = Role::where('name', 'operations_manager')->first();
$role->givePermissionTo(['transport.view', 'transport.create', 'transport.edit', 'transport.print']);
```

---

## Scenario 3: Checking Permissions in Code

### Check if User Has Permission
```php
// In controller
if (auth()->user()->hasPermissionTo('product.delete')) {
    // User can delete products
    $product->delete();
}
```

### Check Multiple Permissions (ANY)
```php
if (auth()->user()->hasAnyPermission(['product.delete', 'product.edit'])) {
    // User can delete OR edit products
}
```

### Check All Permissions
```php
if (auth()->user()->hasAllPermissions(['product.create', 'product.edit'])) {
    // User can both create AND edit
}
```

### Get All User Permissions
```php
$permissions = auth()->user()->getAllPermissions();
// Returns Collection of Permission objects

// To get permission names only:
$permissionNames = auth()->user()->getPermissionNames();
// Returns: ['product.view', 'product.create', ...]
```

### Check Role
```php
if (auth()->user()->hasRole('super admin')) {
    // This user is super admin
}
```

---

## Scenario 4: Permission Checks in Blade

### Simple Permission Check
```blade
@can('product.create')
    <!-- Show only if user has permission -->
    <button>Add Product</button>
@endcan
```

### Multiple Permissions (ANY)
```blade
@canany(['product.edit', 'product.delete'])
    <!-- Show if user has EITHER permission -->
    <div class="admin-actions">
        ...
    </div>
@endcanany
```

### Multiple Permissions (ALL)
```blade
@can('product.create')
    @can('product.edit')
        <!-- Show only if user has BOTH permissions -->
        ...
    @endcan
@endcan
```

### Permission with Fallback
```blade
@can('product.delete')
    <button class="btn btn-danger">Delete</button>
@else
    <button class="btn btn-danger" disabled>Delete (Not Allowed)</button>
@endcan
```

### Check Role in Blade
```blade
@role('super admin')
    <!-- Show only to super admin -->
    <a href="{{ route('admin.settings') }}">Settings</a>
@endrole
```

---

## Scenario 5: API Protection (JSON Endpoints)

### Protect API Routes
```php
Route::middleware(['api', 'auth:sanctum'])->group(function () {
    // Get products (JSON)
    Route::get('/api/products', [ProductController::class, 'apiIndex'])
        ->middleware('permission:product.view')
        ->name('api.products.index');
    
    // Create product (JSON)
    Route::post('/api/products', [ProductController::class, 'apiStore'])
        ->middleware('permission:product.create|product.edit')
        ->name('api.products.store');
});
```

### Return 403 on Permission Denied
```php
// Automatically returned by Laravel
Route::get('/api/products', function() {
    // If middleware denies, user gets:
    // HTTP 403 Forbidden
})
->middleware('permission:product.view');
```

---

## Scenario 6: Debugging Permission Issues

### Check User Permissions in Tinker
```bash
php artisan tinker
```

```php
// Get user
$user = User::find(1);

// Check specific permission
$user->hasPermissionTo('product.create')  // true or false

// Get all permissions
$user->getAllPermissions()

// Get permission names
$user->getPermissionNames()

// Check role
$user->hasRole('super admin')  // true or false

// Get all roles
$user->getRoleNames()

// Check if permission exists in system
Permission::where('name', 'product.create')->exists()

// Get all permissions in system
Permission::all()->pluck('name')
```

### Check Role Permissions
```php
$role = Role::where('name', 'sales_manager')->first();

// Get all permissions for this role
$role->permissions  // Collection of Permission objects

// Get permission names
$role->getPermissionNames()

// Check if role has permission
$role->hasPermissionTo('product.view')  // true or false
```

---

## Common Permission Patterns in This App

### CRUD Operations Pattern
```php
// View (Read)
Route::get('resource', [...])
    ->middleware('permission:resource.view');

// Create
Route::get('resource/create', [...])
    ->middleware('permission:resource.create');

// Store (Create/Edit combined)
Route::post('resource', [...])
    ->middleware('permission:resource.create|resource.edit');

// Edit
Route::get('resource/{id}/edit', [...])
    ->middleware('permission:resource.edit');

// Update
Route::put('resource/{id}', [...])
    ->middleware('permission:resource.edit');

// Delete
Route::delete('resource/{id}', [...])
    ->middleware('permission:resource.delete');
```

### Special Operations Pattern
```php
// For operations like print, invoice, export
Route::get('sale/{id}/invoice', [...])
    ->middleware('permission:sale.invoice');

Route::get('product/{id}/barcode', [...])
    ->middleware('permission:product.barcode');

Route::get('transport/{id}/print', [...])
    ->middleware('permission:transport.print');
```

---

## Important Notes

1. **Route Middleware is PRIMARY** - If user doesn't have permission at route level, they get 403 before reaching controller
2. **Controller Checks are DEFENSIVE** - Additional safety layer if middleware is bypassed
3. **Blade Directives are UI ONLY** - Don't protect API/direct access, just hide/show elements
4. **Permission Names are Case-Sensitive** - `product.create` NOT `product.Create`
5. **Super Admin Gets All** - After seeding, "super admin" role has all 150+ permissions
6. **Cache After Changes** - Run `php artisan permission:cache-reset` after permission modifications

---

## Quick Reference

| Task | Command |
|------|---------|
| Seed all permissions | `php artisan db:seed --class=PermissionSeeder` |
| Reset permission cache | `php artisan permission:cache-reset` |
| View all routes | `php artisan route:list` |
| Check user permissions | `php artisan tinker` then `User::find(1)->hasPermissionTo(...)` |
| Create role | Via Admin Panel or `Role::create([...])` |
| Assign permission to role | Via Admin Panel or `$role->givePermissionTo(...)` |
| Assign role to user | Via Admin Panel or `$user->assignRole(...)` |
