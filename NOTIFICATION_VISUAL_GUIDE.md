# Notification System - Visual Summary

## 🔄 Flow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│  1. USER INTERFACE - Sale Form (add_sale222.blade.php)          │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │  Notify Me (Days - Optional)                              │  │
│  │  [Input: 15] [days]                                       │  │
│  │                                                            │  │
│  │  Supported values: 0-365 days                             │  │
│  │  - If 0: No notification created                          │  │
│  │  - If 15: Notification in 15 days from now               │  │
│  │  - If 30: Notification in 30 days from now               │  │
│  └───────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
                              ↓
                      [Form Submit]
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│  2. BACKEND - SaleController@ajaxPost()                         │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │  ✅ Create Productbooking                                 │  │
│  │  ✅ Create Sale record                                    │  │
│  │  ✅ Update Stock/Warehouse                                │  │
│  │  ✅ Create Customer Ledger                                │  │
│  │  ✅ Process Receipts                                      │  │
│  │                                                            │  │
│  │  🔔 CREATE NOTIFICATION:                                  │  │
│  │     $notificationDate = TODAY + notify_me days            │  │
│  │     Notification::create([                                │  │
│  │         'booking_id' => $booking->id,                    │  │
│  │         'sale_id' => $sale->id,                          │  │
│  │         'customer_id' => $booking->customer_id,          │  │
│  │         'type' => 'booking_payment',                     │  │
│  │         'title' => 'Payment Reminder - INVSLE-0001',    │  │
│  │         'description' => '...',                          │  │
│  │         'notification_date' => $notificationDate, ⭐     │  │
│  │         'status' => 'pending',                           │  │
│  │         'created_by' => auth()->id(),                    │  │
│  │     ])                                                    │  │
│  └───────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
                              ↓
                      [Save to DB]
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│  3. DATABASE - notifications Table                              │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │  id     │ booking_id │ sale_id │ customer_id │ type      │  │
│  │─────────┼────────────┼─────────┼─────────────┼───────────│  │
│  │ 1       │ 5          │ 12      │ 3           │ booking_  │  │
│  │         │            │         │             │ payment   │  │
│  │                                                            │  │
│  │  title              │ notification_date │ status │        │  │
│  │──────────────────────┼──────────────────┼────────│        │  │
│  │ Payment Reminder -   │ 2026-02-15      │ pending│        │  │
│  │ INVSLE-0001          │                  │        │        │  │
│  │                                                            │  │
│  │  sent_at │ is_read │ created_by │ created_at         │  │
│  │──────────┼─────────┼────────────┼──────────────────   │  │
│  │ NULL     │ 0       │ 1          │ 2026-01-31 10:30   │  │
│  └───────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
                              ↓
                    [Waiting until notification_date]
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│  4. REMINDER - On notification_date (e.g., Feb 15)              │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │  Daily Cron Job or Manual Check:                          │  │
│  │                                                            │  │
│  │  $ php artisan notifications:send                         │  │
│  │                                                            │  │
│  │  Finds all notifications where:                           │  │
│  │  - notification_date <= TODAY                            │  │
│  │  - status = 'pending'                                    │  │
│  │                                                            │  │
│  │  Actions:                                                 │  │
│  │  📧 Send Email to customer                                │  │
│  │  📱 Send SMS to customer                                  │  │
│  │  🔔 Send Push Notification                                │  │
│  │  💾 Update: status = 'sent', sent_at = NOW()              │  │
│  └───────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 📊 Notifications Table Details

### Column Definitions:

| Column | Type | Purpose |
|--------|------|---------|
| `id` | BIGINT (PK) | Unique notification ID |
| `booking_id` | BIGINT (FK) | Links to productbookings table |
| `sale_id` | BIGINT (FK) | Links to sales table |
| `customer_id` | BIGINT (FK) | Links to customers table |
| `type` | VARCHAR | Type of notification (e.g., 'booking_payment') |
| `title` | VARCHAR | Short title (e.g., "Payment Reminder - INVSLE-0001") |
| `description` | TEXT | Long message for customer |
| `notification_date` | DATE | **When to send** (today + days) ⭐ |
| `sent_at` | DATETIME | When it was actually sent (NULL if not sent) |
| `status` | ENUM | pending, sent, dismissed |
| `is_read` | BOOLEAN | Has customer read it? (0 = no, 1 = yes) |
| `created_by` | BIGINT (FK) | Who created it (users table) |
| `created_at` | TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | Last update time |

