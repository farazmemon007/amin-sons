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
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarehouseStockController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;




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



    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    route::get('/category', [CategoryController::class, 'index'])->middleware('permission:category.view')->name('Category.home');
    Route::get('/category/delete/{id}', [CategoryController::class, 'delete'])->middleware('permission:category.delete')->name('delete.category');
    route::post('/category/stote', [CategoryController::class, 'store'])->middleware('permission:category.create|category.edit')->name('store.category');

    route::get('/Brand', [BrandController::class, 'index'])->middleware('permission:brand.view')->name('Brand.home');
    Route::get('/Brand/delete/{id}', [BrandController::class, 'delete'])->middleware('permission:brand.delete')->name('delete.Brand');
    route::post('/Brand/stote', [BrandController::class, 'store'])->middleware('permission:brand.create|brand.edit')->name('store.Brand');

    route::get('/Unit', [UnitController::class, 'index'])->middleware('permission:unit.view')->name('Unit.home');
    Route::get('/Unit/delete/{id}', [UnitController::class, 'delete'])->middleware('permission:unit.delete')->name('delete.Unit');
    route::post('/Unit/stote', [UnitController::class, 'store'])->middleware('permission:unit.create|unit.edit')->name('store.Unit');

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

    // Route::get('/barcode/{id}', [ProductController::class, 'barcode'])->name('product.barcode');
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
    Route::get('/vendor', [VendorController::class, 'index'])->middleware('permission:vendor.view');
    Route::post('/vendor/store', [VendorController::class, 'store'])->middleware('permission:vendor.create|vendor.edit')->name('vendors.store.ajax');
    Route::get('/vendor/delete/{id}', [VendorController::class, 'delete'])->middleware('permission:vendor.delete');
    Route::get('/vendors-ledger', [VendorController::class, 'vendors_ledger'])->middleware('permission:vendor.view')->name('vendors-ledger');
    Route::get('/vendor/payments', [VendorController::class, 'vendor_payments'])->middleware('permission:vendor.payments.view')->name('vendor.payments');
    Route::post('/vendor/payments', [VendorController::class, 'store_vendor_payment'])->middleware('permission:vendor.payments.create')->name('vendor.payments.store');
    Route::get('/vendor/bilties', [VendorController::class, 'vendor_bilties'])->middleware('permission:vendor.bilties.view')->name('vendor.bilties');
    Route::post('/vendor/bilties', [VendorController::class, 'store_vendor_bilty'])->middleware('permission:vendor.bilties.create')->name('vendor.bilties.store');

    // Warehouse Routes
    /////
    Route::get('/warehouses/get/', [WarehouseController::class, 'getWarehouses'])->middleware('permission:warehouse.view')->name('warehouses.get');

    /////
    Route::get('/warehouse', [WarehouseController::class, 'index'])->middleware('permission:warehouse.view');
    Route::post('/warehouse/store', [WarehouseController::class, 'store'])->middleware('permission:warehouse.create|warehouse.edit');
    Route::get('/warehouse/delete/{id}', [WarehouseController::class, 'delete'])->middleware('permission:warehouse.delete');

    // Branches
    Route::resource('branch', BranchController::class)->middleware('permission:branch.view')->names('branch')->only(['index', 'store']);
    Route::get('/branch/delete/{id}', [BranchController::class, 'delete'])->middleware('permission:branch.delete')->name('branch.delete');




    Route::middleware(['role:super admin|admin'])->group(function () {
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
    route::get('/add/Purchase', [PurchaseController::class, 'add_purchase'])->middleware('permission:purchase.create')->name('add_purchase');
    route::post('/Purchase/stote', [PurchaseController::class, 'store'])->middleware('permission:purchase.create|purchase.edit')->name('store.Purchase');
    Route::get('/purchase/{id}/edit', [PurchaseController::class, 'edit'])->middleware('permission:purchase.edit')->name('purchase.edit');
    Route::put('/purchase/{id}', [PurchaseController::class, 'update'])->middleware('permission:purchase.edit')->name('purchase.update');
    Route::delete('/purchase/{id}', [PurchaseController::class, 'destroy'])->middleware('permission:purchase.delete')->name('purchase.destroy');
    Route::post('/search_products', [ProductController::class, 'searchProducts'])->middleware('permission:product.view')->name('search_products');
    Route::get('/purchase/{id}/invoice', [PurchaseController::class, 'Invoice'])->middleware('permission:purchase.invoice')->name('purchase.invoice');

    Route::get('purchase/return', [PurchaseController::class, 'purchaseReturnIndex'])->middleware('permission:purchase.return.view')->name('purchase.return.index');
    Route::get('purchase/return/{id}', [PurchaseController::class, 'showReturnForm'])->middleware('permission:purchase.return.view')->name('purchase.return.show');
    Route::post('purchase/return/store', [PurchaseController::class, 'storeReturn'])->middleware('permission:purchase.return.create|purchase.return.edit')->name('purchase.return.store');
    Route::get('/getPartyList', [PurchaseController::class, 'getPartyList'])->middleware('permission:vendor.view')->name('party.list');
    // Inward Gatepass Routes
    Route::get('/InwardGatepass', [InwardgatepassController::class, 'index'])->middleware('permission:inward.gatepass.view')->name('InwardGatepass.home');
    Route::get('/add/InwardGatepass', [InwardgatepassController::class, 'create'])->middleware('permission:inward.gatepass.create')->name('add_inwardgatepass');
    Route::post('/InwardGatepass/store', [InwardgatepassController::class, 'store'])->middleware('permission:inward.gatepass.create|inward.gatepass.edit')->name('store.InwardGatepass');
    Route::get('/InwardGatepass/{id}', [InwardgatepassController::class, 'show'])->middleware('permission:inward.gatepass.view')->name('InwardGatepass.show');

    // edit/update/delete abhi comment kiye hue hain
    Route::get('/InwardGatepass/{id}/edit', [InwardgatepassController::class, 'edit'])->middleware('permission:inward.gatepass.edit')->name('InwardGatepass.edit');
    Route::put('/InwardGatepass/{id}', [InwardgatepassController::class, 'update'])->middleware('permission:inward.gatepass.edit')->name('InwardGatepass.update');
    Route::get('/inward-gatepass/{id}/pdf', [InwardgatepassController::class, 'pdf'])->middleware('permission:inward.gatepass.view')->name('InwardGatepass.pdf');

    Route::delete('/InwardGatepass/{id}', [InwardgatepassController::class, 'destroy'])->middleware('permission:inward.gatepass.delete')->name('InwardGatepass.destroy');
    // Products search
    Route::get('/search-products', [InwardgatepassController::class, 'searchProducts'])->middleware('permission:product.view')->name('search-products');

    // Show Add Bill Form
    Route::get('inward-gatepass/{id}/add-bill', [PurchaseController::class, 'addBill'])->middleware('permission:purchase.create')->name('add_bill');
    // Store Bill
    Route::post('inward-gatepass/{id}/store-bill', [PurchaseController::class, 'store'])->middleware('permission:purchase.create|purchase.edit')->name('store.bill');
    // Purchase Return Routes

    // Route::get('/fetch-product', [PurchaseController::class, 'fetchProduct'])->name('item.search');
    // Route::post('/fetch-item-details', [PurchaseController::class, 'fetchItemDetails']);
    // Route::get('/Purchase/create', function () {
    //     return view('admin_panel.purchase.add_purchase');
    // });
    // Route::get('/get-items-by-category/{categoryId}', [PurchaseController::class, 'getItemsByCategory'])->name('get-items-by-category');
    Route::get('/get-product-details/{id}', [ProductController::class, 'getProductDetails'])->name('get-product-details');

    // Route::get('booking/system', [SaleController::class,'booking-system'])->name('booking.index');
    Route::get('sale', [SaleController::class, 'index'])->middleware('permission:sale.view')->name('sale.index');
    Route::get('sale/create', [SaleController::class, 'addsale'])->middleware('permission:sale.create')->name('sale.add');
    Route::get('/products/search', [SaleController::class, 'searchProducts'])->middleware('permission:product.view')->name('products.search');
    Route::get('/search-product-name', [SaleController::class, 'searchpname'])->middleware('permission:product.view')->name('search-product-name');
    Route::post('/sales/store', [SaleController::class, 'store'])->middleware('permission:sale.create|sale.edit')->name('sales.store');
    Route::get('/sales/{id}/return', [SaleController::class, 'saleretun'])->middleware('permission:sale.return.view|sale.return.create')->name('sales.return.create');
    Route::post('/sales-return/store', [SaleController::class, 'storeSaleReturn'])->middleware('permission:sale.return.create|sale.return.edit')->name('sales.return.store');
    Route::get('/sale-returns', [App\Http\Controllers\SaleController::class, 'salereturnview'])->middleware('permission:sale.return.view')->name('sale.returns.index');
    // Route::get('/sales/{id}/invoice', [SaleController::class, 'saleinvoice'])->name('sales.invoice');
    Route::get('/sales/{id}/edit', [SaleController::class, 'saleedit'])->middleware('permission:sale.edit')->name('sales.edit');
    Route::put('/sales/{id}', [SaleController::class, 'update'])->middleware('permission:sale.edit')->name('sales.update');
    Route::delete('/sales/{id}', [SaleController::class, 'destroy'])->middleware('permission:sale.delete')->name('sales.destroy');
    Route::get('/sales/{id}/dc', [SaleController::class, 'saledc'])->middleware('permission:sale.delivery.challan')->name('sales.dc');
    Route::get('/sales/{id}/recepit', [SaleController::class, 'salerecepit'])->middleware('permission:sale.receipt')->name('sales.recepit');
// AJAX (no refresh)
    Route::post('/sale/ajax/save', [SaleController::class, 'ajaxSave'])->middleware('permission:sale.create|sale.edit')->name('sale.ajax.save');
    Route::get('/sale/ajax/post', [SaleController::class, 'ajaxPost'])->middleware('permission:sale.view')->name('sale.ajax.post');
    Route::get('/sale/invoice/{booking}', [SaleController::class, 'invoice'])
    ->middleware('permission:sale.invoice')->name('booking.invoice');
// routes/web.php
// Route::get('get-warehouses/{product_id}',
//     [SaleController::class, 'getWarehousesByProducts']
// )->name('sale.warehouses.by.products');
Route::get('/get-warehouses', [SaleController::class, 'getWarehousesByProducts'])->middleware('permission:warehouse.view');

        // Prints
    Route::get('/booking/dc/{booking}', [SaleController::class, 'bookingDc'])->middleware('permission:booking.view')->name('booking.dc');
    // Route::get('/booking/dc/{id}', function(){

    // });
    Route::get('/sale/invoice/{sale}', [SaleController::class, 'invoice'])->middleware('permission:sale.invoice')->name('sale.invoice');
    // Route::get('/sale/invoice',function(){
    //     return view('admin_panel.sale.invoice2');
    // });
    // Route::get('/sale/print2/{sale}', [SaleController::class, 'print2'])->name('sale.print2');
    // Route::get('/sale/dc/{sale}', [SaleController::class, 'dc'])->name('sale.dc');
    // booking system

    Route::get('bookings', [ProductBookingController::class, 'index'])->middleware('permission:booking.view')->name('bookings.index');
    Route::get('bookings/create', [ProductBookingController::class, 'create'])->middleware('permission:booking.create')->name('bookings.create');
    Route::post('bookings/store', [ProductBookingController::class, 'store'])->middleware('permission:booking.create|booking.edit')->name('bookings.store');
    Route::get('booking/receipt/{id}', [ProductBookingController::class, 'receipt'])->middleware('permission:booking.receipt')->name('booking.receipt');
    Route::get('/sales/from-booking/{id}', [SaleController::class, 'convertFromBooking'])->middleware('permission:sale.create')->name('sales.from.booking');

    // web.php
    Route::get('/warehouse-stock-quantity', [StockTransferController::class, 'getStockQuantity'])->middleware('permission:stock.transfer.view')->name('warehouse.stock.quantity');


    Route::get('/get-customers-by-type', [CustomerController::class, 'getByType'])->middleware('permission:customer.view');
    Route::resource('warehouse_stocks', WarehouseStockController::class)->middleware('permission:warehouse.stock.view');
    Route::resource('stock_transfers', StockTransferController::class)->middleware('permission:stock.transfer.view');
    ////////////
    Route::get('/get-stock/{product}', [StocksController::class, 'getStock'])
        ->middleware('permission:warehouse.stock.view')->name('get.stock');
    //////////
    // narratiions
    Route::resource('narrations', NarrationController::class)->middleware('permission:narration.view')->only(['index', 'store', 'destroy']);
    Route::get('vouchers/{type}', [VoucherController::class, 'index'])->middleware('permission:voucher.view')->name('vouchers.index');
    Route::post('vouchers/store', [VoucherController::class, 'store'])->middleware('permission:voucher.view')->name('vouchers.store');
    Route::get('/view_all', [AccountsHeadController::class, 'index'])->middleware('permission:chart.of.accounts.view')->name('view_all');
    Route::get('/get-vendor-balance/{id}', [VendorController::class, 'getVendorBalance'])->middleware('permission:vendor.view');
    ///// Recipt Vouchers
    Route::get('/receipt-voucher/print/{id}', [VoucherController::class, 'print'])->middleware('permission:receipts.voucher.print')->name('receiptVoucher.print');
    Route::get('/get-accounts-by-head/{headId}', [VoucherController::class, 'getAccountsByHead'])->middleware('permission:chart.of.accounts.view');
    Route::get('/get-opening-balance/{type}/{id}', [VoucherController::class, 'getOpeningBalance'])->middleware('permission:chart.of.accounts.view');


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
    // reporting routes

    Route::get('/report/item-stock', [ReportingController::class, 'item_stock_report'])->middleware('permission:report.item.stock.view')->name('report.item_stock');
    Route::post('/report/item-stock-fetch', [ReportingController::class, 'fetchItemStock'])->middleware('permission:report.item.stock.view')->name('report.item_stock.fetch');

    Route::get('report/purchase', [ReportingController::class, 'purchase_report'])->middleware('permission:report.purchase.view')->name('report.purchase');
    Route::post('report/purchase/fetch', [ReportingController::class, 'fetchPurchaseReport'])->middleware('permission:report.purchase.view')->name('report.purchase.fetch');

    Route::get('report/sale', [ReportingController::class, 'sale_report'])->middleware('permission:report.sale.view')->name('report.sale');
    Route::get('report/sale/fetch', [ReportingController::class, 'fetchsaleReport'])->middleware('permission:report.sale.view')->name('report.sale.fetch');

    Route::get('report/customer/ledger', [ReportingController::class, 'customer_ledger_report'])->middleware('permission:report.customer.ledger.view')->name('report.customer.ledger');
    Route::get('report/customer-ledger/fetch', [ReportingController::class, 'fetch_customer_ledger'])->middleware('permission:report.customer.ledger.view')->name('report.customer.ledger.fetch');

    Route::get('reports/onhand', [ReportingController::class, 'onhand'])->middleware('permission:report.inventory.onhand.view')->name('reports.onhand');
    // reports
    Route::prefix('coa')->middleware('permission:chart.of.accounts.view')->group(function () {
        Route::get('/', [AccountsHeadController::class, 'index'])->name('coa.index');
        Route::post('/head', [AccountsHeadController::class, 'storeHead'])->middleware('permission:chart.of.accounts.create')->name('coa.head.store');
        Route::post('/account', [AccountsHeadController::class, 'storeAccount'])->middleware('permission:chart.of.accounts.create')->name('coa.account.store');
    });

    // Notification Routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', function() {
            return view('notifications.index');
        })->name('notifications.index');
        Route::get('/pending', [NotificationController::class, 'getPendingNotifications'])->name('notifications.pending');
        Route::get('/all', [NotificationController::class, 'getAllNotifications'])->name('notifications.all');
        Route::get('/count', [NotificationController::class, 'getCount'])->name('notifications.count');
        Route::post('/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
        Route::post('/{id}/mark-as-sent', [NotificationController::class, 'markAsSent'])->name('notifications.mark-sent');
        Route::post('/{id}/dismiss', [NotificationController::class, 'dismiss'])->name('notifications.dismiss');
    });
});
require __DIR__ . '/auth.php';
