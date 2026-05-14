<?php
include 'vendor/autoload.php';
$app = include_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

$barcodes = Product::whereNotNull('barcode_path')->orderBy('id', 'desc')->take(10)->pluck('barcode_path');
print_r($barcodes->toArray());
