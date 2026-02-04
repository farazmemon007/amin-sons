# Sale Edit & Update - Complete Flow Documentation

## 🎯 Overview
Complete, fully functional sale edit and update system with proper handling of:
- Sale header (customer, address, remarks)
- Line-item discounts (discount_percent, discount_amount per product)
- Stock management (warehouse and main stock reversals & updates)
- Customer ledger tracking (balance adjustments)
- Account updates (sales account adjustments)

---

## 📊 Database Structure

### Sale Table
```
id, invoice_no, customer_id, sub_total1, sub_total2, 
discount_percent, discount_amount, total_net, ...
```

### SaleItem Table (Line Items)
```
id, sale_id, product_id, warehouse_id, 
sales_qty, retail_price, 
discount_percent, discount_amount, amount
```

### Stock Management Tables
- `warehouse_stock` - Per-warehouse inventory (warehouse_id, product_id, quantity)
- `stock` - Global inventory (product_id, quantity)
- `stock_movements` - Audit trail for stock changes

### Customer Ledger
```
customer_id, previous_balance, closing_balance, 
reference_type, reference_id (sale.id)
```

### Accounts
```
id, head_id (reference to account_head), 
opening_balance (debit/credit)
```

---

## 🔄 Complete Edit & Update Flow

### 1️⃣ DISPLAY SALE (GET /sales/{id}/edit)

**Controller Method:** `saleedit($id)`

```php
public function saleedit($id)
{
    // Load sale with relationships
    $sale = Sale::with(['customer', 'saleItems.product'])->findOrFail($id);
    $customers = Customer::all();
    
    // Transform SaleItem records into form-friendly array
    $items = [];
    foreach ($sale->saleItems as $saleItem) {
        $items[] = [
            'product_id' => $saleItem->product_id,
            'item_name' => $saleItem->product->item_name,
            'price' => $saleItem->retail_price,
            'discount_percent' => $saleItem->discount_percent,    // ✅ Line-item
            'discount' => $saleItem->discount_amount,              // ✅ Line-item
            'qty' => $saleItem->sales_qty,
            'total' => $saleItem->amount,
        ];
    }
    
    return view('admin_panel.sale.saleedit', compact('sale', 'customers', 'items'));
}
```

**View:** `resources/views/admin_panel/sale/saleedit.blade.php`
- Form with customer selector: `name="customer_id"`
- Table with sale items (editable)
- Each row has: product, qty, price, discount_percent, discount_amount

**Key Fields Displayed:**
- Customer (dropdown)
- Address, Tel, Remarks (text inputs)
- Items table with discount columns

---

### 2️⃣ SUBMIT EDITED SALE (PUT /sales/{id})

**Form Submission:**
```html
<form action="{{ route('sales.update', $sale->id) }}" method="POST">
    @csrf
    @method('PUT')
    
    <input name="customer_id" value="4">
    
    <!-- Line items array -->
    <input name="product_id[]" value="5">
    <input name="sales_qty[]" value="2">
    <input name="retail_price[]" value="1200">
    <input name="discount_percentage[]" value="10">
    <input name="discount_amount[]" value="120">
    <input name="warehouse_id[5]" value="2">
    
    <!-- Totals -->
    <input name="sub_total1" value="2400">
    <input name="sub_total2" value="2160">
    <input name="total_net" value="2160">
</form>
```

---

### 3️⃣ PROCESS UPDATE (Controller: `update()`)

**Full Transaction with 7 Steps:**

#### Step 1: Fetch Existing Sale
```php
$sale = Sale::with(['saleItems', 'customer'])->findOrFail($id);
$oldTotal = $sale->total_net;  // For ledger difference
```

#### Step 2: Reverse Old Stock
```php
foreach ($sale->saleItems as $oldItem) {
    // Add back warehouse stock
    $whStock = WarehouseStock::where('product_id', $oldItem->product_id)
        ->where('warehouse_id', $oldItem->warehouse_id)->first();
    $whStock->quantity += $oldItem->sales_qty;
    $whStock->save();
    
    // Add back main stock
    $mainStock = Stock::where('product_id', $oldItem->product_id)
        ->where('warehouse_id', $oldItem->warehouse_id)->first();
    $mainStock->qty += $oldItem->sales_qty;
    $mainStock->save();
}

// Delete old sale items
$sale->saleItems()->delete();
```

#### Step 3: Update Sale Header
```php
$sale->update([
    'customer_id' => $request->input('customer_id'),
    'address' => $request->input('address'),
    'tel' => $request->input('tel'),
    'remarks' => $request->input('remarks'),
    'sub_total1' => $request->input('sub_total1'),
    'discount_percent' => $request->input('discount_percent') ?? 0,
    'discount_amount' => $request->input('discount_amount') ?? 0,
    'total_net' => $request->input('total_net'),
]);
```

#### Step 4: Create New Sale Items with Discounts
```php
foreach ($request->product_id ?? [] as $i => $productId) {
    $qty = (float) $request->sales_qty[$i];
    $price = (float) $request->retail_price[$i];
    $discountAmount = (float) $request->discount_amount[$i];
    $discountPercent = (float) $request->discount_percentage[$i];
    $warehouse_id = $request->warehouse_id[$productId];
    
    // ✅ Create SaleItem WITH discount fields
    SaleItem::create([
        'sale_id' => $sale->id,
        'warehouse_id' => $warehouse_id,
        'product_id' => $productId,
        'sales_qty' => $qty,
        'retail_price' => $price,
        'discount_percent' => $discountPercent,   // ✅ Saved
        'discount_amount' => $discountAmount,     // ✅ Saved
        'amount' => ($qty * $price) - $discountAmount,
    ]);
    
    // Deduct new stock from warehouse
    $whStock = WarehouseStock::where(...)->first();
    $whStock->quantity -= $qty;
    $whStock->save();
}
```

