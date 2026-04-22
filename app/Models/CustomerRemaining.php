<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerRemaining extends Model
{
    protected $table = 'customer_remaining';
    protected $guarded = [];
    protected $casts = [
        'remaining_qty' => 'float',
    ];

    /**
     * Get the sale associated with this remaining item
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    /**
     * Get the customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the warehouse
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * Scope to get pending/partial deliveries for a customer
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'partial']);
    }

    /**
     * Scope to get by customer
     */
    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope to get by sale
     */
    public function scopeForSale($query, $saleId)
    {
        return $query->where('sale_id', $saleId);
    }
}
