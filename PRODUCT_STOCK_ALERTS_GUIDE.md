# 📦 PRODUCT STOCK ALERT NOTIFICATIONS

**Status:** ✅ IMPLEMENTED  
**Date:** February 1, 2026

---

## 🎯 Overview

جب product کی موجودہ quantity **alert_quantity سے کم** ہو تو automatically notification create ہو۔

---

## 📋 کیا Add کیا

### 1️⃣ **Service Class** (`app/Services/StockAlertService.php`)

**Main Functions:**

```php
StockAlertService::checkAndCreateAlert($productId, $warehouseId);
```
- Check کرتا ہے کہ current stock < alert_quantity
- اگر ہاں تو notification create کرتا ہے
- Duplicate notifications سے بچاتا ہے (ایک دن میں ایک ہی)
- اگر stock نارمل ہو تو pending alerts dismiss کرتا ہے

**دوسری methods:**
```php
StockAlertService::getPendingStockAlerts()    // تمام pending stock alerts
StockAlertService::getAlertsForProduct($id)   // کسی ایک product کے alerts
```

### 2️⃣ **Database Migration** (`2026_02_01_add_product_warehouse_to_notifications.php`)

```sql
ALTER TABLE notifications ADD COLUMN product_id BIGINT NULLABLE
ALTER TABLE notifications ADD COLUMN warehouse_id BIGINT NULLABLE
```

**Foreign Keys:**
- `product_id` → references products.id (cascade delete)
- `warehouse_id` → references warehouses.id (set null)

**Indexes:**
- product_id (for fast lookup)
- warehouse_id (for filtering by warehouse)

### 3️⃣ **Model Updates** (`app/Models/Notification.php`)

**New Relationships:**
```php
public function product() {
    return $this->belongsTo(Product::class, 'product_id');
}

public function warehouse() {
    return $this->belongsTo(Warehouse::class, 'warehouse_id');
}
```

**Updated fillable fields:**
```php
'product_id',
'warehouse_id',
```

### 4️⃣ **Controller Update** (`app/Http/Controllers/NotificationController.php`)

Updated `getPendingNotifications()` to include:
- Product relationships
- Warehouse relationships
- Product name in response
- Warehouse name in response

### 5️⃣ **Artisan Command** (`app/Console/Commands/CheckStockAlerts.php`)

```bash
# Check all products
php artisan stocks:check-alerts

# Check specific product
php artisan stocks:check-alerts --product_id=5
```

---

## 🔧 کیسے استعمال کریں

### Option 1: جب کبھی Stock Update ہو

**کسی بھی stock update کے بعد:**

```php
use App\Services\StockAlertService;

// Stock updated
$product->update(['quantity' => $newQuantity]);

// Check if alert needed
StockAlertService::checkAndCreateAlert($product->id);
```

### Option 2: Scheduled Check (Daily)

**`app/Console/Kernel.php` میں:**

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('stocks:check-alerts')
        ->daily()
        ->at('08:00'); // ہر روز 8 AM کو چیک کریں
}
```

### Option 3: Manual Check

```bash
# Terminal میں
php artisan stocks:check-alerts
```

---

## 📊 Notification Details

### Type: `product_stock_alert`

**Example:**
```json
{
  "id": 1,
  "type": "product_stock_alert",
  "title": "Stock Alert - Samsung AC 1.5 Ton",
  "description": "Product \"Samsung AC 1.5 Ton\" stock is now 2 units (Alert: 5)",
  "product_id": 5,
  "warehouse_id": 1,
  "notification_date": "2026-02-01",
  "status": "pending",
  "is_read": false
}
```

---

## 🎨 UI میں دکھے گا

### Notification Panel میں:

```
[🔔] Stock Alert
    Samsung AC 1.5 Ton
    Stock is now 2 units (Alert: 5)
    Warehouse: Main Store
    [Read] [Dismiss]
```

### Full Notifications Page:

```
NOTIFICATIONS

Pending (2)
├─ Stock Alert - Samsung AC 1.5 Ton
│  Stock is now 2 units (Alert: 5)
│  Warehouse: Main Store
│  [Mark as Read] [Mark as Sent] [Dismiss]
└─ Stock Alert - Motor
   Stock is now 1 unit (Alert: 3)
   Warehouse: Branch 1
   [Mark as Read] [Mark as Sent] [Dismiss]
```

---

## 💡 Logic Explanation

### کب Notification بنے؟

```
1. Product کی current stock check ہو
2. اگر qty <= alert_quantity
   ├─ اور پہلے سے today کے لیے pending notification نہیں ہے
   └─ تو نیا notification create کریں
3. ورنہ کوئی notification نہیں
```

### Duplicate Prevention:

```
✓ ایک دن میں ایک ہی product کے لیے ایک notification
✓ اگر stock دوبارہ کم ہو تو اگلے دن نیا notification
✓ اگر stock نارمل ہو تو pending alerts dismiss ہو جاتے ہیں
```

### Automatic Dismissal:

```
اگر stock > alert_quantity
  ├─ تو pending notifications dismiss ہو جاتے ہیں
  └─ یعنی issue resolved
