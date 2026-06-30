<?php

use App\Http\Controllers\AccountsHeadController;
use App\Http\Controllers\AssemblyController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InwardgatepassController;
use App\Http\Controllers\NarrationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PackageTypeController;
use App\Http\Controllers\PakageTypeController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductBookingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportingController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SalesOfficerController;
use App\Http\Controllers\StocksController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\TypeController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarehouseStockController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\OutwardGatepassController;
use App\Http\Controllers\CustomerRemainingController;
use App\Http\Controllers\VendorRemainingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BranchWarehouseController;
use App\Http\Controllers\StockRequestController;
use App\Http\Controllers\BranchLedgerController;
use App\Http\Controllers\VoucherInterBranchController;
use App\Http\Controllers\FindController;
use App\Http\Controllers\ComplaintController;





/*
    |--------------------------------------------------------------------------
    | Web Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register web routes for your application. These
    | routes are loaded by the RouteServiceProvider and all of them will
    | be assigned to the "web" middleware group. Make something great!
    |
    */


    // Route::get('/', function () {
    //         echo "faraz memon";
    //     });

        // Route::get('/dashboard', function () {
            //     return view('dashboard');
            // })->middleware(['auth', 'verified'])->name('dashboard');

            Route::middleware('auth')->group(function () {

                Route::get('/', [HomeController::class, 'index'])->name('home');

    // ─── Complaint Management System ──────────────────────────────
    Route::prefix('complaints')->name('complaints.')->group(function () {
        Route::get('/',               [ComplaintController::class, 'index'])->middleware('permission:complaint.view')->name('index');
        Route::get('/create',         [ComplaintController::class, 'create'])->middleware('permission:complaint.create')->name('create');
        Route::post('/store',         [ComplaintController::class, 'store'])->middleware('permission:complaint.create')->name('store');
        Route::get('/{id}',           [ComplaintController::class, 'show'])->middleware('permission:complaint.view')->name('show');
        Route::get('/{id}/edit',      [ComplaintController::class, 'edit'])->middleware('permission:complaint.edit')->name('edit');
        Route::put('/{id}',           [ComplaintController::class, 'update'])->middleware('permission:complaint.edit')->name('update');
        Route::delete('/{id}',        [ComplaintController::class, 'destroy'])->middleware('permission:complaint.delete')->name('destroy');
        Route::get('/{id}/print-slip',[ComplaintController::class, 'printSlip'])->middleware('permission:complaint.print')->name('print-slip');
        Route::get('/{id}/print-tag', [ComplaintController::class, 'printTag'])->middleware('permission:complaint.print')->name('print-tag');
        Route::post('/{id}/status',   [ComplaintController::class, 'changeStatus'])->middleware('permission:complaint.edit')->name('change-status');
        Route::get('/{id}/whatsapp',  [ComplaintController::class, 'whatsappShare'])->middleware('permission:complaint.view')->name('whatsapp-share');
        // Home Service
        Route::post('/{id}/home-service',        [ComplaintController::class, 'storeHomeService'])->middleware('permission:complaint.home_service')->name('home-service.store');
        Route::post('/home-service/{id}/update', [ComplaintController::class, 'updateHomeService'])->middleware('permission:complaint.home_service')->name('home-service.update');
        // AJAX
        Route::get('/search/customers',          [ComplaintController::class, 'searchCustomers'])->middleware('permission:complaint.view')->name('search-customers');
    });
    // ─── End Complaint Management System ─────────────────────────

    // ✅ Find Document
    Route::get('/find', [FindController::class, 'index'])->name('find.index');
    Route::get('/find/search', [FindController::class, 'search'])->name('find.search');


    Route::post('type/store', [TypeController::class,'store'])->name('store.type');
    Route::get('type/select', [TypeController::class,'select'])->name('select.type');
    Route::post('type/Delete', [TypeController::class,'delete'])->name('delete.type');
    Route::post('type/update', [TypeController::class,'update'])->name('update.type');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    route::get('/category', [CategoryController::class, 'index'])->middleware('permission:category.view')->name('Category.home');
    Route::get('/category/delete/{id}', [CategoryController::class, 'delete'])->middleware('permission:category.delete')->name('delete.category');
    route::post('/category/stote', [CategoryController::class, 'store'])->middleware('permission:category.create|category.edit')->name('store.category');
    route::post('/category/catagorystore', [CategoryController::class, 'catagorystore'])->middleware('permission:category.create|category.edit')->name('store.categorybypage');

    route::get('/Brand', [BrandController::class, 'index'])->middleware('permission:brand.view')->name('Brand.home');
    Route::get('/Brand/delete/{id}', [BrandController::class, 'delete'])->middleware('permission:brand.delete')->name('delete.Brand');
    route::post('/Brand/stote', [BrandController::class, 'store'])->middleware('permission:brand.create|brand.edit')->name('store.Brand');

    route::get('/Unit', [UnitController::class, 'index'])->middleware('permission:unit.view')->name('Unit.home');
    Route::get('/Unit/delete/{id}', [UnitController::class, 'delete'])->middleware('permission:unit.delete')->name('delete.Unit');
    route::post('/Unit/stote', [UnitController::class, 'store'])->middleware('permission:unit.create|unit.edit')->name('store.Unit');
    Route::get('/get-units', [UnitController::class, 'getUnits'])->name('get-units');

    route::get('/subcategory', [SubcategoryController::class, 'index'])->middleware('permission:subcategory.view')->name('subcategory.home');
    Route::get('/subcategory/delete/{id}', [SubcategoryController::class, 'delete'])->middleware('permission:subcategory.delete')->name('delete.subcategory');
    route::post('/subcategory/stote', [SubcategoryController::class, 'store'])->middleware('permission:subcategory.create|subcategory.edit')->name('store.subcategory');

    Route::post('/assembly/pluck-part', [AssemblyController::class, 'pluckPart'])->name('assembly.pluck.part');
    Route::post('/assembly/repair-incomplete', [AssemblyController::class, 'repairIncomplete'])->name('assembly.repair.incomplete');
    Route::post('/assembly/build-auto', [AssemblyController::class, 'buildAuto'])->name('assembly.build.auto');
    Route::get('/products/{id}/assembly-report', [ProductController::class, 'assemblyReport'])->name('products.assembly-report');
    Route::get('/assembly/summary', [ProductController::class, 'assemblySummary'])->name('assembly.summary');

    Route::post('/assembly/ensure-part-for-sale', [AssemblyController::class, 'ensurePartForSale'])->name('assembly.ensure_part_for_sale');
    Route::get('productget', [ProductController::class, 'productget'])->name('productget');

    Route::get('/Product', [ProductController::class, 'product'])->middleware('permission:product.view')->name('product');
    Route::get('/productview/{id}', [ProductController::class, 'productview'])->middleware('permission:product.view')->name('productview');
    ////////////
    Route::get('/products/price', [ProductController::class, 'getPrice'])
        ->name('products.price');
//////

///////////////////////////////////////////////////////////////////////////////
    //////////
Route::get('/search_products', [ProductController::class, 'searchProducts'])
     ->middleware('permission:product.view')->name('products_search');
    Route::get('/search-products-sale', [ProductController::class, 'searchProductsForSalebypagination'])->middleware('permission:product.view')->name('search-products-sale');
    Route::get('/products/warehouses', [ProductController::class, 'warehouses'])->middleware('permission:product.view')->name('products.warehouses');
    Route::get('/stock-locations', [ProductController::class, 'stockLocations'])->middleware('permission:warehouse.stock.view')->name('stock.locations');
    Route::get('/stock-locations/data', [ProductController::class, 'stockLocationsData'])->middleware('permission:warehouse.stock.view')->name('stock.locations.data');
Route::get('/check-product-name', [ProductController::class, 'checkProductName'])->name('check-product-name');
//////////////////////////////////////////////////////////////////////////////////////////////
    //////////
    Route::get('/create_prodcut', [ProductController::class, 'view_store'])->middleware('permission:product.create')->name('store');
    Route::post('/store-product', [ProductController::class, 'store_product'])->middleware('permission:product.create|product.edit')->name('store-product');
//  Route::post('/store-product', function (Request $request) {
//     echo "<pre>";
//     print_r($request->all());
// })->name('store-product');

    Route::put('/product/update/{id}', [ProductController::class, 'update'])->middleware('permission:product.edit')->name('product.update');
    Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->middleware('permission:product.edit')->name('products.edit');
    Route::get('/generate-barcode-image', [ProductController::class, 'generateBarcode'])->middleware('permission:product.barcode')->name('generate-barcode-image');
    
    // ✅ Phase 2 Routes: Opening Stock Configuration (legacy)
    Route::get('/products/opening-stock/{product_id}', [ProductController::class, 'createOpeningStock'])->middleware('permission:product.edit')->name('product.opening-stock.create');
    Route::get('/products/incomplete', [ProductController::class, 'incompleteProducts'])->middleware('permission:product.view')->name('product.incomplete');

    // ✅ NEW: Unified Opening Stock Manager (ERP Standard)
    Route::get('/opening-stocks', [ProductController::class, 'openingStockManager'])->middleware('permission:product.edit')->name('opening.stocks.index');
    Route::post('/opening-stocks/store', [ProductController::class, 'storeOpeningStocks'])->middleware('permission:product.edit')->name('opening.stocks.store');
    Route::get('/opening-stocks/search-products', [ProductController::class, 'searchProductsForStock'])->middleware('permission:product.view')->name('opening.stocks.search');
    Route::get('/opening-stocks/{id}/edit', [ProductController::class, 'editOpeningStock'])->middleware('permission:product.edit')->name('opening.stocks.edit');
    Route::post('/opening-stocks/{id}/update', [ProductController::class, 'updateOpeningStock'])->middleware('permission:product.edit')->name('opening.stocks.update');
    Route::get('/opening-stocks/warehouses-by-branch', [ProductController::class, 'getWarehousesForBranch'])->middleware('permission:product.view')->name('opening.stocks.warehouses');
    Route::get('/opening-stocks/stock-breakdown', [ProductController::class, 'getProductStockBreakdown'])->middleware('permission:product.view')->name('opening.stocks.breakdown');

    // Route::get('/barcode/{id}', [ProductController::class, 'barcode'])->name('p  roduct.barcode');
    // Searches
    Route::get('/generate-barcode-image', [ProductController::class, 'generateBarcode'])->middleware('permission:product.barcode')->name('generate-barcode-image');
    Route::get('/get-subcategories/{category_id}', [ProductController::class, 'getSubcategories'])->name('fetch-subcategories');

    Route::get('/search-part-name', [ProductController::class, 'searchPartName'])->name('search-part-name');

    Route::prefix('discount')->middleware('permission:product.discount.view')->group(function () {
        Route::get('/', [DiscountController::class, 'index'])->name('discount.index');
        Route::get('/create', [DiscountController::class, 'create'])->middleware('permission:product.discount.create')->name('discount.create');
        Route::post('/store', [DiscountController::class, 'store'])->middleware('permission:product.discount.create|product.discount.edit')->name('discount.store');
        Route::post('/toggle-status/{id}', [DiscountController::class, 'toggleStatus'])->middleware('permission:product.discount.edit')->name('discount.toggleStatus');
        Route::get('/barcode/{id}', [DiscountController::class, 'barcode'])->middleware('permission:product.discount.barcode')->name('discount.barcode');
    });

    Route::get('/parts-adjust', [AssemblyController::class, 'adjustForm'])
        ->middleware('permission:stock.adjust')->name('stock.adjust.form');

    Route::post('/stock-adjust/bulk', [AssemblyController::class, 'adjustBulk'])
        ->middleware('permission:stock.adjust')->name('assembly.adjust.bulk');

    // package type controller


    // Route::get('/package-types', [PakageTypeController::class, 'index'])
    //     ->name('package-type.index');

    // Route::post('/package-type/store', [PackageTypeController::class, 'store'])
    //     ->name('package-type.store');

    // Route::post('/package-type/update', [PackageTypeController::class, 'update'])
    //     ->name('package-type.update');

    // Route::get('/package-type/delete/{id}', [PackageTypeController::class, 'destroy'])
    //     ->name('package-type.delete');





    // Assembly Routes
    Route::get('/assembly-report', [AssemblyController::class, 'index'])->middleware('permission:product.assembly')->name('assembly.report');
    Route::get('/assembly-report/{product}', [AssemblyController::class, 'show'])->middleware('permission:product.assembly')->name('assembly.report.show');
    Route::post('/assembly/build', [AssemblyController::class, 'build'])->middleware('permission:product.assembly')->name('assembly.build');

    // routes/web.php

    // Customer Routes
    // Dropdown list (by type)
    Route::get('sale/customers', [CustomerController::class, 'saleindex'])
        ->middleware('permission:customer.view')->name('salecustomers.index');

    // Single customer detail
    Route::get('sale/customers/{id}', [CustomerController::class, 'show'])
        ->middleware('permission:customer.view')->name('salecustomers.show');
    Route::get('/get-customer/{id}', [SaleController::class, 'getCustomerData'])->middleware('permission:customer.view')->name('customers.show');
    // Cutomer create
    Route::get('/customers', [CustomerController::class, 'index'])->middleware('permission:customer.view')->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->middleware('permission:customer.create')->name('customers.create');
    Route::get('/get-next-customer-id', [CustomerController::class, 'getNextCustomerId'])->name('customers.nextId');
    Route::post('/customers/store', [CustomerController::class, 'store'])->middleware('permission:customer.create|customer.edit')->name('customers.store');
    Route::get('/customers/edit/{id}', [CustomerController::class, 'edit'])->middleware('permission:customer.edit')->name('customers.edit');
    Route::post('/customers/update/{id}', [CustomerController::class, 'update'])->middleware('permission:customer.edit')->name('customers.update');
    Route::get('/customers/delete/{id}', [CustomerController::class, 'destroy'])->middleware('permission:customer.delete')->name('customers.destroy');

    // New
    Route::get('/customers/inactive', [CustomerController::class, 'inactiveCustomers'])->middleware('permission:customer.view')->name('customers.inactive');
    Route::get('/customers/inactive/{id}', [CustomerController::class, 'markInactive'])->middleware('permission:customer.edit')->name('customers.markInactive');
    Route::get('customers/toggle-status/{id}', [CustomerController::class, 'toggleStatus'])->middleware('permission:customer.toggle.status')->name('customers.toggleStatus');
    Route::get('/customers/ledger', [CustomerController::class, 'customer_ledger'])->middleware('permission:customer.ledger')->name('customers.ledger');
    Route::get('/customer/payments', [CustomerController::class, 'customer_payments'])->middleware('permission:customer.payments.view')->name('customer.payments');
    Route::post('/customer/payments', [CustomerController::class, 'store_customer_payment'])->middleware('permission:customer.payments.create')->name('customer.payments.store');
    // web.php
    Route::get('/customer/ledger/{id}', [CustomerController::class, 'getCustomerLedger'])->middleware('permission:customer.ledger');
    Route::delete('/customer-payments/{id}', [CustomerController::class, 'destroy_payment'])->middleware('permission:customer.payments.delete')->name('customer.payments.destroy');

    // Vendor Routes
    // ✅ Permission restrictions removed - all vendor routes now accessible
    // Route::get('/vendor', [VendorController::class, 'index']);
    Route::get('/vendorlist', [VendorController::class, 'index'])->middleware('permission:vendor.view')->name('vendor.index');
    Route::post('/vendor/store', [VendorController::class, 'store'])->name('vendors.store.ajax');
    Route::get('/vendor/delete/{id}', [VendorController::class, 'delete']);
    Route::get('/vendors/ledger', [VendorController::class, 'vendors_ledger'])->middleware('permission:vendor.ledger')->name('vendors.ledger');
    Route::get('/vendors-ledger', [\App\Http\Controllers\ReportingController::class, 'vendor_ledger_new'])->middleware('permission:report.vendor.ledger.view')->name('vendors-ledger');
    Route::get('/vendor/payments', [VendorController::class, 'vendor_payments'])->name('vendor.payments');
    Route::post('/vendor/payments', [VendorController::class, 'store_vendor_payment'])->name('vendor.payments.store');
    Route::get('/vendor/bilties', [VendorController::class, 'vendor_bilties'])->name('vendor.bilties');
    Route::post('/vendor/bilties', [VendorController::class, 'store_vendor_bilty'])->name('vendor.bilties.store');

    // Warehouse Routes
    /////
    Route::get('/warehouses/get/', [WarehouseController::class, 'getWarehouses'])->middleware('permission:warehouse.view')->name('warehouses.get');

    /////
    Route::get('/warehouse', [WarehouseController::class, 'index'])->middleware('permission:warehouse.view');
    Route::post('/warehouse/store', [WarehouseController::class, 'store'])->middleware('permission:warehouse.create|warehouse.edit');
    Route::get('/warehouse/delete/{id}', [WarehouseController::class, 'delete'])->middleware('permission:warehouse.delete');
    // ✅ ERP: Role-Based Warehouse Staff Assignment
    Route::post('/warehouse/assign-users', [WarehouseController::class, 'assignUsers'])->middleware('permission:warehouse.manage')->name('warehouse.assign.users');
    Route::get('/warehouse/{id}/users', [WarehouseController::class, 'getWarehouseUsers'])->middleware('permission:warehouse.view')->name('warehouse.get.users');
    // Branch <-> Warehouse mapping (admin)
    Route::get('/admin/branch-warehouse', [BranchWarehouseController::class, 'index'])->name('branch.warehouse.index')->middleware('permission:warehouse.manage');
    Route::put('/admin/branch-warehouse/{branch}', [BranchWarehouseController::class, 'update'])->name('branch.warehouse.update')->middleware('permission:warehouse.manage');
    Route::get('/admin/branch-warehouse/check-products/{branchId}/{warehouseId}', [BranchWarehouseController::class, 'getWarehouseProducts'])->middleware('permission:warehouse.manage');

    // Branches
    // index (view) requires branch.view
    Route::get('/branch', [BranchController::class, 'index'])->middleware('permission:branch.view')->name('branch.index');
    // store (create/update) requires create or edit permission
    Route::post('/branch', [BranchController::class, 'store'])->middleware('permission:branch.create|branch.edit')->name('branch.store');
    Route::get('/branch/delete/{id}', [BranchController::class, 'delete'])->middleware('permission:branch.delete')->name('branch.delete');




    // Route::middleware(['role:super admin|admin|manager'])->group(function () {
    // Roles
    Route::resource('roles', RoleController::class)->names('roles')->only(['index', 'store']);
    Route::get('/roles/delete/{id}', [RoleController::class, 'delete'])->name('roles.delete')->middleware('permission:delete role');
    Route::post('/admin/roles/update-permission', [RoleController::class, 'updatePermissions'])->name('roles.update.permission');

    // Permissions
    Route::resource('permissions', PermissionController::class)->names('permissions')->only(['index', 'store']);
    Route::get('/permissions/modules', [PermissionController::class, 'modulesList'])->name('modules.list');
    Route::get('/permissions/delete/{id}', [PermissionController::class, 'delete'])->name('permission.delete')->middleware('permission:delete role');;

    // Users
    Route::resource('users', UserController::class)->names('users')->only(['index', 'store']);
    Route::get('/users/delete/{id}', [UserController::class, 'delete'])->name('users.delete')->middleware('permission:delete role');;
    Route::post('/admin/users/update-roles', [UserController::class, 'updateRoles'])->name('users.update.roles');
    // ✅ ERP: User-Centric Warehouse Assignment (Super Admin assigns multiple warehouses to a user)
    Route::get('/users/{id}/warehouse-assignments', [UserController::class, 'getUserWarehouseAssignments'])->name('users.warehouse.assignments')->middleware('permission:warehouse.manage');
    Route::post('/users/assign-warehouses', [UserController::class, 'assignUserWarehouses'])->name('users.assign.warehouses')->middleware('permission:warehouse.manage');
    });
    // Route::put('/users/{id}/roles', [UserController::class, 'updateRoles'])->name('users.update.roles');

    // Zone
    Route::get('zone', [ZoneController::class, 'index'])->middleware('permission:zone.view')->name('zone.index');
    Route::post('zones/store', [ZoneController::class, 'store'])->middleware('permission:zone.create|zone.edit')->name('zone.store');
    Route::get('zones/edit/{id}', [ZoneController::class, 'edit'])->middleware('permission:zone.edit')->name('zone.edit');
    Route::get('zones/delete/{id}', [ZoneController::class, 'destroy'])->middleware('permission:zone.delete')->name('zone.delete');

    // Sales Officer
    Route::get('sales-officers', [SalesOfficerController::class, 'index'])->middleware('permission:sales.officer.view')->name('sales.officer.index');
    Route::post('sales-officers/store', [SalesOfficerController::class, 'store'])->middleware('permission:sales.officer.create|sales.officer.edit')->name('sales-officer.store');
    Route::get('sales-officers/edit/{id}', [SalesOfficerController::class, 'edit'])->middleware('permission:sales.officer.edit')->name('sales.officer.edit');
    Route::delete('sales-officers/{id}', [SalesOfficerController::class, 'destroy'])->middleware('permission:sales.officer.delete')->name('sales-officer.delete');

    // products

    route::get('/Purchase', [PurchaseController::class, 'index'])->middleware('permission:purchase.view')->name('Purchase.home');
    route::get('/Purchase/{id}/pending', [PurchaseController::class, 'showPending'])->middleware('permission:purchase.view')->name('purchase.pending');
    route::get('/add/Purchase', [PurchaseController::class, 'add_purchase'])->middleware('permission:purchase.create')->name('add_purchase');
    
    // ✅ NEW: Local Purchase (Direct inventory add)
    Route::get('/add/LocalPurchase', [PurchaseController::class, 'addLocalPurchase'])->middleware('permission:purchase.create')->name('purchase.addLocal');
    Route::post('/store/LocalPurchase', [PurchaseController::class, 'storeLocalPurchase'])->middleware('permission:purchase.create')->name('purchase.storeLocal');

    route::post('/Purchase/stote', [PurchaseController::class, 'store'])->middleware('permission:purchase.create|purchase.edit')->name('store.Purchase');
    Route::get('/purchase/{id}/edit', [PurchaseController::class, 'edit'])->middleware('permission:purchase.edit')->name('purchase.edit');
    Route::put('/purchase/{id}', [PurchaseController::class, 'update'])->middleware('permission:purchase.edit')->name('purchase.update');
    Route::delete('/purchase/{id}', [PurchaseController::class, 'destroy'])->middleware('permission:purchase.delete')->name('purchase.destroy');
    Route::post('/search_products', [ProductController::class, 'searchProducts'])->middleware('permission:product.view')->name('search_products');
    Route::get('/purchase/{id}/invoice', [PurchaseController::class, 'Invoice'])->middleware('permission:purchase.invoice')->name('purchase.invoice');

    Route::get('purchase/return', [PurchaseController::class, 'purchaseReturnIndex'])->middleware('permission:purchase.return.view')->name('purchase.return.index');
    Route::get('purchase/return/{id}', [PurchaseController::class, 'showReturnForm'])->middleware('permission:purchase.return.view')->name('purchase.return.show');
    Route::post('purchase/return/store', [PurchaseController::class, 'storeReturn'])->middleware('permission:purchase.return.create|purchase.return.edit')->name('purchase.return.store');
    Route::get('/getPartyList', [PurchaseController::class, 'getPartyList'])->name('party.list');

    // Purchase Order (PO) Routes
    Route::prefix('purchase-orders')->group(function () {
        Route::get('/', [App\Http\Controllers\PurchaseOrderController::class, 'index'])->middleware('permission:purchase.order.view')->name('purchase_orders.index');
        Route::get('/create', [App\Http\Controllers\PurchaseOrderController::class, 'create'])->middleware('permission:purchase.order.create')->name('purchase_orders.create');
        Route::post('/store', [App\Http\Controllers\PurchaseOrderController::class, 'store'])->middleware('permission:purchase.order.create')->name('purchase_orders.store');
        Route::get('/{id}', [App\Http\Controllers\PurchaseOrderController::class, 'show'])->middleware('permission:purchase.order.view')->name('purchase_orders.show');
        Route::get('/{id}/print', [App\Http\Controllers\PurchaseOrderController::class, 'print'])->middleware('permission:purchase.order.view')->name('purchase_orders.print');
        Route::get('/{id}/edit', [App\Http\Controllers\PurchaseOrderController::class, 'edit'])->middleware('permission:purchase.order.edit')->name('purchase_orders.edit');
        Route::put('/{id}', [App\Http\Controllers\PurchaseOrderController::class, 'update'])->middleware('permission:purchase.order.edit')->name('purchase_orders.update');
        Route::delete('/{id}', [App\Http\Controllers\PurchaseOrderController::class, 'destroy'])->middleware('permission:purchase.order.delete')->name('purchase_orders.destroy');
        
        // AJAX Helpers
        Route::get('/branch/{branchId}/next-po', [App\Http\Controllers\PurchaseOrderController::class, 'getNextPONumber'])->name('purchase_orders.next_po');
        Route::get('/branch/{branchId}/vendors', [App\Http\Controllers\VendorController::class, 'getVendorsByBranch'])->name('vendors.by_branch');
    });

    // AJAX Helpers (Accessible by authorized users)
    Route::group(['middleware' => ['auth']], function () {
        Route::get('/purchase-orders/search-by-number', [App\Http\Controllers\PurchaseOrderController::class, 'searchByNumber'])->name('purchase_orders.search-by-number');
    });

    // Inward Gatepass Routes
    Route::get('/InwardGatepass', [InwardgatepassController::class, 'index'])->middleware('permission:inward.gatepass.view')->name('InwardGatepass.home');
    Route::get('/add/InwardGatepass', [InwardgatepassController::class, 'create'])->middleware('permission:inward.gatepass.create')->name('add_inwardgatepass');
    Route::get('/inward-gatepass/from-purchase/{purchaseId}', [InwardgatepassController::class, 'createFromPurchase'])->middleware('permission:inward.gatepass.create')->name('inward-gatepass.from-purchase');
    Route::get('/inward-gatepass/from-po/{poId}', [InwardgatepassController::class, 'createFromPO'])->middleware('permission:inward.gatepass.create')->name('inward-gatepass.from-po');
    Route::post('/InwardGatepass/store', [InwardgatepassController::class, 'store'])->middleware('permission:inward.gatepass.create|inward.gatepass.edit')->name('store.InwardGatepass');
    Route::get('/InwardGatepass/{id}', [InwardgatepassController::class, 'show'])->middleware('permission:inward.gatepass.view')->name('InwardGatepass.show');

    // edit/update/delete abhi comment kiye hue hain
    Route::get('/InwardGatepass/{id}/edit', [InwardgatepassController::class, 'edit'])->middleware('permission:inward.gatepass.edit')->name('InwardGatepass.edit');
    Route::put('/InwardGatepass/{id}', [InwardgatepassController::class, 'update'])->middleware('permission:inward.gatepass.edit')->name('InwardGatepass.update');
    Route::get('/inward-gatepass/{id}/pdf', [InwardgatepassController::class, 'pdf'])->middleware('permission:inward.gatepass.view')->name('InwardGatepass.pdf');
    Route::get('/inward-gatepass/{id}/thermal', [InwardgatepassController::class, 'thermal'])->middleware('permission:inward.gatepass.view')->name('InwardGatepass.thermal');

    Route::delete('/InwardGatepass/{id}', [InwardgatepassController::class, 'destroy'])->middleware('permission:inward.gatepass.delete')->name('InwardGatepass.destroy');
    // Products search
    Route::get('/search-products', [InwardgatepassController::class, 'searchProducts'])->middleware('permission:product.view')->name('search-products');
    Route::get('/get-warehouse-stock', [OutwardGatepassController::class, 'getWarehouseStock'])->middleware('permission:warehouse.view')->name('get-warehouse-stock');

    // Show Add Bill Form
    Route::get('inward-gatepass/{id}/add-bill', [PurchaseController::class, 'addBill'])->middleware('permission:purchase.create')->name('add_bill');
    // Store Bill
    Route::post('inward-gatepass/{id}/store-bill', [PurchaseController::class, 'store'])->middleware('permission:purchase.create|purchase.edit')->name('store.bill');

    // Vendor Remaining (Partial Deliveries) Routes
    Route::get('/vendor-remaining', [VendorRemainingController::class, 'index'])->middleware('permission:purchase.view')->name('vendor-remaining.index');
    Route::get('/vendor-remaining/{id}', [VendorRemainingController::class, 'show'])->middleware('permission:purchase.view')->name('vendor-remaining.show');
    Route::post('/vendor-remaining/{id}/mark-completed', [VendorRemainingController::class, 'markCompleted'])->middleware('permission:purchase.edit')->name('vendor-remaining.mark-completed');
    Route::delete('/vendor-remaining/{id}', [VendorRemainingController::class, 'delete'])->middleware('permission:purchase.delete')->name('vendor-remaining.delete');
    Route::get('/vendor-remaining/{id}/create-gatepass', [VendorRemainingController::class, 'createGatepass'])->middleware('permission:inward.gatepass.create')->name('vendor-remaining.create-gatepass');
    Route::get('/pending-deliveries/vendor/{vendorId}', [VendorRemainingController::class, 'getPendingForVendor'])->middleware('permission:purchase.view')->name('vendor.pending-deliveries');
    Route::get('/pending-deliveries/purchase/{purchaseId}', [VendorRemainingController::class, 'getPendingForPurchase'])->middleware('permission:purchase.view')->name('purchase.pending-deliveries');
    // Purchase Return Routes

    // Outward Gatepass Routes
    Route::get('/OutwardGatepass', [OutwardGatepassController::class, 'index'])->middleware('permission:outward.gatepass.view')->name('OutwardGatepass.home');
    Route::get('/OutwardGatepass/list', [OutwardGatepassController::class, 'listGatepasses'])->middleware('permission:outward.gatepass.view')->name('OutwardGatepass.list');
    Route::get('/OutwardGatepass/select-dc', [OutwardGatepassController::class, 'selectDC'])->middleware('permission:outward.gatepass.create')->name('OutwardGatepass.selectDC');
    Route::get('/add/OutwardGatepass/{orderId}', [OutwardGatepassController::class, 'create'])->middleware('permission:outward.gatepass.create')->name('outward_gatepass.create');
    Route::post('/OutwardGatepass/store', [OutwardGatepassController::class, 'store'])->middleware('permission:outward.gatepass.create|outward.gatepass.edit')->name('store.OutwardGatepass');
    Route::get('/OutwardGatepass/{id}', [OutwardGatepassController::class, 'show'])->middleware('permission:outward.gatepass.view')->name('OutwardGatepass.show');
    Route::get('/outward-gatepass/{id}/pdf', [OutwardGatepassController::class, 'pdf'])->middleware('permission:outward.gatepass.view')->name('OutwardGatepass.pdf');
    Route::get('/outward-gatepass/{id}/delivery-receipt', [OutwardGatepassController::class, 'getDeliveryReceipt'])->middleware('permission:outward.gatepass.view')->name('OutwardGatepass.deliveryReceipt');
    Route::get('/outward-gatepass/{id}/receipt-file', [OutwardGatepassController::class, 'getReceiptFile'])->middleware('permission:outward.gatepass.view')->name('OutwardGatepass.receiptFile');
    Route::post('/outward-gatepass/{id}/transport-receipt', [OutwardGatepassController::class, 'uploadTransportReceipt'])->middleware('permission:outward.gatepass.edit')->name('OutwardGatepass.uploadTransportReceipt');
    Route::get('/outward-gatepass/{id}/thermal', [OutwardGatepassController::class, 'thermal'])->middleware('permission:outward.gatepass.view')->name('OutwardGatepass.thermal');
    Route::post('/outward-gatepass/{id}/packing-notes', [OutwardGatepassController::class, 'updatePackingNotes'])->middleware('permission:outward.gatepass.edit')->name('OutwardGatepass.updatePackingNotes');
    Route::post('/outward-gatepass/{id}/delivery-status', [OutwardGatepassController::class, 'updateDeliveryStatus'])->middleware('permission:outward.gatepass.edit')->name('OutwardGatepass.updateDeliveryStatus');

    // Customer Remaining Items Routes
    Route::get('/customer-remaining',[CustomerRemainingController::class, 'index'])->middleware('permission:outward.gatepass.view')->name('customer-remaining.index');
    Route::get('/customer-remaining/{id}', [CustomerRemainingController::class, 'show'])->middleware('permission:outward.gatepass.view')->name('customer-remaining.show');
    Route::post('/customer-remaining/{id}/mark-completed', [CustomerRemainingController::class, 'markCompleted'])->middleware('permission:outward.gatepass.edit')->name('customer-remaining.markCompleted');
    Route::get('/customer-remaining/{id}/create-gatepass', [OutwardGatepassController::class, 'createFromRemaining'])->middleware('permission:outward.gatepass.create')->name('OutwardGatepass.createFromRemaining');
    Route::delete('/customer-remaining/{id}', [CustomerRemainingController::class, 'delete'])->middleware('permission:outward.gatepass.delete')->name('customer-remaining.delete');
    
    // ✅ NEW: DC Creation from Customer Remaining (before gate pass)
    Route::get('/customer-remaining/{id}/create-dc', [CustomerRemainingController::class, 'showCreateDCForm'])->middleware('permission:outward.gatepass.create')->name('customer-remaining.create-dc');
    Route::post('/customer-remaining/{id}/store-dc', [CustomerRemainingController::class, 'storeDCFromRemaining'])->middleware('permission:outward.gatepass.create')->name('customer-remaining.store-dc');


    // Route::get('/fetch-product', [PurchaseController::class, 'fetchProduct'])->name('item.search');
    // Route::post('/fetch-item-details', [PurchaseController::class, 'fetchItemDetails']);
    // Route::get('/Purchase/create', function () {
    //     return view('admin_panel.purchase.add_purchase');
    // });
    // Route::get('/get-items-by-category/{categoryId}', [PurchaseController::class, 'getItemsByCategory'])->name('get-items-by-category');
    Route::get('/get-product-details/{id}', [ProductController::class, 'getProductDetails'])->name('get-product-details');

    // Route::get('booking/system', [SaleController::class,'booking-system'])->name('booking.index');
    Route::get('/dc-index', [SaleController::class, 'showdc'])->middleware('permission:generate Dc.view')->name('sale.showdc');
   Route::get('/dc-find-view', [SaleController::class, 'finddcview'])
    ->middleware('permission:find Dc.view')
    ->name('sale.find.view');

