<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockRequestItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'delivery_charges' => 'decimal:2',
    ];

    // Relationships
    public function request()
    {
        return $this->belongsTo(StockRequest::class, 'stock_request_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    // Helpers
    public function totalAmount()
    {
        return $this->approved_qty * $this->unit_price;
    }

    public function isFullyApproved()
    {
        return $this->approved_qty === $this->requested_qty;
    }

    public function isPartiallyApproved()
    {
        return $this->approved_qty > 0 && $this->approved_qty < $this->requested_qty;
    }
}
