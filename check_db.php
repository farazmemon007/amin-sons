<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$table = 'purchases';
$columns = Schema::getColumnListing($table);

echo "Columns in '$table':\n";
foreach ($columns as $column) {
    echo "- $column\n";
}

$missing = [];
foreach (['purchase_type', 'vendor_name'] as $col) {
    if (!in_array($col, $columns)) {
        $missing[] = $col;
    }
}

if (!empty($missing)) {
    echo "\nMISSING COLUMNS: " . implode(', ', $missing) . "\n";
} else {
    echo "\nAll required columns exist.\n";
}
