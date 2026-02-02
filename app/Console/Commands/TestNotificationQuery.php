<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TestNotificationQuery extends Command
{
    protected $signature = 'test:notification-query';
    protected $description = 'Test notification query with debugging';

    public function handle()
    {
        $this->info("=== TESTING NOTIFICATION QUERY ===\n");

        $today = Carbon::today();
        $this->info("Today: {$today->format('Y-m-d H:i:s')}");
        $this->info("Today (date only): {$today->toDateString()}\n");

        // Test 1: All pending notifications
        $this->info("Test 1: All pending notifications");
        $allPending = Notification::where('status', 'pending')->get();
        $this->line("Total pending: " . $allPending->count());
        foreach ($allPending as $n) {
            $this->line("  ID: {$n->id}, Type: {$n->type}, Date: {$n->notification_date}, Created: {$n->created_at}");
        }

        // Test 2: With whereDate filter
        $this->info("\nTest 2: Pending with whereDate filter (<= today)");
        $filtered = Notification::where('status', 'pending')
            ->whereDate('notification_date', '<=', Carbon::today())
            ->get();
        $this->line("Filtered pending: " . $filtered->count());
        foreach ($filtered as $n) {
            $this->line("  ID: {$n->id}, Type: {$n->type}, Date: {$n->notification_date}");
        }

        // Test 3: Product stock alerts specifically
        $this->info("\nTest 3: Stock alert notifications");
        $stockAlerts = Notification::where('type', 'product_stock_alert')
            ->where('status', 'pending')
            ->get();
        $this->line("Stock alerts: " . $stockAlerts->count());
        foreach ($stockAlerts as $n) {
            $this->line("  ID: {$n->id}, Product: {$n->product_id}, Date: {$n->notification_date}");
        }

        // Test 4: API response simulation
        $this->info("\nTest 4: API Response (simulated getPendingNotifications)");
        $notifications = Notification::where('status', 'pending')
            ->whereDate('notification_date', '<=', Carbon::today())
            ->with(['booking', 'customer', 'product', 'warehouse'])
            ->orderBy('notification_date', 'asc')
            ->get();

        $response = $notifications->map(function ($n) {
            return [
                'id' => $n->id,
                'title' => $n->title,
                'type' => $n->type,
                'notification_date' => $n->notification_date->format('Y-m-d'),
                'customer_name' => $n->customer?->customer_name ?? ($n->product?->item_name ?? 'Unknown'),
                'booking_no' => $n->booking?->invoice_no ?? ($n->product?->item_code ?? 'N/A'),
            ];
        });

        $this->line("Total in response: " . $response->count());
        foreach ($response as $item) {
            $this->line("  - {$item['title']} ({$item['type']})");
        }

        $this->info("\n=== END TEST ===");
    }
}
