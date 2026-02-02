<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'Dashboard',
            'Products',
            'Discount Products',
            'Category',
            'Sub Category',
            'Brands',
            'Units',
            'Parts Adjust',
            'Inward Gatepass',
            'Add Inward Gatepass',
            'Purchase',
            'Vendor',
            'Warehouse',
            'Warehouse Stock',
            'Stock Transfer',
            'Sales',
            'Customer',
            'Sales Officer',
            'Zone',
            'Chart Of Accounts',
            'Narrations',
            'Receipts Voucher',
            'Payment Voucher',
            'Expense Voucher',
            'Journal Voucher',
            'Item Stock Report',
            'Purchase Report',
            'Sale Report',
            'Customer Ledger',
            'Assembly Report',
            'Inventory On-Hand',
            'Users',
            'Roles',
            'Permissions',
            'Branches',
            'booking'
        ];

        foreach ($modules as $module) {
            DB::table('modules')->updateOrInsert(
                ['module_name' => $module]
            );
        }
    }
}
