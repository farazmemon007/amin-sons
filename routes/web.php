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

    route::get('/category', [CategoryController::class, 'index'])->name('Category.home');
    Route::get('/category/delete/{id}', [CategoryController::class, 'delete'])->name('delete.category');
    route::post('/category/stote', [CategoryController::class, 'store'])->name('store.category');

    route::get('/Brand', [BrandController::class, 'index'])->name('Brand.home');
    Route::get('/Brand/delete/{id}', [BrandController::class, 'delete'])->name('delete.Brand');
    route::post('/Brand/stote', [BrandController::class, 'store'])->name('store.Brand');

    route::get('/Unit', [UnitController::class, 'index'])->name('Unit.home');
    Route::get('/Unit/delete/{id}', [UnitController::class, 'delete'])->name('delete.Unit');
    route::post('/Unit/stote', [UnitController::class, 'store'])->name('store.Unit');

    route::get('/subcategory', [SubcategoryController::class, 'index'])->name('subcategory.home');
    Route::get('/subcategory/delete/{id}', [SubcategoryController::class, 'delete'])->name('delete.subcategory');
    route::post('/subcategory/stote', [SubcategoryController::class, 'store'])->name('store.subcategory');

    Route::post('/assembly/pluck-part', [AssemblyController::class, 'pluckPart'])->name('assembly.pluck.part');
    Route::post('/assembly/repair-incomplete', [AssemblyController::class, 'repairIncomplete'])->name('assembly.repair.incomplete');
    Route::post('/assembly/build-auto', [AssemblyController::class, 'buildAuto'])->name('assembly.build.auto');
    Route::get('/products/{id}/assembly-report', [ProductController::class, 'assemblyReport'])->name('products.assembly-report');
    Route::get('/assembly/summary', [ProductController::class, 'assemblySummary'])->name('assembly.summary');

    Route::post('/assembly/ensure-part-for-sale', [AssemblyController::class, 'ensurePartForSale'])->name('assembly.ensure_part_for_sale');
    Route::get('productget', [ProductController::class, 'productget'])->name('productget');

    Route::get('/Product', [ProductController::class, 'product'])->name('product');
    Route::get('/productview/{id}', [ProductController::class, 'productview'])->name('productview');
    ////////////
    Route::get('/products/price', [ProductController::class, 'getPrice'])
        ->name('products.price');
//////

///////////////////////////////////////////////////////////////////////////////
    //////////
Route::get('/search_products', [ProductController::class, 'searchProducts'])
     ->name('products_search');
    Route::get('/search-products-sale', [ProductController::class, 'searchProductsForSalebypagination'])->name('search-products-sale');

//////////////////////////////////////////////////////////////////////////////////////////////
    //////////
    Route::get('/create_prodcut', [ProductController::class, 'view_store'])->name('store');
    Route::post('/store-product', [ProductController::class, 'store_product'])->name('store-product');