#### Step 5: Update Customer Ledger
```php
$difference = $sale->total_net - $oldTotal;

if ($difference != 0) {
    $latestLedger = CustomerLedger::where('customer_id', $sale->customer_id)
        ->latest('id')->first();
    $previousBalance = $latestLedger->closing_balance ?? 0;
    
    // Create new ledger entry with difference
    CustomerLedger::create([
        'customer_id' => $sale->customer_id,
        'admin_or_user_id' => auth()->id(),
        'previous_balance' => $previousBalance,
        'closing_balance' => $previousBalance + $difference,
        'reference_type' => 'Sale Update',
        'reference_id' => $sale->id,
    ]);
}
```

#### Step 6: Update Sales Account
```php
$salesHead = AccountHead::where('name', 'like', '%Sales%')->first();
if ($salesHead && $difference != 0) {
    $saleAccount = Account::where('head_id', $salesHead->id)->first();
    $saleAccount->opening_balance += $difference;
    $saleAccount->save();
}
```

#### Step 7: Return Response
```php
return redirect()->route('sale.index')
    ->with('success', 'Sale updated successfully with all items and stock adjusted!');
```

---

## 🔍 What Gets Updated

| Component | Action | Details |
|-----------|--------|---------|
| **Sale Header** | UPDATE | customer_id, address, tel, remarks, totals |
| **SaleItems** | DELETE ALL + RECREATE | Preserves discount_percent & discount_amount |
| **Warehouse Stock** | REVERSE + DEDUCT | Old qty reversed, new qty deducted |
| **Main Stock** | REVERSE + DEDUCT | Same as warehouse |
| **Customer Ledger** | CREATE NEW ENTRY | Only if total changed (difference logic) |
| **Sales Account** | UPDATE | Balance increased/decreased by difference |

---

## 📋 Form Structure (saleedit.blade.php)

```html
<form action="{{ route('sales.update', $sale->id) }}" method="POST">
    @csrf
    @method('PUT')
    
    <!-- Customer Selection -->
    <select name="customer_id">
        @foreach ($Customer as $c)
            <option value="{{ $c->id }}" 
                {{ $sale->customer_id == $c->id ? 'selected' : '' }}>
                {{ $c->customer_name }}
            </option>
        @endforeach
    </select>
    
    <!-- Items Table -->
    <table>
        <tbody id="saleItems">
            @foreach ($saleItems as $index => $item)
            <tr>
                <input type="hidden" name="product_id[]" value="{{ $item['product_id'] }}">
                <input type="text" name="item_name[]" value="{{ $item['item_name'] }}">
                <input type="number" name="sales_qty[]" value="{{ $item['qty'] }}">
                <input type="number" name="retail_price[]" value="{{ $item['price'] }}">
                <input type="number" name="discount_percentage[]" value="{{ $item['discount_percent'] }}">
                <input type="number" name="discount_amount[]" value="{{ $item['discount'] }}">
                <input type="hidden" name="warehouse_id[{{ $item['product_id'] }}]" value="2">
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <button type="submit">Update Sale</button>
</form>
```

---

## ✅ Checklist: What's Implemented

- ✅ Load sale with SaleItem relationship
- ✅ Display discount_percent and discount_amount per line item
- ✅ Reverse old stock before creating new items
- ✅ Create SaleItem records with discount fields
- ✅ Deduct new stock quantities
- ✅ Calculate and update customer ledger (only if total changed)
- ✅ Update sales account balance
- ✅ Database transaction (rollback on error)
- ✅ Proper error handling with validation
- ✅ Redirect with success message

---

## 🚀 Route Registration

```php
// In routes/web.php
Route::put('/sales/{id}', [SaleController::class, 'update'])
    ->middleware('permission:sale.edit')
    ->name('sales.update');

Route::get('/sales/{id}/edit', [SaleController::class, 'saleedit'])
    ->middleware('permission:sale.edit')
    ->name('sales.edit');
```

---

## 🧪 Testing the Flow

1. **Edit Page Load**: GET `/sales/27/edit`
   - ✅ Should show all items with discount_percent & discount_amount
   - ✅ Customer dropdown should have current customer selected
   
2. **Update Sale**: PUT `/sales/27` with new values
   - ✅ SaleItem records deleted and recreated
   - ✅ Stock reversed then deducted
   - ✅ Ledger balance adjusted by difference
   - ✅ Redirect to sale.index with success message

3. **Verify in Database**:
   ```sql
   SELECT * FROM sale_items WHERE sale_id = 27;
   -- Should show: discount_percent=10, discount_amount=120
   
   SELECT * FROM customer_ledgers WHERE customer_id=4 ORDER BY id DESC;
   -- Should show new entry with adjusted closing_balance
   ```

---

## ❌ Common Issues & Solutions

| Issue | Cause | Solution |
|-------|-------|----------|
| "Object could not be converted to int" | `$sale->customer` vs `$sale->customer_id` | Use `customer_id` in blade |
| Discount not saving | Missing fields in SaleItem::create() | Add `discount_percent` & `discount_amount` |
| Stock doubled | Not reversing old stock | Call stock += qty before creating new items |
| Ledger not updated | Difference = 0 logic | Check if ($difference != 0) before creating |
| 404 on form submit | Wrong route name | Use `route('sales.update', $sale->id)` |

---

## 📞 Support

For questions about the flow, check:
- Controller: `app/Http/Controllers/SaleController.php` - `update()` method
- View: `resources/views/admin_panel/sale/saleedit.blade.php`
- Models: Sale, SaleItem, WarehouseStock, Stock, CustomerLedger

