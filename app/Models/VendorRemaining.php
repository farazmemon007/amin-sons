<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorRemaining extends Model
{
    use HasFactory;

    protected $table = 'vendor_remaining';

    protected $fillable = [
        'purchase_id',
        'vendor_id',
        'product_id',
        'warehouse_id',
        'ordered_qty',
        'received_qty',
        'remaining_qty',
        'status',
    ];

    protected $casts = [
        'ordered_qty'   => 'integer',
        'received_qty'  => 'integer',
        'remaining_qty' => 'integer',
    ];

    /**
     * Get the purchase associated with this remaining item
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Get the vendor associated with this remaining item
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the product associated with this remaining item
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the warehouse associated with this remaining item
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Scope: Get all pending vendor deliveries
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'partial']);
    }

    /**
     * Scope: Get all remaining items for a specific vendor
     */
    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Scope: Get all remaining items for a specific purchase
     */
    public function scopeForPurchase($query, $purchaseId)
    {
        return $query->where('purchase_id', $purchaseId);
    }

    /**
     * Check if this delivery is still pending
     */
    public function isPending()
    {
        return $this->remaining_qty > 0 && in_array($this->status, ['pending', 'partial']);
    }
}