Route::get('/dc-find/{invoice}', [SaleController::class, 'finddc'])
    ->middleware('permission:find Dc.view')
    ->name('sale.find.search');
    Route::get('/sale/search', [SaleController::class, 'search'])->middleware('permission:generate Dc.view')->name('sale.search');
    Route::get('sale', [SaleController::class, 'index'])->middleware('permission:sale.view')->name('sale.index');
    Route::get('sale/create', [SaleController::class, 'addsale'])->middleware('permission:sale.create')->name('sale.add');
    Route::get('/products/search', [SaleController::class, 'searchProducts'])->middleware('permission:product.view')->name('products.search');
    Route::get('/search-product-name', [SaleController::class, 'searchpname'])->middleware('permission:product.view')->name('search-product-name');
    Route::get('/sale/check-stock', [SaleController::class, 'checkStock'])->middleware('permission:product.view')->name('sale.check.stock');
    Route::post('/sales/store', [SaleController::class, 'store'])->middleware('permission:sale.create|sale.edit')->name('sales.store');
    Route::get('/sales/{id}/return', [SaleController::class, 'saleretun'])->middleware('permission:sale.return.view|sale.return.create')->name('sales.return.create');
    Route::post('/sales-return/store', [SaleController::class, 'storeSaleReturn'])->middleware('permission:sale.return.create|sale.return.edit')->name('sales.return.store');
    Route::get('/sale-returns', [App\Http\Controllers\SaleController::class, 'salereturnview'])->middleware('permission:sale.return.view')->name('sale.returns.index');
    // Route::get('/sales/{id}/invoice', [SaleController::class, 'saleinvoice'])->name('sales.invoice');
    Route::get('/sales/{id}/edit', [SaleController::class, 'saleedit'])->middleware('permission:sale.edit')->name('sales.edit');
    Route::put('/sales/{id}', [SaleController::class, 'updatebooking'])->middleware('permission:sale.edit')->name('sales.update');
    Route::delete('/sales/{id}', [SaleController::class, 'destroy'])->middleware('permission:sale.delete')->name('sales.destroy');
    Route::get('/sales/{id}/dc', [SaleController::class, 'saledc'])->middleware('permission:sale.delivery.challan')->name('sales.dc');
    Route::get('/sales/{id}/recepit', [SaleController::class, 'salerecepit'])->middleware('permission:sale.receipt')->name('sales.recepit');
