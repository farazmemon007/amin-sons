<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockHold extends Model
{
    protected $table = 'stock_holds';

    protected $fillable = [
        'sale_id',
        'warehouse_order_id',
        'product_id',
        'warehouse_id',
        'customer_id',
        'invoice_no',
        'dc_no',
        'available_qty',
        'deliver_qty',
        'remaining_qty',
        'product_name',
        'product_code',
        'unit_price',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'available_qty' => 'decimal:2',
        'deliver_qty' => 'decimal:2',
        'remaining_qty' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ✅ Relationships
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function warehouseOrder()
    {
        return $this->belongsTo(WarehouseOrder::class, 'warehouse_order_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ✅ Scopes for filtering
    public function scopeByInvoice($query, $invoiceNo)
    {
        return $query->where('invoice_no', $invoiceNo);
    }

    public function scopeByDc($query, $dcNo)
    {
        return $query->where('dc_no', $dcNo);
    }

    public function scopeBySale($query, $saleId)
    {
        return $query->where('sale_id', $saleId);
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->whereDate('created_at', '>=', now()->subDays($days));
    }
}
