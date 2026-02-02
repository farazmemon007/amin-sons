# Notification System Implementation Guide

## 📋 Overview

A complete notification system has been implemented where:
1. **User enters number of days** (15, 20, 30, etc.) in the "Notify Me" field
2. **System calculates notification date** = Today's date + entered days
3. **Notification is saved** to the database with all relevant details
4. **You can query notifications** and send reminders to customers

---

## 📊 Notifications Table Schema

The `notifications` table has been created with the following columns:

```
┌─ ID (Primary Key)
├─ booking_id (FK → productbookings)
├─ sale_id (FK → sales)
├─ customer_id (FK → customers)
│
├─ type (VARCHAR) - e.g., 'booking_payment', 'sale_payment'
├─ title (VARCHAR) - e.g., 'Payment Reminder - INVSLE-0001'
├─ description (TEXT) - Detailed message
│
├─ notification_date (DATE) - When to trigger (today + days)
├─ sent_at (DATETIME) - When it was actually sent (NULL until sent)
│
├─ status (ENUM: pending, sent, dismissed) - Current state
├─ is_read (BOOLEAN) - Whether customer/admin has read it
│
├─ created_by (FK → users) - Who created the notification
│
├─ created_at (DATETIME)
├─ updated_at (DATETIME)
```

### Indexes for Performance:
- `notification_date` - For querying notifications by date
- `status` - For filtering pending/sent notifications
- `customer_id` - For customer-specific notifications

---

## 🔧 How It Works

### Step 1: User Enters Days in Form
```html
<!-- resources/views/admin_panel/sale/add_sale222.blade.php -->
<input type="number" name="notify_me" id="notify_me"  
       class="form-control form-control-sm" 
       placeholder="Enter payment Expected days" 
       min="0" max="365" value="">
```

### Step 2: Form Submits to Controller
The `SaleController@ajaxPost()` method processes the booking and creates a notification:

```php
// Extracting from booking
if (!empty($booking->notify_me) && $booking->notify_me > 0) {
    // Calculate notification date
    $notificationDate = Carbon::today()->addDays($booking->notify_me);
    
    // Create notification record
    Notification::create([
        'booking_id' => $booking->id,
        'sale_id' => $sale->id,
        'customer_id' => $booking->customer_id,
        'type' => 'booking_payment',
        'title' => 'Payment Reminder - ' . $booking->invoice_no,
        'description' => 'Payment reminder for booking ' . $booking->invoice_no . ' (Amount: ' . $sale->total_net . ')',
        'notification_date' => $notificationDate,  // ✅ Today + Days
        'status' => 'pending',
        'created_by' => auth()->id(),
    ]);
}
```

### Step 3: Notification is Stored
The notification waits in the database until the `notification_date` arrives.

---

## 💻 Notification Model

The `Notification` model has been updated with:

### Properties
```php
class Notification extends Model {
    protected $fillable = [
        'booking_id', 'sale_id', 'customer_id',
        'type', 'title', 'description',
        'notification_date', 'sent_at', 'status',
        'is_read', 'created_by'
    ];

    protected $casts = [
        'notification_date' => 'date',
        'sent_at' => 'datetime',
        'is_read' => 'boolean',
    ];
}
```

### Relationships
```php
$notification->booking();      // Get related booking
$notification->sale();         // Get related sale
$notification->customer();     // Get customer
$notification->createdBy();    // Get who created it
```

### Query Scopes (Helper Methods)
```php
// Get all pending notifications
Notification::pending()->get();

// Get all sent notifications
Notification::sent()->get();

// Get unread notifications
Notification::unread()->get();

// Get notifications for today
Notification::forToday()->get();

// Get overdue notifications (not yet sent)
Notification::overdue()->get();
```

---

## 🔍 How to Use Notifications

### 1. **Get Notifications Due Today**
```php
$todaysNotifications = Notification::forToday()
    ->where('status', 'pending')
    ->get();

foreach ($todaysNotifications as $notif) {
    // Send email/SMS to customer
    // Mark as sent: $notif->update(['status' => 'sent', 'sent_at' => now()]);
}
```

