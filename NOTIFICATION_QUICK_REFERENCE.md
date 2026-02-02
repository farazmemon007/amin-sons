# 🚀 Notification System - Quick Reference Card

## 📋 What It Does
User enters **15-20-30 days** → System calculates **notification date** (today + days) → Creates **database record** → Can send **reminders on that date**

---

## 🗄️ Database Table

```
notifications
├── id (PK)
├── booking_id (FK)
├── sale_id (FK)
├── customer_id (FK)
├── type (VARCHAR) e.g., 'booking_payment'
├── title (VARCHAR) e.g., 'Payment Reminder - INVSLE-0001'
├── description (TEXT)
├── notification_date (DATE) ⭐ **When to send**
├── sent_at (DATETIME) When it was sent
├── status (ENUM: pending/sent/dismissed)
├── is_read (BOOLEAN)
├── created_by (FK)
└── timestamps (created_at, updated_at)
```

---

## 💻 Model & Scopes

```php
// Get notifications
Notification::all()                  // All
Notification::pending()              // WHERE status='pending'
Notification::sent()                 // WHERE status='sent'
Notification::unread()               // WHERE is_read=false
Notification::forToday()             // WHERE notification_date=TODAY
Notification::overdue()              // WHERE date < TODAY AND status!='sent'

// Filter by customer
Notification::where('customer_id', 3)->get()

// With relations
Notification::with(['booking', 'sale', 'customer'])->get()

// Count
Notification::count()
Notification::pending()->count()
```

---

## 🔄 Controller Logic

**File:** `app/Http/Controllers/SaleController.php@ajaxPost()`

```php
// When posting a booking:
if (!empty($booking->notify_me) && $booking->notify_me > 0) {
    $notificationDate = Carbon::today()->addDays($booking->notify_me);
    // Jan 31 + 15 = Feb 15
    
    Notification::create([
        'booking_id' => $booking->id,
        'sale_id' => $sale->id,
        'customer_id' => $booking->customer_id,
        'type' => 'booking_payment',
        'title' => 'Payment Reminder - ' . $booking->invoice_no,
        'description' => '...',
        'notification_date' => $notificationDate,
        'status' => 'pending',
        'created_by' => auth()->id(),
    ]);
}
```

---

## 📊 Common Queries

### Tinker Commands
```bash
php artisan tinker

# Get all
> Notification::all()

# Get pending
> Notification::pending()->get()

# Get today's pending
> Notification::forToday()->pending()->get()

# Get customer's notifications
> Notification::where('customer_id', 5)->get()

# Mark as sent
> Notification::find(1)->update(['status' => 'sent', 'sent_at' => now()])

# Count by status
> Notification::select('status')->selectRaw('count(*) as count')->groupBy('status')->get()
```

---

## 🎯 Use Cases

| Scenario | Query |
|----------|-------|
| Send today's reminders | `Notification::forToday()->pending()->get()` |
| Check overdue | `Notification::overdue()->get()` |
| Customer's reminders | `Notification::where('customer_id', 5)->get()` |
| Mark as sent | `$n->update(['status'=>'sent','sent_at'=>now()])` |
| Get unread | `Notification::unread()->get()` |

---

## 📁 Files Changed

| File | Change |
|------|--------|
| `database/migrations/2026_01_31_create_notifications_table.php` | ✨ NEW |
| `app/Models/Notification.php` | ✏️ Updated with scopes |
| `app/Http/Controllers/SaleController.php` | ✏️ Added notification logic |

---

## ✅ Verification

```bash
# Check table exists
mysql> SHOW TABLES LIKE 'notifications';

# Check has data
mysql> SELECT COUNT(*) FROM notifications;

# In Tinker
php artisan tinker
> Notification::all()
> Notification::pending()->count()
```

---

## 🌟 Key Features

✅ **Automatic date calculation** - Today + notify_me days  
✅ **Status tracking** - pending → sent → dismissed  
✅ **Full relationships** - Links booking, sale, customer  
✅ **Query scopes** - pending(), sent(), forToday(), overdue()  
✅ **Performance indexed** - Fast queries on date, status, customer  
✅ **Fully logged** - Tracks who created each notification  

---

## 🔮 Next Steps

1. **Send Reminders** - Create artisan command to send on notification_date
2. **Email Integration** - Add Mail class for customer emails
3. **Admin Dashboard** - View/manage notifications
4. **Customer Portal** - Show their pending reminders

---

## 📞 Quick Help

**Migration:** `php artisan migrate`  
**Tinker:** `php artisan tinker`  
**Check data:** `Notification::all()`  
**See pending:** `Notification::pending()->get()`  
**For today:** `Notification::forToday()->get()`  

---

## 📖 Full Docs

- `NOTIFICATION_SYSTEM_GUIDE.md` - Complete guide
- `NOTIFICATION_VISUAL_GUIDE.md` - Flow diagrams  
- `NOTIFICATION_TINKER_EXAMPLES.md` - Query examples
- `NOTIFICATION_IMPLEMENTATION_COMPLETE.md` - Summary

---

## 🎓 Example

```
User books with 15 days reminder
↓
Notification created with notification_date = Feb 15
↓
Status = pending
↓
On Feb 15, send email to customer
↓
Update: status = sent, sent_at = now()
```

That's it! Simple and powerful. 🚀