// AJAX (no refresh)
    Route::post('/sale/ajax/save', [SaleController::class, 'ajaxSave'])->middleware('permission:sale.create|sale.edit')->name('sale.ajax.save');
    Route::get('/sale/ajax/post', [SaleController::class, 'ajaxPost'])->middleware('permission:sale.view')->name('sale.ajax.post');
    // Route::get('/sale/ajax/post-and-print2', [SaleController::class, 'ajaxPostAndPrint2'])->middleware('permission:sale.view')->name('sale.ajax.post-and-print2');

    // Post a booking using the Main Store warehouse automatically (for cash/walking/credit customers)
    Route::post('/sale/ajax/post-mainstore', [SaleController::class, 'ajaxPostMainStore'])->middleware('permission:sale.view')->name('sale.ajax.post-mainstore');
    
    // ✅ Check booking posted state for button restoration on page load
    Route::get('/booking/check-posted-state/{id}', [SaleController::class, 'checkBookingPostedState'])->middleware('permission:sale.view')->name('booking.check-posted-state');
    
    // ✅ Draft Posting (btnPosted3) - Save items to sale_postings without stock deduction
    Route::post('/sale/ajax/post-draft', [SaleController::class, 'ajaxPostDraft'])->middleware('permission:sale.create|sale.edit')->name('sale.ajax.post-draft');
    
    // ✅ NEW: Post for Delivery (btnPosted3) - Like warehouse sale but marks ready_for_delivery instead of reducing stock
    Route::post('/sale/ajax/post-for-delivery', [SaleController::class, 'ajaxPostForDelivery'])->middleware('permission:sale.view')->name('sale.ajax.post-for-delivery');
    
    // ✅ NEW: Post & Finalize with Account Transfer + Ledger Update + Pending Stock
    Route::post('/sale/finalize-posting', [SaleController::class, 'finalizePosting'])->middleware('permission:sale.view')->name('sale.finalize.posting');
    
    // Booking Invoice (ProductBooking data)
    // routes/web.php
    // Route::get('get-warehouses/{product_id}',
    //     [SaleController::class, 'getWarehousesByProducts']
    // )->name('sale.warehouses.by.products');
    Route::get('/get-warehouses', [SaleController::class, 'getWarehousesByProducts'])->middleware('permission:warehouse.view');
    
    // Prints
    // Route::get('/sale/dc/{sale}', [SaleController::class, 'bookingDc'])->middleware('permission:booking.view')->name('sale.dc');
    // Route::get('/booking/dc/{id}', function(){
        Route::get('/sale/dc/{sale}', [SaleController::class, 'saleDc'])->middleware('permission:booking.view')->name('sale.dc');
        
        // ✅ Warehouse Selection for Draft Delivery (before DC creation)
        Route::get('/sale/warehouse-select/{sale}', [SaleController::class, 'showWarehouseSelection'])
        ->middleware('permission:warehouse.view')->name('sale.warehouse.select');
        
        Route::post('/sale/warehouse-select/{sale}', [SaleController::class, 'processWarehouseSelection'])
        ->middleware('permission:warehouse.view')->name('sale.warehouse.select.store');
        
    // Dedicated thermal print view for DC (server-rendered)
    Route::get('/sale/dc/{sale}/thermal', [SaleController::class, 'saleDcThermal'])->middleware('permission:booking.view')->name('sale.dc.thermal');
    
    // });
    Route::get('/sale/invoice/{sale}', [SaleController::class, 'invoicesale'])->middleware('permission:sale.invoice')->name('sale.invoice');
    // Route::get('/sale/invoice',function(){
        //     return view('admin_panel.sale.invoice2');
        // });
        Route::get('/sale/print2/{sale}', [SaleController::class, 'print2'])->name('sale.print2');
        // Route::get('/sale/dc/{sale}', [SaleController::class, 'dc'])->name('sale.dc');
        // booking system
        
        Route::get('bookings', [ProductBookingController::class, 'index'])->middleware('permission:booking.view')->name('bookings.index');
        Route::get('bookings/create', [ProductBookingController::class, 'create'])->middleware('permission:booking.create')->name('bookings.create');
        Route::post('bookings/store', [ProductBookingController::class, 'store'])->middleware('permission:booking.create|booking.edit')->name('bookings.store');
        Route::get('/booking/invoice/{booking}', [SaleController::class, 'invoice'])
        ->middleware('permission:booking.invoice')->name('booking.invoice');
    Route::get('booking/print2/{booking}', [SaleController::class, 'bookingPrint2'])->middleware('permission:booking.view')->name('booking.print2');
    Route::get('/sales/from-booking/{id}', [SaleController::class, 'convertFromBooking'])->middleware('permission:sale.create')->name('sales.from.booking');
   
    // web.php
    Route::get('/warehouse-stock-quantity', [StockTransferController::class, 'getStockQuantity'])->middleware('permission:stock.transfer.view')->name('warehouse.stock.quantity');
    Route::get('/product-locations-stock', [StockTransferController::class, 'getProductLocationsStock'])->middleware('permission:stock.transfer.view')->name('product.locations.stock');

    
    Route::get('/get-customers-by-type', [CustomerController::class, 'getByType'])->middleware('permission:customer.view');
    Route::get('/warehouse_stocks/filter-warehouses', [WarehouseStockController::class, 'getWarehousesForFilter'])->name('warehouse_stocks.filter_warehouses');
    Route::resource('warehouse_stocks', WarehouseStockController::class)->middleware('permission:warehouse.stock.view');
    
    // Stock Transfers with proper permission checks
    Route::get('stock_transfers', [StockTransferController::class, 'index'])->middleware('permission:stock.transfer.view')->name('stock_transfers.index');
    Route::get('stock_transfers/create', [StockTransferController::class, 'create'])->middleware('permission:stock.transfer.create')->name('stock_transfers.create');
    Route::post('stock_transfers', [StockTransferController::class, 'store'])->middleware('permission:stock.transfer.create')->name('stock_transfers.store');
    Route::delete('stock_transfers/{stockTransfer}', [StockTransferController::class, 'destroy'])->middleware('permission:stock.transfer.delete')->name('stock_transfers.destroy');
    
    // ✅ INTER-BRANCH STOCK REQUEST SYSTEM
    Route::prefix('inter-branch')->name('inter_branch_')->group(function () {
        // Stock Requests (Request/Approval/Reject workflow)
        Route::name('stock_requests.')->prefix('stock-requests')->group(function () {
            Route::get('/', [StockRequestController::class, 'index'])->middleware('permission:stock.request.view')->name('index');
            Route::get('/create', [StockRequestController::class, 'create'])->middleware('permission:stock.request.create')->name('create');
            Route::post('/', [StockRequestController::class, 'store'])->middleware('permission:stock.request.create')->name('store');
            Route::get('/{stockRequest}', [StockRequestController::class, 'show'])->middleware('permission:stock.request.approve')->name('show');
            Route::post('/{stockRequest}/approve', [StockRequestController::class, 'approve'])->middleware('permission:stock.request.approve')->name('approve');
            Route::post('/{stockRequest}/reject', [StockRequestController::class, 'reject'])->middleware('permission:stock.request.approve')->name('reject');
            Route::get('/warehouse-stock/{warehouse}/{product}', [StockRequestController::class, 'getWarehouseStock'])->middleware('permission:stock.request.approve')->name('warehouse_stock');
        });

        // Vouchers (Payment/Receipt - to settle ledger balances)
        Route::name('vouchers.')->prefix('vouchers')->group(function () {
            Route::get('/', [VoucherInterBranchController::class, 'index'])->middleware('permission:inter.branch.voucher.view')->name('index');
            Route::get('/payment/create', [VoucherInterBranchController::class, 'createPayment'])->middleware('permission:inter.branch.voucher.create')->name('create_payment');
            Route::post('/payment', [VoucherInterBranchController::class, 'storePayment'])->middleware('permission:inter.branch.voucher.create')->name('store_payment');
            Route::get('/receipt/create', [VoucherInterBranchController::class, 'createReceipt'])->middleware('permission:inter.branch.voucher.create')->name('create_receipt');
            Route::post('/receipt', [VoucherInterBranchController::class, 'storeReceipt'])->middleware('permission:inter.branch.voucher.create')->name('store_receipt');
            Route::get('/{voucher}', [VoucherInterBranchController::class, 'show'])->middleware('permission:inter.branch.voucher.view')->name('show');
        });
    });

    // ✅ BRANCH LEDGER SYSTEM (Financial tracking)
    Route::prefix('branch-ledger')->name('branch_ledger_')->middleware('permission:branch.ledger.view')->group(function () {
        Route::get('/', [BranchLedgerController::class, 'index'])->name('index');
        Route::get('/all-branches', [BranchLedgerController::class, 'allBranches'])->name('all_branches');
        Route::get('/branch/{branchId}', [BranchLedgerController::class, 'viewBranchLedger'])->name('view_branch');
        Route::get('/branch/{branchId}/transfers', [BranchLedgerController::class, 'transferDetails'])->name('transfer_details');
        Route::get('/summary', [BranchLedgerController::class, 'summary'])->name('summary');
        Route::get('/ledger', [BranchLedgerController::class, 'ledger'])->name('detail');
        Route::get('/report', [BranchLedgerController::class, 'report'])->middleware('permission:branch.ledger.report')->name('report');
    });
    
    ////////////
    Route::get('/get-stock/{product}', [StocksController::class, 'getStock'])
        ->middleware('permission:warehouse.stock.view')->name('get.stock');
        // ????????
    Route::get('warehouse_orders', [WarehouseStockController::class, 'warehouseOrders'])->name('warehouse_orders.index');

    // Admin: warehouse orders management (index / edit / update)
    Route::prefix('admin')->name('admin.')->group(function(){
        Route::get('warehouse_orders', [App\Http\Controllers\WarehouseOrderController::class, 'index'])->name('warehouse_orders.index');
        Route::get('warehouse_orders/{id}/edit', [App\Http\Controllers\WarehouseOrderController::class, 'edit'])->name('warehouse_orders.edit');
        Route::put('warehouse_orders/{id}', [App\Http\Controllers\WarehouseOrderController::class, 'update'])->name('warehouse_orders.update');
    });
    Route::get('/warehouse-orders/status/{id}', [WarehouseStockController::class, 'changeStatus'])
    ->name('warehouse_orders.status');
    // ???????//////////
    // narratiions
    Route::resource('narrations', NarrationController::class)->middleware('permission:narration.view')->only(['index', 'store', 'destroy']);
    Route::get('vouchers/{type}', [VoucherController::class, 'index'])->middleware('permission:voucher.view')->name('vouchers.index');
    Route::post('vouchers/store', [VoucherController::class, 'store'])->middleware('permission:voucher.view')->name('vouchers.store');
    Route::get('/view_all', [AccountsHeadController::class, 'index'])->middleware('permission:chart.of.accounts.view')->name('view_all');
    Route::get('/branch-accounts/{branchId}', [AccountsHeadController::class, 'showBranchAccounts'])->middleware('permission:chart.of.accounts.view')->name('branch.accounts');
    // ✅ ERP STANDARD: Per-Account General Ledger
    Route::get('/account-ledger/{accountId}', [AccountsHeadController::class, 'accountLedger'])->middleware('permission:chart.of.accounts.view')->name('account.ledger');
    // ✅ Permission removed - getVendorBalance now accessible
    Route::get('/get-vendor-balance/{id}', [VendorController::class, 'getVendorBalance']);
    ///// Recipt Vouchers
    Route::get('/receipt-voucher/print/{id}', [VoucherController::class, 'print'])->middleware('permission:receipts.voucher.print')->name('receiptVoucher.print');
    Route::get('/get-accounts-by-head/{headId}', [VoucherController::class, 'getAccountsByHead']);
    Route::get('/get-receipt-party-list', [VoucherController::class, 'getReceiptPartyList'])->name('receipt.party.list');
    Route::get('/get-opening-balance/{type}/{id}', [VoucherController::class, 'getOpeningBalance'])->middleware('permission:chart.of.accounts.view');
    Route::post('/store-narration-ajax', [VoucherController::class, 'storeNarrationAjax'])->name('store.narration.ajax');


    Route::get('/all-recepit-vochers', [VoucherController::class, 'all_recepit_vochers'])->middleware('permission:receipts.voucher.view')->name('all-recepit-vochers');
    Route::get('/recepit-vochers', [VoucherController::class, 'recepit_vochers'])->middleware('permission:receipts.voucher.view')->name('recepit-vochers');
    Route::post('/recepit/vochers/stote', [VoucherController::class, 'store_rec_vochers'])->middleware('permission:receipts.voucher.create')->name('recepit.vochers.store');
    ////// payment vouchers
    Route::get('/Payment-vochers', [VoucherController::class, 'Payment_vochers'])->middleware('permission:payment.voucher.view')->name('Payment-vochers');
