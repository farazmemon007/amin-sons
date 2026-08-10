<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_warehouse_id',
        'to_warehouse_id',
        'from_branch_id',
        'to_branch_id',
        'to_shop',
        'product_id',
        'quantity',
        'remarks',
        // New fields for approval workflow
        'status',
        'stock_request_id',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function fromWarehouse() {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse() {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function fromBranch() {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch() {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function request() {
        return $this->belongsTo(StockRequest::class, 'stock_request_id');
    }

    public function approvedByUser() {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopePending($query) {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query) {
        return $query->where('status', 'approved');
    }

    public function scopeCompleted($query) {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query) {
        return $query->where('status', 'cancelled');
    }

    // Helpers
    public function isInterBranch() {
        return $this->from_branch_id && $this->to_branch_id;
    }

    public function isPending() {
        return $this->status === 'pending';
    }

    public function canApprove() {
        return $this->status === 'pending';
    }

    public function approve($userId) {
        $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    /**
     * Get unit price for stock transfer:
     * 1. If stock request item exists, use approved unit_price
     * 2. Otherwise, check latest purchase price
     * 3. Fallback to product wholesale_price or price
     */
    public function getUnitPriceAttribute()
    {
        // 1. Check if linked to stock_request and has unit_price on item
        if ($this->stock_request_id) {
            $item = \App\Models\StockRequestItem::where('stock_request_id', $this->stock_request_id)
                ->where('product_id', $this->product_id)
                ->first();
            if ($item && $item->unit_price && $item->unit_price > 0) {
                return (float) $item->unit_price;
            }
        }

        // 2. Check latest PurchaseItem price for this product
        try {
            $purchasePrice = \App\Models\PurchaseItem::where('product_id', $this->product_id)
                ->where('price', '>', 0)
                ->latest('id')
                ->value('price');
            if ($purchasePrice && $purchasePrice > 0) {
                return (float) $purchasePrice;
            }
        } catch (\Exception $e) {
            // ignore
        }

        // 3. Fallback to product price (wholesale_price or price)
        if ($this->product) {
            if ($this->product->wholesale_price && $this->product->wholesale_price > 0) {
                return (float) $this->product->wholesale_price;
            }
            if ($this->product->price && $this->product->price > 0) {
                return (float) $this->product->price;
            }
        }

        return 0.00;
    }

    public function getTotalValueAttribute()
    {
        return (float) ($this->quantity * $this->unit_price);
    }
}

