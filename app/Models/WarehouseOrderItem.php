<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseOrderItem extends Model
{
    protected $table = 'warehouse_order_items';

    protected $fillable = [
        'warehouse_order_id',
        'sale_item_id',
        'product_id',
        'product_name',
        'item_code',
        'qty',
        'retail_price',
        'amount',
        'warehouse_id',
    ];

    public function order()
    {
        return $this->belongsTo(WarehouseOrder::class, 'warehouse_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function saleItem()
    {
        return $this->belongsTo(\App\Models\SaleItem::class, 'sale_item_id');
    }
}