route::post('/Payment/vochers/stote', [VoucherController::class, 'store_Pay_vochers'])->middleware('permission:payment.voucher.create')->name('Payment.vochers.store');
Route::get('/all-Payment-vochers', [VoucherController::class, 'all_Payment_vochers'])->middleware('permission:payment.voucher.view')->name('all-Payment-vochers');
Route::get('/Payment-voucher/print/{id}', [VoucherController::class, 'Paymentprint'])->middleware('permission:payment.voucher.print')->name('PaymentVoucher.print');
    ////// expense voucher
    Route::get('/all-expense-vochers', [VoucherController::class, 'all_expense_vochers'])->middleware('permission:expense.voucher.view')->name('all-expense-vochers');
Route::get('/expense-vochers', [VoucherController::class, 'expense_vochers'])->middleware('permission:expense.voucher.view')->name('expense-vochers');
route::post('/expense/vochers/stote', [VoucherController::class, 'store_expense_vochers'])->middleware('permission:expense.voucher.create')->name('expense.vochers.store');
Route::get('/expense-voucher/print/{id}', [VoucherController::class, 'expenseprint'])->middleware('permission:expense.voucher.print')->name('expenseVoucher.print');
    ////// journal voucher
    Route::get('/journal-vouchers',         [VoucherController::class, 'journal_vouchers_index'])->middleware('permission:journal.voucher.view')->name('journal.vouchers.index');
    Route::get('/journal-vouchers/create',  [VoucherController::class, 'journal_voucher_create'])->middleware('permission:journal.voucher.create')->name('journal.vouchers.create');
    Route::post('/journal-vouchers/store',  [VoucherController::class, 'journal_voucher_store'])->middleware('permission:journal.voucher.create')->name('journal.vouchers.store');
    Route::get('/journal-vouchers/{id}/print',  [VoucherController::class, 'journal_voucher_print'])->middleware('permission:journal.voucher.view')->name('journal.vouchers.print');
    Route::delete('/journal-vouchers/{id}', [VoucherController::class, 'journal_voucher_destroy'])->middleware('permission:journal.voucher.delete')->name('journal.vouchers.destroy');
    Route::get('/get-journal-party-list',   [VoucherController::class, 'getJournalPartyList'])->name('journal.party.list');
    // reporting routes


    Route::get('/report/item-stock', [ReportingController::class, 'item_stock_report'])->middleware('permission:report.item.stock.view')->name('report.item_stock');
    Route::post('/report/item-stock-fetch', [ReportingController::class, 'fetchItemStock'])->middleware('permission:report.item.stock.view')->name('report.item_stock.fetch');

    Route::get('report/purchase', [ReportingController::class, 'purchase_report'])->middleware('permission:report.purchase.view')->name('report.purchase');
    Route::post('report/purchase/fetch', [ReportingController::class, 'fetchPurchaseReport'])->middleware('permission:report.purchase.view')->name('report.purchase.fetch');

    Route::get('report/local-purchase', [ReportingController::class, 'local_purchase_report'])->middleware('permission:report.purchase.view')->name('report.local_purchase');
    Route::get('report/local-purchase/fetch', [ReportingController::class, 'fetch_local_purchase_report'])->middleware('permission:report.purchase.view')->name('report.local_purchase.fetch');
    Route::post('report/local-purchase/pay', [PurchaseController::class, 'payLocalPurchase'])->middleware('permission:purchase.create')->name('report.local_purchase.pay');

    // ✅ PO vs Gatepass Report
    Route::get('report/po-vs-gatepass', [ReportingController::class, 'po_vs_gatepass_report'])->middleware('permission:report.purchase.view')->name('report.po_vs_gatepass');
    Route::get('report/po-vs-gatepass/fetch', [ReportingController::class, 'fetch_po_vs_gatepass_report'])->middleware('permission:report.purchase.view')->name('report.po_vs_gatepass.fetch');
    Route::get('report/get-vendors-by-branch', [ReportingController::class, 'getVendorsByBranch'])->name('report.get_vendors_by_branch');

    Route::get('report/sale', [ReportingController::class, 'sale_report'])->middleware('permission:report.sale.view')->name('report.sale');
    Route::get('report/sale/fetch', [ReportingController::class, 'fetchsaleReport'])->middleware('permission:report.sale.view')->name('report.sale.fetch');

    Route::get('report/customer/ledger', [ReportingController::class, 'customer_ledger_report'])->middleware('permission:report.customer.ledger.view')->name('report.customer.ledger');
    Route::get('report/customer-ledger/fetch', [ReportingController::class, 'fetch_customer_ledger'])->middleware('permission:report.customer.ledger.view')->name('report.customer.ledger.fetch');
    Route::get('report/customer-ledger/fetch-detailed', [ReportingController::class, 'fetch_customer_ledger_detailed'])->middleware('permission:report.customer.ledger.view')->name('report.customer.ledger.fetch.detailed');
    Route::get('report/customers-by-branch', [ReportingController::class, 'customersByBranch'])->middleware('permission:report.customer.ledger.view')->name('report.customers.byBranch');
