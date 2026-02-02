#!/usr/bin/env php
<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test the new getAllNotifications endpoint
$controller = new \App\Http\Controllers\NotificationController();
$response = $controller->getAllNotifications();
$data = json_decode($response->content(), true);

echo "=== TESTING NEW ENDPOINT ===\n";
echo "Success: " . ($data['success'] ? 'YES' : 'NO') . "\n";
echo "Total notifications: " . count($data['notifications'] ?? []) . "\n";

if (!empty($data['notifications'])) {
    $pending = array_filter($data['notifications'], fn($n) => $n['status'] === 'pending');
    $sent = array_filter($data['notifications'], fn($n) => $n['status'] === 'sent');
    $dismissed = array_filter($data['notifications'], fn($n) => $n['status'] === 'dismissed');
    
    echo "\nBreakdown:\n";
    echo "  Pending: " . count($pending) . "\n";
    echo "  Sent: " . count($sent) . "\n";
    echo "  Dismissed: " . count($dismissed) . "\n";
    
    echo "\nNotifications:\n";
    foreach ($data['notifications'] as $n) {
        echo "  - {$n['title']} ({$n['status']})\n";
    }
}

echo "\n=== END TEST ===\n";
