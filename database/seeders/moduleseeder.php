<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $modules = [
            
    ["id" => 49, "module_name" => "report.vendor.ledger"],
    ["id" => 48, "module_name" => "Branch Wise Permission"],
    ["id" => 47, "module_name" => "find Dc"],
    ["id" => 46, "module_name" => "generate Dc"],
    ["id" => 45, "module_name" => "warehouse.orders"],
    ["id" => 44, "module_name" => "general"],

    ["id" => 43, "module_name" => "branch"],
    ["id" => 42, "module_name" => "permission"],
    ["id" => 41, "module_name" => "role.permission"],
    ["id" => 40, "module_name" => "role"],
    ["id" => 39, "module_name" => "user"],
    ["id" => 38, "module_name" => "report.inventory.onhand"],
    ["id" => 37, "module_name" => "report.assembly"],
    ["id" => 36, "module_name" => "report.customer.ledger"],
    ["id" => 35, "module_name" => "report.sale"],
    ["id" => 34, "module_name" => "report.purchase"],
    ["id" => 33, "module_name" => "report.item.stock"],
    ["id" => 32, "module_name" => "narration"],
    ["id" => 31, "module_name" => "chart.of.accounts"],
    ["id" => 30, "module_name" => "journal.voucher"],
    ["id" => 29, "module_name" => "expense.voucher"],
    ["id" => 28, "module_name" => "payment.voucher"],
    ["id" => 27, "module_name" => "receipts.voucher"],
    ["id" => 26, "module_name" => "voucher"],
    ["id" => 25, "module_name" => "booking"],
    ["id" => 24, "module_name" => "zone"],
    ["id" => 23, "module_name" => "sales.officer"],
    ["id" => 22, "module_name" => "customer.toggle"],
    ["id" => 21, "module_name" => "customer.payments"],
    ["id" => 20, "module_name" => "customer"],
    ["id" => 19, "module_name" => "sale.return"],
    ["id" => 18, "module_name" => "sale.delivery"],
    ["id" => 17, "module_name" => "sale"],
    ["id" => 16, "module_name" => "vendor.bilties"],
    ["id" => 15, "module_name" => "vendor.payments"],
    ["id" => 14, "module_name" => "vendor"],
    ["id" => 13, "module_name" => "stock"],
    ["id" => 12, "module_name" => "stock.transfer"],
    ["id" => 11, "module_name" => "warehouse.stock"],
    ["id" => 10, "module_name" => "warehouse"],
    ["id" => 9, "module_name" => "inward.gatepass"],
    ["id" => 8, "module_name" => "purchase.return"],
    ["id" => 7, "module_name" => "purchase"],
    ["id" => 6, "module_name" => "unit"],
    ["id" => 5, "module_name" => "brand"],
    ["id" => 4, "module_name" => "subcategory"],
    ["id" => 3, "module_name" => "category"],
    ["id" => 2, "module_name" => "product.discount"],
    ["id" => 1, "module_name" => "product"],
    
    
];
  

        foreach ($modules as &$module) {
            $module['created_at'] = $now;
            $module['updated_at'] = $now;
        }

        // Use insertOrIgnore to avoid duplicate primary key errors when seeding repeatedly
        DB::table('modules')->insertOrIgnore($modules);
    }
}