Route::get('testing',[ReportingController::class, 'customer_ledger_new'])->middleware('permission:report.customer.ledger.view')->name('report.customer.ledger.new');
Route::get('report/customer-ledger/fetch-new', [ReportingController::class, 'fetch_customer_ledger_new'])->middleware('permission:report.customer.ledger.view')->name('report.customer.ledger.fetch.new');

    Route::get('report/salesman-performance', [ReportingController::class, 'salesman_performance_report'])->middleware('permission:report.sale.view')->name('report.salesman.performance');
    Route::get('report/salesman-performance/fetch', [ReportingController::class, 'fetch_salesman_performance'])->middleware('permission:report.sale.view')->name('report.salesman.performance.fetch');
    Route::get('report/salesman-ledger/fetch', [ReportingController::class, 'fetch_salesman_ledger'])->middleware('permission:report.sale.view')->name('report.salesman.ledger.fetch');
    Route::get('report/salesmen-by-branch', [ReportingController::class, 'salesmenByBranch'])->middleware('permission:report.sale.view')->name('report.salesmen.byBranch');

    Route::get('report/vendor-ledger-new', [ReportingController::class, 'vendor_ledger_new'])->middleware('permission:report.vendor.ledger.view')->name('report.vendor.ledger.new');
    Route::get('report/vendor-ledger/fetch-new', [ReportingController::class, 'fetch_vendor_ledger_new'])->middleware('permission:report.vendor.ledger.view')->name('report.vendor.ledger.fetch.new');
    Route::get('report/vendors-by-branch', [ReportingController::class, 'vendorsByBranch'])->middleware('permission:report.vendor.ledger.view|report.purchase.view')->name('vendors-by-branch');
    Route::get('/warehouses-by-branch', [App\Http\Controllers\WarehouseController::class, 'warehousesByBranch'])->middleware('permission:inward.gatepass.create|inward.gatepass.edit|purchase.create')->name('warehouses-by-branch');
    Route::get('reports/onhand', [ReportingController::class, 'onhand'])->middleware('permission:report.inventory.onhand.view')->name('reports.onhand');
    
    // ✅ Stock Hold Audit Report (ERP Compliance)
    Route::get('report/stock-hold-audit', [ReportingController::class, 'stockHoldAudit'])->middleware('permission:report.stock.hold.view')->name('report.stock.hold.audit');
    Route::get('report/stock-hold-audit/export', [ReportingController::class, 'stockHoldAuditExport'])->middleware('permission:report.stock.hold.view')->name('report.stock.hold.audit.export');
    
    // reports
    Route::prefix('coa')->middleware('permission:chart.of.accounts.view')->group(function () {
        Route::get('/', [AccountsHeadController::class, 'index'])->name('coa.index');
        Route::post('/head', [AccountsHeadController::class, 'storeHead'])->middleware('permission:chart.of.accounts.create')->name('coa.head.store');
        Route::post('/account', [AccountsHeadController::class, 'storeAccount'])->middleware('permission:chart.of.accounts.create')->name('coa.account.store');
        Route::put('/account/{id}', [AccountsHeadController::class, 'updateAccount'])->middleware('permission:chart.of.accounts.create')->name('coa.account.update');
        Route::delete('/account/{id}', [AccountsHeadController::class, 'destroyAccount'])->middleware('permission:chart.of.accounts.create')->name('coa.account.delete');
                    

    });

    // Notification Routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', function() {
            return view('notifications.index');
        })->name('notifications.index');

        // Branch <-> Warehouse mapping (admin)
        
        Route::get('/pending', [NotificationController::class, 'getPendingNotifications'])->name('notifications.pending');
        Route::get('/all', [NotificationController::class, 'getAllNotifications'])->name('notifications.all');
        Route::get('/count', [NotificationController::class, 'getCount'])->name('notifications.count');
        Route::post('/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
        Route::post('/{id}/mark-as-sent', [NotificationController::class, 'markAsSent'])->name('notifications.mark-sent');
        Route::post('/{id}/dismiss', [NotificationController::class, 'dismiss'])->name('notifications.dismiss');
    });
// });
require __DIR__ . '/auth.php';