//  Route::post('/store-product', function (Request $request) {
//     echo "<pre>";
//     print_r($request->all());
// })->name('store-product');

    Route::put('/product/update/{id}', [ProductController::class, 'update'])->name('product.update');
    Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::get('/generate-barcode-image', [ProductController::class, 'generateBarcode'])->name('generate-barcode-image');

    // Route::get('/barcode/{id}', [ProductController::class, 'barcode'])->name('product.barcode');
    // Searches
    Route::get('/generate-barcode-image', [ProductController::class, 'generateBarcode'])->name('generate-barcode-image');
    Route::get('/get-subcategories/{category_id}', [ProductController::class, 'getSubcategories'])->name('fetch-subcategories');

    Route::get('/search-part-name', [ProductController::class, 'searchPartName'])->name('search-part-name');

    Route::prefix('discount')->group(function () {
        Route::get('/', [DiscountController::class, 'index'])->name('discount.index');
        Route::get('/create', [DiscountController::class, 'create'])->name('discount.create');
        Route::post('/store', [DiscountController::class, 'store'])->name('discount.store');
        Route::post('/toggle-status/{id}', [DiscountController::class, 'toggleStatus'])->name('discount.toggleStatus');
        Route::get('/barcode/{id}', [DiscountController::class, 'barcode'])->name('discount.barcode');
    });

    Route::get('/parts-adjust', [AssemblyController::class, 'adjustForm'])
        ->name('stock.adjust.form');

    Route::post('/stock-adjust/bulk', [AssemblyController::class, 'adjustBulk'])
        ->name('assembly.adjust.bulk');

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
    Route::get('/assembly-report', [AssemblyController::class, 'index'])->name('assembly.report');
    Route::get('/assembly-report/{product}', [AssemblyController::class, 'show'])->name('assembly.report.show');
    Route::post('/assembly/build', [AssemblyController::class, 'build'])->name('assembly.build');

    // routes/web.php

    // Customer Routes
    // Dropdown list (by type)
    Route::get('sale/customers', [CustomerController::class, 'saleindex'])
        ->name('salecustomers.index');

    // Single customer detail
    Route::get('sale/customers/{id}', [CustomerController::class, 'show'])
        ->name('salecustomers.show');
    Route::get('/get-customer/{id}', [SaleController::class, 'getCustomerData'])->name('customers.show');
    // Cutomer create
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers/store', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/edit/{id}', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::post('/customers/update/{id}', [CustomerController::class, 'update'])->name('customers.update');
    Route::get('/customers/delete/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    // New
    Route::get('/customers/inactive', [CustomerController::class, 'inactiveCustomers'])->name('customers.inactive');
    Route::get('/customers/inactive/{id}', [CustomerController::class, 'markInactive'])->name('customers.markInactive');
    Route::get('customers/toggle-status/{id}', [CustomerController::class, 'toggleStatus'])->name('customers.toggleStatus');
    Route::get('/customers/ledger', [CustomerController::class, 'customer_ledger'])->name('customers.ledger');
    Route::get('/customer/payments', [CustomerController::class, 'customer_payments'])->name('customer.payments');
    Route::post('/customer/payments', [CustomerController::class, 'store_customer_payment'])->name('customer.payments.store');
    // web.php
    Route::get('/customer/ledger/{id}', [CustomerController::class, 'getCustomerLedger']);
    Route::delete('/customer-payments/{id}', [CustomerController::class, 'destroy_payment'])->name('customer.payments.destroy');

    // Vendor Routes
    Route::get('/vendor', [VendorController::class, 'index']);
    Route::post('/vendor/store', [VendorController::class, 'store'])->name('vendors.store.ajax');
    Route::get('/vendor/delete/{id}', [VendorController::class, 'delete']);
    Route::get('/vendors-ledger', [VendorController::class, 'vendors_ledger'])->name('vendors-ledger');
    Route::get('/vendor/payments', [VendorController::class, 'vendor_payments'])->name('vendor.payments');
    Route::post('/vendor/payments', [VendorController::class, 'store_vendor_payment'])->name('vendor.payments.store');
    Route::get('/vendor/bilties', [VendorController::class, 'vendor_bilties'])->name('vendor.bilties');
    Route::post('/vendor/bilties', [VendorController::class, 'store_vendor_bilty'])->name('vendor.bilties.store');

    // Warehouse Routes
    /////
    Route::get('/warehouses/get/', [WarehouseController::class, 'getWarehouses'])->name('warehouses.get');

    /////
    Route::get('/warehouse', [WarehouseController::class, 'index']);
    Route::post('/warehouse/store', [WarehouseController::class, 'store']);
    Route::get('/warehouse/delete/{id}', [WarehouseController::class, 'delete']);

    // Branches
    Route::resource('branch', BranchController::class)->names('branch')->only(['index', 'store']);
    Route::get('/branch/delete/{id}', [BranchController::class, 'delete'])->name('branch.delete');




    Route::middleware(['role:super admin|admin'])->group(function () {
    // Roles
    Route::resource('roles', RoleController::class)->names('roles')->only(['index', 'store']);
    Route::get('/roles/delete/{id}', [RoleController::class, 'delete'])->name('roles.delete')->middleware('permission:delete role');
    Route::post('/admin/roles/update-permission', [RoleController::class, 'updatePermissions'])->name('roles.update.permission');

    // Permissions
    Route::resource('permissions', PermissionController::class)->names('permissions')->only(['index', 'store']);
    Route::get('/permissions/delete/{id}', [PermissionController::class, 'delete'])->name('permission.delete')->middleware('permission:delete role');;

    // Users
    Route::resource('users', UserController::class)->names('users')->only(['index', 'store']);
    Route::get('/users/delete/{id}', [UserController::class, 'delete'])->name('users.delete')->middleware('permission:delete role');;
    Route::post('/admin/users/update-roles', [UserController::class, 'updateRoles'])->name('users.update.roles');
    });
    // Route::put('/users/{id}/roles', [UserController::class, 'updateRoles'])->name('users.update.roles');

    // Zone
    Route::get('zone', [ZoneController::class, 'index'])->name('zone.index');
    Route::post('zones/store', [ZoneController::class, 'store'])->name('zone.store');
    Route::get('zones/edit/{id}', [ZoneController::class, 'edit'])->name('zone.edit');
    Route::get('zones/delete/{id}', [ZoneController::class, 'destroy'])->name('zone.delete');

    // Sales Officer
    Route::get('sales-officers', [SalesOfficerController::class, 'index'])->name('sales.officer.index');
    Route::post('sales-officers/store', [SalesOfficerController::class, 'store'])->name('sales-officer.store');
    Route::get('sales-officers/edit/{id}', [SalesOfficerController::class, 'edit'])->name('sales.officer.edit');
    Route::delete('sales-officers/{id}', [SalesOfficerController::class, 'destroy'])->name('sales-officer.delete');

    // products

    route::get('/Purchase', [PurchaseController::class, 'index'])->name('Purchase.home');
    route::get('/add/Purchase', [PurchaseController::class, 'add_purchase'])->name('add_purchase');
    route::post('/Purchase/stote', [PurchaseController::class, 'store'])->name('store.Purchase');
    Route::get('/purchase/{id}/edit', [PurchaseController::class, 'edit'])->name('purchase.edit');
    Route::put('/purchase/{id}', [PurchaseController::class, 'update'])->name('purchase.update');
    Route::delete('/purchase/{id}', [PurchaseController::class, 'destroy'])->name('purchase.destroy');
    Route::post('/search_products', [ProductController::class, 'searchProducts'])->name('search_products');
    Route::get('/purchase/{id}/invoice', [PurchaseController::class, 'Invoice'])->name('purchase.invoice');

    Route::get('purchase/return', [PurchaseController::class, 'purchaseReturnIndex'])->name('purchase.return.index');
    Route::get('purchase/return/{id}', [PurchaseController::class, 'showReturnForm'])->name('purchase.return.show');
    Route::post('purchase/return/store', [PurchaseController::class, 'storeReturn'])->name('purchase.return.store');
    Route::get('/getPartyList', [PurchaseController::class, 'getPartyList'])->name('party.list');
    // Inward Gatepass Routes
    Route::get('/InwardGatepass', [InwardgatepassController::class, 'index'])->name('InwardGatepass.home');
    Route::get('/add/InwardGatepass', [InwardgatepassController::class, 'create'])->name('add_inwardgatepass');
    Route::post('/InwardGatepass/store', [InwardgatepassController::class, 'store'])->name('store.InwardGatepass');
    Route::get('/InwardGatepass/{id}', [InwardgatepassController::class, 'show'])->name('InwardGatepass.show');

    // edit/update/delete abhi comment kiye hue hain
    Route::get('/InwardGatepass/{id}/edit', [InwardgatepassController::class, 'edit'])->name('InwardGatepass.edit');
    Route::put('/InwardGatepass/{id}', [InwardgatepassController::class, 'update'])->name('InwardGatepass.update');
    Route::get('/inward-gatepass/{id}/pdf', [InwardgatepassController::class, 'pdf'])->name('InwardGatepass.pdf');

    Route::delete('/InwardGatepass/{id}', [InwardgatepassController::class, 'destroy'])->name('InwardGatepass.destroy');
    // Products search
    Route::get('/search-products', [InwardgatepassController::class, 'searchProducts'])->name('search-products');

    // Show Add Bill Form
    Route::get('inward-gatepass/{id}/add-bill', [PurchaseController::class, 'addBill'])->name('add_bill');
    // Store Bill
    Route::post('inward-gatepass/{id}/store-bill', [PurchaseController::class, 'store'])->name('store.bill');
    // Purchase Return Routes

    // Route::get('/fetch-product', [PurchaseController::class, 'fetchProduct'])->name('item.search');
    // Route::post('/fetch-item-details', [PurchaseController::class, 'fetchItemDetails']);
    // Route::get('/Purchase/create', function () {
    //     return view('admin_panel.purchase.add_purchase');
    // });
    // Route::get('/get-items-by-category/{categoryId}', [PurchaseController::class, 'getItemsByCategory'])->name('get-items-by-category');
    Route::get('/get-product-details/{id}', [ProductController::class, 'getProductDetails'])->name('get-product-details');

    // Route::get('booking/system', [SaleController::class,'booking-system'])->name('booking.index');
    Route::get('sale', [SaleController::class, 'index'])->name('sale.index');
    Route::get('sale/create', [SaleController::class, 'addsale'])->name('sale.add');
    Route::get('/products/search', [SaleController::class, 'searchProducts'])->name('products.search');
    Route::get('/search-product-name', [SaleController::class, 'searchpname'])->name('search-product-name');
    Route::post('/sales/store', [SaleController::class, 'store'])->name('sales.store');
    Route::get('/sales/{id}/return', [SaleController::class, 'saleretun'])->name('sales.return.create');
    Route::post('/sales-return/store', [SaleController::class, 'storeSaleReturn'])->name('sales.return.store');
    Route::get('/sale-returns', [App\Http\Controllers\SaleController::class, 'salereturnview'])->name('sale.returns.index');
    // Route::get('/sales/{id}/invoice', [SaleController::class, 'saleinvoice'])->name('sales.invoice');
    Route::get('/sales/{id}/edit', [SaleController::class, 'saleedit'])->name('sales.edit');
    Route::put('/sales/{id}', [SaleController::class, 'updatesale'])->name('sales.update');
    Route::get('/sales/{id}/dc', [SaleController::class, 'saledc'])->name('sales.dc');
    Route::get('/sales/{id}/recepit', [SaleController::class, 'salerecepit'])->name('sales.recepit');
// AJAX (no refresh)
    Route::post('/sale/ajax/save', [SaleController::class, 'ajaxSave'])->name('sale.ajax.save');
    Route::get('/sale/ajax/post', [SaleController::class, 'ajaxPost'])->name('sale.ajax.post');
    Route::get('/sale/invoice/{booking}', [SaleController::class, 'invoice'])
    ->name('booking.invoice');
// routes/web.php
// Route::get('get-warehouses/{product_id}', 
//     [SaleController::class, 'getWarehousesByProducts']
// )->name('sale.warehouses.by.products');
Route::get('/get-warehouses', [SaleController::class, 'getWarehousesByProducts']);

        // Prints
    Route::get('/booking/dc/{booking}', [SaleController::class, 'bookingDc'])->name('booking.dc');
    // Route::get('/booking/dc/{id}', function(){

    // });
    Route::get('/sale/invoice/{sale}', [SaleController::class, 'invoice'])->name('sale.invoice');
    // Route::get('/sale/invoice',function(){
    //     return view('admin_panel.sale.invoice2');
    // });
    // Route::get('/sale/print2/{sale}', [SaleController::class, 'print2'])->name('sale.print2');
    // Route::get('/sale/dc/{sale}', [SaleController::class, 'dc'])->name('sale.dc');
    // booking system

    Route::get('bookings', [ProductBookingController::class, 'index'])->name('bookings.index');
    Route::get('bookings/create', [ProductBookingController::class, 'create'])->name('bookings.create');
    Route::post('bookings/store', [ProductBookingController::class, 'store'])->name('bookings.store');
    Route::get('booking/receipt/{id}', [ProductBookingController::class, 'receipt'])->name('booking.receipt');
    Route::get('/sales/from-booking/{id}', [SaleController::class, 'convertFromBooking'])->name('sales.from.booking');

    // web.php
    Route::get('/warehouse-stock-quantity', [StockTransferController::class, 'getStockQuantity'])->name('warehouse.stock.quantity');


    Route::get('/get-customers-by-type', [CustomerController::class, 'getByType']);
    Route::resource('warehouse_stocks', WarehouseStockController::class);
    Route::resource('stock_transfers', StockTransferController::class);
    ////////////
    Route::get('/get-stock/{product}', [StocksController::class, 'getStock'])
        ->name('get.stock');
    //////////
    // narratiions
    Route::resource('narrations', NarrationController::class)->only(['index', 'store', 'destroy']);
    Route::get('vouchers/{type}', [VoucherController::class, 'index'])->name('vouchers.index');
    Route::post('vouchers/store', [VoucherController::class, 'store'])->name('vouchers.store');
    Route::get('/view_all', [AccountsHeadController::class, 'index'])->name('view_all');
    Route::get('/get-vendor-balance/{id}', [VendorController::class, 'getVendorBalance']);
    ///// Recipt Vouchers
    Route::get('/receipt-voucher/print/{id}', [VoucherController::class, 'print'])->name('receiptVoucher.print');
    Route::get('/get-accounts-by-head/{headId}', [VoucherController::class, 'getAccountsByHead']);
    Route::get('/get-opening-balance/{type}/{id}', [VoucherController::class, 'getOpeningBalance']);


    Route::get('/all-recepit-vochers', [VoucherController::class, 'all_recepit_vochers'])->name('all-recepit-vochers');
    Route::get('/recepit-vochers', [VoucherController::class, 'recepit_vochers'])->name('recepit-vochers');
    Route::post('/recepit/vochers/stote', [VoucherController::class, 'store_rec_vochers'])->name('recepit.vochers.store');
    ////// payment vouchers
    Route::get('/Payment-vochers', [VoucherController::class, 'Payment_vochers'])->name('Payment-vochers');
route::post('/Payment/vochers/stote', [VoucherController::class, 'store_Pay_vochers'])->name('Payment.vochers.store');
Route::get('/all-Payment-vochers', [VoucherController::class, 'all_Payment_vochers'])->name('all-Payment-vochers');
Route::get('/Payment-voucher/print/{id}', [VoucherController::class, 'Paymentprint'])->name('PaymentVoucher.print');
    ////// expense voucher 
    Route::get('/all-expense-vochers', [VoucherController::class, 'all_expense_vochers'])->name('all-expense-vochers');
Route::get('/expense-vochers', [VoucherController::class, 'expense_vochers'])->name('expense-vochers');
route::post('/expense/vochers/stote', [VoucherController::class, 'store_expense_vochers'])->name('expense.vochers.store');
Route::get('/expense-voucher/print/{id}', [VoucherController::class, 'expenseprint'])->name('expenseVoucher.print');
    // reporting routes

    Route::get('/report/item-stock', [ReportingController::class, 'item_stock_report'])->name('report.item_stock');
    Route::post('/report/item-stock-fetch', [ReportingController::class, 'fetchItemStock'])->name('report.item_stock.fetch');

    Route::get('report/purchase', [ReportingController::class, 'purchase_report'])->name('report.purchase');
    Route::post('report/purchase/fetch', [ReportingController::class, 'fetchPurchaseReport'])->name('report.purchase.fetch');

    Route::get('report/sale', [ReportingController::class, 'sale_report'])->name('report.sale');
    Route::get('report/sale/fetch', [ReportingController::class, 'fetchsaleReport'])->name('report.sale.fetch');

    Route::get('report/customer/ledger', [ReportingController::class, 'customer_ledger_report'])->name('report.customer.ledger');
    Route::get('report/customer-ledger/fetch', [ReportingController::class, 'fetch_customer_ledger'])->name('report.customer.ledger.fetch');

    Route::get('reports/onhand', [ReportingController::class, 'onhand'])->name('reports.onhand');
    // reports
    Route::prefix('coa')->group(function () {
        Route::get('/', [AccountsHeadController::class, 'index'])->name('coa.index');
        Route::post('/head', [AccountsHeadController::class, 'storeHead'])->name('coa.head.store');
        Route::post('/account', [AccountsHeadController::class, 'storeAccount'])->name('coa.account.store');
    });
});
require __DIR__ . '/auth.php';
