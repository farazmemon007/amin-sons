# Notification System - Quick Tinker Examples

Open Tinker with: `php artisan tinker`

## 📋 Basic Queries

### 1. View All Notifications
```php
Notification::all()

// Output: Collection of all notifications
```

### 2. View Notifications with Relations
```php
Notification::with(['booking', 'sale', 'customer', 'createdBy'])->get()

// Shows notification + related booking/sale/customer/user info
```

### 3. Count Notifications by Status
```php
Notification::groupBy('status')->selectRaw('status, count(*) as total')->get()

// Example Output:
// [
//   ['status' => 'pending', 'total' => 5],
//   ['status' => 'sent', 'total' => 2],
// ]
```

---

## 🔍 Filtering Queries

### Get Pending Notifications (Not yet sent)
```php
Notification::pending()->get()

// Equivalent to: where('status', 'pending')
```

### Get Sent Notifications
```php
Notification::sent()->get()

// Equivalent to: where('status', 'sent')
```

### Get Unread Notifications
```php
Notification::unread()->get()

// Equivalent to: where('is_read', false)
```

### Get Notifications Due Today
```php
Notification::forToday()->get()

// Checks: whereDate('notification_date', today())
```

### Get Overdue Notifications
```php
Notification::overdue()->get()

// Checks: whereDate('notification_date', '<', today())
//         AND status != 'sent'
```

### Combine Multiple Filters
```php
Notification::pending()
    ->where('customer_id', 5)
    ->get()

// Get all pending notifications for customer ID 5
```

---

## 👤 Customer-Specific Queries

### Get All Notifications for a Customer
```php
Notification::where('customer_id', 5)->get()

// Show all notifications (pending, sent, dismissed) for customer 5
```

### Get Pending Notifications for Customer
```php
Notification::where('customer_id', 5)
    ->pending()
    ->get()
```

### Get Unread Notifications for Customer
```php
Notification::where('customer_id', 5)
    ->unread()
    ->get()
```

### Get Customer's Notification History
```php
Notification::where('customer_id', 5)
    ->orderBy('notification_date', 'desc')
    ->get()

// Most recent first
```

---

## 📅 Date-Based Queries

### Get Notifications for Specific Date
```php
Notification::whereDate('notification_date', '2026-02-15')->get()

// Get notifications due on Feb 15, 2026
```

### Get Notifications in a Date Range
```php
Notification::whereBetween('notification_date', ['2026-02-01', '2026-02-28'])->get()

// All notifications in February 2026
```

### Get Upcoming Notifications (Next 7 Days)
```php
Notification::whereBetween('notification_date', [
    now()->toDateString(),
    now()->addDays(7)->toDateString()
])->get()

// Notifications for the next week
```

### Get Past Notifications (Historical)
```php
Notification::where('notification_date', '<', now()->toDateString())->get()

// Notifications that should have been sent already
```

---

## 🔔 Notification Lifecycle

### Create a New Notification Manually
```php
Notification::create([
    'booking_id' => 5,
    'sale_id' => 12,
    'customer_id' => 3,
    'type' => 'booking_payment',
    'title' => 'Payment Reminder - INVSLE-0001',
    'description' => 'Payment reminder for booking INVSLE-0001 (Amount: 50000.00)',
    'notification_date' => '2026-02-15',
    'status' => 'pending',
    'created_by' => 1,
])

// Output: Notification object with ID assigned
```

### Update Notification Status
```php
$notif = Notification::find(1)

// Mark as Sent
$notif->update([
    'status' => 'sent',
    'sent_at' => now(),
])

// Mark as Read
$notif->update(['is_read' => true])

// Mark as Dismissed
$notif->update(['status' => 'dismissed'])
```

### Delete Notification
```php
Notification::find(1)->delete()

// Remove notification from database
```

---

## 📊 Reporting & Analytics

### Count by Status
```php
Notification::select('status')
    ->selectRaw('count(*) as count')
    ->groupBy('status')
    ->get()

// Output: Count of pending, sent, dismissed
```

### Count by Type
```php
Notification::select('type')
    ->selectRaw('count(*) as count')
    ->groupBy('type')
    ->get()

// Output: Count by notification type
```

### Count by Customer
```php
Notification::select('customer_id')
    ->selectRaw('count(*) as count')
    ->groupBy('customer_id')
    ->orderByRaw('count(*) desc')
    ->get()

// Shows which customers have most notifications
```

### Get Sending Statistics
```php
Notification::selectRaw('
    status,
    DATE(notification_date) as date,
    count(*) as count
')
->groupBy('status', 'date')
->orderBy('date', 'desc')
->get()

// Break down by date and status
```

---

## 🔗 Relationship Queries

### Get Notification with Booking Details
```php
$notif = Notification::find(1)
$notif->booking    // Get related Productbooking
$notif->sale       // Get related Sale
$notif->customer   // Get related Customer
$notif->createdBy  // Get who created it (User)
```

