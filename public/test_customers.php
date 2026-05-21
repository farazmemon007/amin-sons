<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$customers = \App\Models\Customer::select('id', 'customer_name', 'customer_type', 'branch_id')->get();
echo json_encode($customers);