---

## ✨ Example Data

### Input: User enters 15 days
```
Today's Date: January 31, 2026
Notify Me Input: 15
Notification Date: January 31 + 15 days = February 15, 2026
```

### Notification Record Created:
```json
{
  "id": 1,
  "booking_id": 5,
  "sale_id": 12,
  "customer_id": 3,
  "type": "booking_payment",
  "title": "Payment Reminder - INVSLE-0001",
  "description": "Payment reminder for booking INVSLE-0001 (Amount: 50000.00)",
  "notification_date": "2026-02-15",
  "sent_at": null,
  "status": "pending",
  "is_read": false,
  "created_by": 1,
  "created_at": "2026-01-31 10:30:45",
  "updated_at": "2026-01-31 10:30:45"
}
```

### After Sending Reminder:
```json
{
  ...same as above...
  "sent_at": "2026-02-15 09:00:00",
  "status": "sent",
  "is_read": true,
  "updated_at": "2026-02-15 09:00:00"
}
```

---

## 🎯 Usage Scenarios

### Scenario 1: Payment Reminder
```
Day 1 (Jan 31):    Customer makes booking with 15 days payment reminder
Day 15 (Feb 15):   System sends "Your payment of Rs. 50,000 is due"
Expected Action:   Customer makes payment
```

### Scenario 2: Follow-up Reminder
```
Day 1 (Jan 31):    Customer makes booking with 30 days reminder
Day 30 (Mar 2):    System sends "Please confirm delivery of goods"
Expected Action:   Customer confirms or raises issues
```

### Scenario 3: Multiple Reminders (Future)
```
Day 1 (Jan 31):    Customer makes booking with 15 days reminder
Day 15 (Feb 15):   System sends first reminder
Day 20 (Feb 20):   Another system creates follow-up reminder
Expected Action:   Customer pays or contacts support
```

---

## 🔧 Implementation Status

### ✅ Completed
- [x] Notifications migration (table structure)
- [x] Notification model with relationships
- [x] Query scopes (pending(), sent(), unread(), forToday(), overdue())
- [x] SaleController integration - Creates notification when posting booking
- [x] Automatic date calculation (today + notify_me days)

### 📌 Recommended Next Steps
- [ ] Create NotificationController for admin dashboard
- [ ] Create notification view to display pending reminders
- [ ] Create Laravel Artisan command: `notifications:send`
- [ ] Setup email notifications using Laravel Mail
- [ ] Setup SMS notifications using Twilio API
- [ ] Add notification history/log

---

## 💡 Tips & Best Practices

1. **Always Save notify_me Field**
   - Store in both `productbookings.notify_me` AND create notification record

2. **Use Database Indexes**
   - Queries by `notification_date` and `status` are indexed for speed

3. **Handle Time Zones**
   - Use `Carbon::today()` for database dates (timezone-aware)
   - Use `now()` for timestamps

4. **Mark as Sent**
   - Always update `status='sent'` and `sent_at=now()` after sending

5. **Track Unread**
   - Use `is_read` flag to show which customers have seen the reminder

6. **Overdue Notifications**
   - Check `overdue()` scope regularly to catch missed reminders

---

## 🚀 Quick Start Commands

```bash
# 1. Check pending notifications
php artisan tinker
> Notification::pending()->get()

# 2. Get today's notifications
> Notification::forToday()->get()

# 3. Get overdue notifications
> Notification::overdue()->get()

# 4. Mark notification as sent
> $n = Notification::find(1)
> $n->update(['status' => 'sent', 'sent_at' => now()])

# 5. Get customer notifications
> Notification::where('customer_id', 3)->get()
```

---

## 📞 Support

For questions about:
- **Database schema**: See NOTIFICATION_SYSTEM_GUIDE.md
- **Model methods**: Check app/Models/Notification.php
- **Controller logic**: Check app/Http/Controllers/SaleController.php
- **Migration**: Check database/migrations/2026_01_31_create_notifications_table.php

