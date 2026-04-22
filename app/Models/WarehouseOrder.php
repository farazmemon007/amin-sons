<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseOrder extends Model
{
    protected $table = 'warehouse_orders';

    protected $fillable = [
        'dc_no',
        'warehouse_id',
        'branch_id',
        'delivery_location_type',
        'customer_id',
        'sale_id',
        'status',
        'remarks',
        'prepared_by',
        'created_by',
        'updated_by',
        'items',
        'delivered_qty',
        'remaining_qty',
    ];

    protected $casts = [
        'items' => 'array',
    ];

    // Relationships
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function itemsRelation()
    {
        return $this->hasMany(WarehouseOrderItem::class, 'warehouse_order_id');
    }
}
