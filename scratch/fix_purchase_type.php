<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

try {
    if (!Schema::hasColumn('purchases', 'purchase_type')) {
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('purchase_type')->default('standard')->after('invoice_no');
        });
        echo "Column 'purchase_type' added successfully.\n";
    } else {
        echo "Column 'purchase_type' already exists.\n";
    }

    // Also update existing local purchases (where vendor_id is null and vendor_name is not null)
    $affected = DB::table('purchases')
        ->whereNull('vendor_id')
        ->whereNotNull('vendor_name')
        ->update(['purchase_type' => 'local']);
    
    echo "Updated $affected existing records to 'local' type.\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