### 2. **Get All Pending Notifications**
```php
$pendingNotifications = Notification::pending()->get();

// Filter by customer
$customerNotifications = Notification::where('customer_id', $customerId)
    ->pending()
    ->get();
```

### 3. **Get Overdue Notifications (Past notification date but not sent)**
```php
$overdueNotifications = Notification::overdue()->get();
// These should have been sent but weren't - you may need follow-up
```

### 4. **Mark Notification as Sent**
```php
$notification = Notification::find($id);
$notification->update([
    'status' => 'sent',
    'sent_at' => now(),
]);
```

### 5. **Mark as Read**
```php
$notification->update(['is_read' => true]);
```

### 6. **Get Notifications for a Specific Customer**
```php
$customerNotifications = Notification::where('customer_id', $customerId)
    ->orderBy('notification_date', 'asc')
    ->get();
```

---

## 📱 Example Scenarios

### Scenario 1: User Books with 15 Days Reminder
- **Today**: January 31, 2026
- **User enters**: 15 days
- **Notification Date**: February 15, 2026
- **Action**: On Feb 15, system should send reminder email/SMS

### Scenario 2: User Books with 30 Days Reminder
- **Today**: January 31, 2026
- **User enters**: 30 days
- **Notification Date**: March 2, 2026
- **Action**: On March 2, customer gets payment reminder

---

## 🛠️ Creating a Notification Service (Optional)

For automatic reminders, create a Laravel Artisan command:

```php
// Create: app/Console/Commands/SendNotifications.php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;

class SendNotifications extends Command
{
    protected $signature = 'notifications:send';
    protected $description = 'Send pending notifications due today';

    public function handle()
    {
        $notifications = Notification::forToday()
            ->where('status', 'pending')
            ->get();

        foreach ($notifications as $notif) {
            try {
                // Send email, SMS, or push notification
                // Email::send(new PaymentReminderMail($notif));
                
                $notif->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
                
                $this->info("Sent notification: {$notif->title}");
            } catch (\Exception $e) {
                $this->error("Failed to send: {$e->getMessage()}");
            }
        }
    }
}
```

Schedule it in `app/Console/Kernel.php`:
```php
$schedule->command('notifications:send')->daily();
```

---

## 📊 Database Examples

### Query: Get all notifications for a customer
```sql
SELECT * FROM notifications 
WHERE customer_id = 5 
ORDER BY notification_date ASC;
```

### Query: Get pending notifications due today or overdue
```sql
SELECT * FROM notifications 
WHERE notification_date <= CURDATE() 
  AND status = 'pending'
ORDER BY notification_date ASC;
```

### Query: Get notifications by status
```sql
SELECT status, COUNT(*) as count 
FROM notifications 
GROUP BY status;
```

---

## 🎯 Implementation Checklist

✅ **Completed:**
- [x] Notifications table created with migration
- [x] Notification model with relationships and scopes
- [x] SaleController updated to create notifications when booking is posted
- [x] Logic to calculate `notification_date = today + notify_me days`

📌 **Next Steps (Optional):**
- [ ] Create a Notification dashboard view to display pending reminders
- [ ] Create a Laravel command to send daily reminders
- [ ] Add email notifications using Laravel Mail
- [ ] Add SMS notifications using Twilio/etc
- [ ] Create admin dashboard to manage notifications

---

## 🚀 Quick Reference Commands

```bash
# View notifications in database
php artisan tinker
> Notification::all();

# Get pending notifications
> Notification::pending()->get();

# Get notifications due today
> Notification::forToday()->get();

# Get customer-specific notifications
> Notification::where('customer_id', 1)->pending()->get();

# Mark as sent
> $n = Notification::find(1);
> $n->update(['status' => 'sent', 'sent_at' => now()]);
```

---

## 📝 Notes

- The `notify_me` field accepts 0-365 days
- If `notify_me = 0`, no notification is created
- Notifications are created when booking is **posted** (not when saved as draft)
- Each notification is linked to both `booking_id` and `sale_id` for reference
- The system supports multiple types: `booking_payment`, `sale_payment`, etc.