```

---

## 🧪 Testing

### Test Case 1: Manual Check

```bash
php artisan stocks:check-alerts
# Output: Found X products with alert quantities
```

### Test Case 2: Check Specific Product

```bash
php artisan stocks:check-alerts --product_id=5
# Check only product ID 5
```

### Test Case 3: Database Check

```php
php artisan tinker
> Notification::where('type', 'product_stock_alert')->count()
> 2
```

### Test Case 4: View in UI

1. پہلے ایک product set کریں alert_quantity = 5
2. Product کی stock کو 2 units میں لائیں
3. `php artisan stocks:check-alerts` چلائیں
4. Home page پر [🔔] icon میں badge دیکھیں
5. Click کریں → notification دیکھیں ✓

---

## 🔌 Integration Points

### Product Model:
```php
$product->alert_quantity  // Alert set quantity
```

### Warehouse Stock:
```php
$stock->quantity  // Current quantity
```

### When to Call Service:

1. **Purchase میں:** جب stock add ہو
2. **Sale میں:** جب stock deduct ہو
3. **Stock Transfer میں:** جب warehouse سے move ہو
4. **Inventory میں:** جب adjustment ہو

---

## 📝 Code Examples

### Example 1: Purchase میں

```php
// PurchaseController.php میں

use App\Services\StockAlertService;

$purchase->update(['status' => 'received']);

// Stock update code...

// Check if alert needed
foreach ($purchase->items as $item) {
    StockAlertService::checkAndCreateAlert($item->product_id);
}
```

### Example 2: Sale میں

```php
// SaleController.php میں

use App\Services\StockAlertService;

$sale->update(['status' => 'completed']);

// Stock deduct code...

// Check all affected products
StockAlertService::checkAndCreateAlert($product->id);
```

### Example 3: Scheduled Task

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    // Check stock alerts daily at 8 AM
    $schedule->command('stocks:check-alerts')
        ->dailyAt('08:00');
    
    // Or check every hour
    $schedule->command('stocks:check-alerts')
        ->hourly();
}
```

---

## ⚙️ Configuration

### Product-Level:

ہر product میں `alert_quantity` set کریں:

```php
$product->update([
    'alert_quantity' => 5  // جب 5 سے کم ہو تو alert
]);
```

### No Alert:

```php
$product->update([
    'alert_quantity' => null  // یا 0 = no alert
]);
```

---

## 🔍 Query Examples

### تمام Stock Alerts:

```php
Notification::where('type', 'product_stock_alert')
    ->where('status', 'pending')
    ->with('product', 'warehouse')
    ->get();
```

### Specific Product کے Alerts:

```php
Notification::where('type', 'product_stock_alert')
    ->where('product_id', $productId)
    ->get();
```

### Specific Warehouse میں:

```php
Notification::where('type', 'product_stock_alert')
    ->where('warehouse_id', $warehouseId)
    ->get();
```

---

## 📊 Benefits

✅ **Real-time Alerts** - فوری طور پر stock low ہونے پر معلومات  
✅ **No Stockout** - سامان ختم ہونے سے پہلے order دے سکیں  
✅ **Cost Effective** - Overstock نہیں ہوگا  
✅ **Smart Deduplication** - Spam نہیں ہوگی  
✅ **Multi-warehouse** - ہر warehouse کے لیے الگ alerts  
✅ **Automatic** - Manual check کی ضرورت نہیں  

---

## 🚀 Next Steps

### Optional Enhancements:

1. **Email Alerts:** جب stock alert ہو تو email بھیجیں
2. **SMS Alerts:** Critical products کے لیے SMS
3. **Auto-PO:** Automatically purchase order بنائیں
4. **Warehouse Transfer:** دوسری warehouse سے transfer کی تجویز
5. **Dashboard Widget:** Dashboard میں low stock summary

---

## 📞 Troubleshooting

### Alerts نہیں آ رہے؟

```bash
# 1. Check if alert_quantity set ہے
php artisan tinker
> Product::find(5)->alert_quantity
> 5

# 2. Check current stock
> Product::find(5)->stock()->sum('quantity')
> 2

# 3. Manually trigger
> StockAlertService::checkAndCreateAlert(5)

# 4. Check notifications table
> Notification::where('product_id', 5)->get()
```

### Duplicate Alerts آ رہے ہیں؟

```php
// Service خود se duplicate prevent کرتا ہے
// لیکن اگر problem ہے تو manually dismiss کریں:

Notification::where('type', 'product_stock_alert')
    ->where('product_id', 5)
    ->update(['status' => 'dismissed']);
```

---

## ✅ Deployment Checklist

- [x] Migration run: `php artisan migrate`
- [x] Service class created
- [x] Artisan command created
- [x] Model updated with relationships
- [x] Controller updated
- [x] Command tested: `php artisan stocks:check-alerts`
- [x] Database schema verified
- [x] UI shows product notifications
- [x] Badges count correctly

---

**Version:** 1.0  
**Status:** ✅ READY TO USE

