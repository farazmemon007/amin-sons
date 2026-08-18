<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\StockRequestController;
use App\Http\Controllers\VoucherInterBranchController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ✅ ERP PROPER - Get branch products for dynamic dropdown
Route::get('branch-products/{branchId}', [StockRequestController::class, 'getBranchProducts'])->name('api.branch-products');

// ✅ ERP PROPER - Inter-Branch Voucher Cascading Account Dropdowns
Route::get('branch-heads/{branchId}', [VoucherInterBranchController::class, 'getBranchHeads'])->name('api.branch-heads');
Route::get('branch-head-accounts/{branchId}/{headId}', [VoucherInterBranchController::class, 'getHeadAccounts'])->name('api.branch-head-accounts');
Route::get('account-balance/{accountId}', [VoucherInterBranchController::class, 'getAccountBalance'])->name('api.account-balance');