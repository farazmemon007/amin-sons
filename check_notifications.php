<?php
require 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Query notifications
$notifications = \App\Models\Notification::with(['booking', 'product', 'customer'])
    ->orderBy('created_at', 'desc')
    ->get();

echo "========== NOTIFICATIONS CHECK ==========\n";
echo "Total in database: " . $notifications->count() . "\n\n";

if ($notifications->count() > 0) {
    echo "Breakdown by status:\n";
    $pending = $notifications->where('status', 'pending')->count();
    $sent = $notifications->where('status', 'sent')->count();
    $dismissed = $notifications->where('status', 'dismissed')->count();
    
    echo "  Pending: $pending\n";
    echo "  Sent: $sent\n";
    echo "  Dismissed: $dismissed\n";
    
    echo "\nDetails:\n";
    foreach ($notifications as $n) {
        $type = $n->type;
        $title = $n->title;
        $status = $n->status;
        echo "  [$status] $type - $title\n";
        echo "      ID: {$n->id}, Created: {$n->created_at}\n";
    }
} else {
    echo "❌ No notifications found in database!\n";
}

echo "\n========== END CHECK ==========\n";
