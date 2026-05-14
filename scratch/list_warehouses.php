<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Warehouse;

$warehouses = Warehouse::all(['id', 'warehouse_name']);
echo json_encode($warehouses);
