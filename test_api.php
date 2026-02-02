<?php
require 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Http\Request;

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Simulate the API call
$controller = new \App\Http\Controllers\NotificationController();
$response = $controller->getAllNotifications();

echo "========== API RESPONSE ==========\n";
echo "Status Code: " . $response->status() . "\n";
echo "Content-Type: " . $response->headers->get('content-type') . "\n\n";

$data = json_decode($response->content(), true);

echo "Response JSON:\n";
echo "  success: " . ($data['success'] ? 'true' : 'false') . "\n";
echo "  total notifications: " . count($data['notifications'] ?? []) . "\n\n";

if (!empty($data['notifications'])) {
    $pending = array_filter($data['notifications'], fn($n) => $n['status'] === 'pending');
    $sent = array_filter($data['notifications'], fn($n) => $n['status'] === 'sent');
    $dismissed = array_filter($data['notifications'], fn($n) => $n['status'] === 'dismissed');
    
    echo "Breakdown:\n";
    echo "  Pending: " . count($pending) . "\n";
    echo "  Sent: " . count($sent) . "\n";
    echo "  Dismissed: " . count($dismissed) . "\n\n";
    
    echo "First 3 notifications:\n";
    $count = 0;
    foreach ($data['notifications'] as $n) {
        if ($count++ >= 3) break;
        echo "  - ID {$n['id']}: {$n['title']} (status: {$n['status']})\n";
    }
}

echo "\n========== END RESPONSE ==========\n";