### Get Customer's Notifications with Details
```php
Notification::where('customer_id', 5)
    ->with(['booking', 'sale', 'customer'])
    ->get()

// Loads related data in one query (optimized)
```

### Get All Notifications for a Booking
```php
$booking = Productbooking::find(5)
$notifications = Notification::where('booking_id', $booking->id)->get()

// Or via relationship (if you add it to model):
$notifications = $booking->notifications()->get()
```

### Get All Notifications for a Sale
```php
$sale = Sale::find(12)
$notifications = Notification::where('sale_id', $sale->id)->get()

// Shows all reminders linked to this sale
```

---

## 📈 Real-World Examples

### Example 1: Send Reminders for Today
```php
// Get notifications due today that are pending
$todays = Notification::forToday()
    ->pending()
    ->with(['customer', 'booking'])
    ->get()

foreach ($todays as $notif) {
    // Send email
    Mail::send(new PaymentReminderMail($notif))
    
    // Mark as sent
    $notif->update([
        'status' => 'sent',
        'sent_at' => now(),
    ])
    
    echo "Sent reminder to {$notif->customer->customer_name}\n"
}

// Output: "Sent reminder to Ahmad Khan"
//         "Sent reminder to Fatima Ali"
```

### Example 2: Check for Overdue Reminders
```php
// Reminders that should have been sent but weren't
$overdue = Notification::overdue()
    ->with('customer')
    ->get()

if ($overdue->count() > 0) {
    echo "⚠️  {$overdue->count()} overdue reminders!\n"
    
    foreach ($overdue as $notif) {
        echo "- {$notif->title} (Due: {$notif->notification_date})\n"
    }
}
```

### Example 3: Customer Notification Report
```php
// Get detailed report for one customer
$customer_id = 5
$notifications = Notification::where('customer_id', $customer_id)
    ->orderBy('notification_date', 'desc')
    ->get()

echo "=== Notifications for Customer {$customer_id} ===\n"
foreach ($notifications as $n) {
    echo "\n✓ {$n->title}"
    echo "\n  Date: {$n->notification_date}"
    echo "\n  Status: {$n->status}"
    echo "\n  Sent: {$n->sent_at}\n"
}
```

### Example 4: Mark All Today's Notifications as Sent
```php
Notification::forToday()
    ->pending()
    ->update([
        'status' => 'sent',
        'sent_at' => now(),
    ])

echo "Updated all today's pending notifications to sent"
```

### Example 5: Get Customer with Pending Reminders
```php
// Find all customers with pending reminders
$customersWithPending = Notification::pending()
    ->select('customer_id')
    ->distinct()
    ->get()
    ->map->customer

foreach ($customersWithPending as $customer) {
    echo "{$customer->customer_name}: {$customer->notifications()->pending()->count()} pending\n"
}

// Output:
// Ahmad Khan: 2 pending
// Fatima Ali: 1 pending
```

---

## ⚡ Performance Tips

### Optimize Large Queries
```php
// ❌ BAD - Loads all notifications
Notification::all()

// ✅ GOOD - Load only what you need
Notification::select(['id', 'title', 'status', 'notification_date'])
    ->where('status', 'pending')
    ->get()
```

### Use Eager Loading
```php
// ❌ BAD - N+1 query problem
$notifications = Notification::all()
foreach ($notifications as $n) {
    echo $n->customer->customer_name  // Extra query each time!
}

// ✅ GOOD - Load relationships together
$notifications = Notification::with('customer')->get()
foreach ($notifications as $n) {
    echo $n->customer->customer_name  // No extra queries
}
```

### Paginate Results
```php
// ✅ GOOD - Load 15 at a time
$notifications = Notification::paginate(15)

// In view:
{{ $notifications->links() }}
```

---

## 🧹 Cleanup & Maintenance

### Delete Old Sent Notifications (90 days old)
```php
Notification::sent()
    ->where('sent_at', '<', now()->subDays(90))
    ->delete()

echo "Deleted old notifications"
```

### Delete Dismissed Notifications
```php
Notification::where('status', 'dismissed')->delete()

echo "Cleaned up dismissed notifications"
```

### Archive Notifications (Copy to History)
```php
// Copy old notifications to history table
$old = Notification::where('created_at', '<', now()->subMonths(6))->get()

foreach ($old as $n) {
    NotificationHistory::create($n->toArray())
}

echo "Archived {$old->count()} notifications"
```

---

## 📞 Tips

- Always use `Notification::with(['customer', 'booking'])` to load relations
- Use scopes like `.pending()` for cleaner, more readable queries
- Check `notification_date` vs `sent_at` - one is when to send, one is when it was sent
- Use `whereDate()` for date comparisons (ignores time portion)

