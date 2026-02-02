# 📊 Sale اور Credit Management System - مکمل رہنمائی

## 1️⃣ **Customer کا Previous Balance دیکھنا**
جب sale page میں customer select کریں تو یہ معلومات آتی ہیں:
- **Previous Balance** = Customer Ledger کا Latest Closing Balance
- **Opening Balance** = Customer کو دیا گیا شروع میں credit
- **Credit Limit** = Customer کو دے سکتے ہیں زیادہ سے زیادہ یہ رقم
- **Credit Upto** = یہ credit کب تک valid ہے

---

## 2️⃣ **Sale کے وقت Credit Limit Check**

### ✅ کیا ہوتا ہے:
جب آپ sale کریں تو system یہ checks کرتا ہے:

```
Total Credit = Previous Balance + Sale Amount

اگر Total Credit > Credit Limit → ❌ Sale Block!
اگر Credit Upto کی تاریخ ختم → ❌ Sale Block!
```

### مثال:
```
Customer: ABC Company
Previous Balance: 100,000 روپے
Sale Amount: 80,000 روپے
Total Credit: 180,000 روپے
Credit Limit: 150,000 روپے

❌ RESULT: Sale نہیں ہو سکتا! (180,000 > 150,000)
```

---

## 3️⃣ **Sale ہونے کے بعد Ledger Update**

### کیا ہوتا ہے:
Sale کے فوری بعد Customer Ledger میں نیا entry شامل ہوتی ہے:

| Field | Value |
|-------|-------|
| Previous Balance | آخری closing balance |
| Sale Amount | + فروخت کی رقم |
| Closing Balance | New Balance = Previous + Sale |
| Description | Sale Invoice Number |

### مثال:
```
پہلے:
Previous Closing Balance: 100,000

Sale: 50,000

نے:
New Closing Balance: 150,000
(Ledger میں entry شامل ہوگی)
```

---

## 4️⃣ **Payment ہوتے وقت Ledger Minus**

جب customer payment دے اور آپ **Receipt Voucher** بنائیں:

### کیا ہوتا ہے:
```
Receipt Voucher میں:
- Vendor Type: Customer ✓
- Vendor: Customer کو select کریں
- Amount: Payment کی رقم
- Accounts: جہاں رقم جانی ہے

نتیجہ:
Customer Ledger Closing Balance = پہلے - Payment Amount
```

### مثال:
```
Customer Balance: 150,000 روپے

Payment Receipt: 50,000 روپے

نیا Balance: 100,000 روپے
(50,000 روپے customer کا قرض کم ہو گیا)
```

---

## 5️⃣ **مکمل ترتیب: شروع سے آخر تک**

### Step 1: Customer بنائیں
```
Opening Balance: 20,000 (پہلے سے قرض)
Credit Limit: 200,000 (زیادہ سے زیادہ)
Credit Upto: 31-Mar-2026 (یہ تاریخ تک)
```

### Step 2: Sale کریں
```
Sale Amount: 100,000
→ System Check: 20,000 + 100,000 = 120,000 ≤ 200,000 ✅ OK
→ Ledger Update: New Balance = 120,000
```

### Step 3: دوسرا Sale
```
Sale Amount: 60,000
→ System Check: 120,000 + 60,000 = 180,000 ≤ 200,000 ✅ OK
→ Ledger Update: New Balance = 180,000
```

### Step 4: تیسرا Sale (زیادہ رقم)
```
Sale Amount: 50,000
→ System Check: 180,000 + 50,000 = 230,000 > 200,000 ❌ BLOCKED!
→ Error Message دکھے گا
```

### Step 5: Customer Payment دے
```
Receipt Voucher:
- Customer: ABC Company
- Amount: 100,000
- Account: Bank (جہاں رقم ڈالا)
→ Ledger Update: 180,000 - 100,000 = 80,000
```

### Step 6: اب Sale ہو سکتا ہے
```
Sale Amount: 50,000
→ System Check: 80,000 + 50,000 = 130,000 ≤ 200,000 ✅ OK
→ Sale Complete!
```

---

## 6️⃣ **فیچرز اور Rules**

### Professional Business Rules:
✅ **Credit Limit Protection** - ہر sale سے پہلے check
✅ **Credit Expiry** - تاریخ کے بعد بند
✅ **Automatic Ledger** - ہر لین دین میں update
✅ **Payment Tracking** - Receipt voucher سے minus
✅ **Error Messages** - اردو میں واضح پیغام

### Data Storage:
- **opening_balance** → شروعاتی رقم
- **credit_upto** → تاریخ
- **credit_limit** → حد
- **closing_balance** (ledger میں) → آخری رقم

---

## 7️⃣ **عام سوالات**

### Q: اگر customer کو 200,000 credit دیا ہے لیکن 180,000 استعمال کر چکے ہیں تو?
**A:** بس 20,000 روپے کی اور credit باقی ہے۔ اس سے زیادہ sale نہیں ہوگی۔

### Q: اگر credit تاریخ ختم ہو گئی؟
**A:** Sale نہیں ہوگی۔ Customer کو نیا credit دینا پڑے گا (Customer Edit کریں)۔

### Q: Payment کے بعد balance منفی ہو تو?
**A:** یہ ٹھیک ہے = Customer کو رقم دینا ہے (Advance میں دے گیا)۔

### Q: اگر Account (خودکار) نہیں ہے تب بھی Sale ہو سکتی ہے?
**A:** ہاں! Sale ہو جائے گی اور ledger update ہوگی۔ بعد میں Receipt Voucher سے منہا کریں۔

---

## 8️⃣ **خلاصہ - کیا بہتر ہوا**

| پہلے | اب |
|------|-----|
| Balance دیکھنا مشکل | Sale page میں واضح نظر آتی ہے |
| Credit حد نہیں | Credit limit سے زیادہ نہیں ہو سکتے |
| Manual Ledger | خودکار ledger update |
| Payment عام entry | Professional Receipt Voucher |
| Balance tracking مشکل | Automatic minus ہوتا ہے |

---

**یہ system **ERP بہتری** ہے جو business کو محفوظ رکھتی ہے!** 🎉
