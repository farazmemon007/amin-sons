<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalePosting extends Model
{
    use HasFactory;
    
    protected $table = 'sale_postings';
    protected $guarded = [];
    
    protected $casts = [
        'qty' => 'integer',
        'source_id' => 'integer',
        'status' => 'string',
    ];

    /**
     * Boot model
     */
    protected static function boot()
    {
        parent::boot();
        
        // Optionally: auto-qualify status values
        static::creating(function ($model) {
            if (!in_array($model->status, ['pending', 'processed'])) {
                $model->status = 'pending';
            }
        });
    }

    // ═══════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get the sale this posting belongs to
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id', 'id');
    }

    /**
     * Get the product this posting is for
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * Get the source (branch or warehouse)
     */
    public function source()
    {
        // Polymorphic-style resolution based on source_type
        if ($this->source_type === 'warehouse') {
            return $this->belongsTo(Warehouse::class, 'source_id', 'id');
        } elseif ($this->source_type === 'branch') {
            return $this->belongsTo(Branch::class, 'source_id', 'id');
        }
        return null;
    }

    // ═══════════════════════════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get pending (draft) postings
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Get processed postings
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    /**
     * Get postings for a specific sale
     */
    public function scopeForSale($query, $saleId)
    {
        return $query->where('sale_id', $saleId);
    }

    /**
     * Get postings from branch stock
     */
    public function scopeFromBranch($query)
    {
        return $query->where('source_type', 'branch');
    }

    /**
     * Get postings from warehouse
     */
    public function scopeFromWarehouse($query)
    {
        return $query->where('source_type', 'warehouse');
    }

    // ═══════════════════════════════════════════════════════════════
    // HELPER METHODS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Check if this posting is still pending (not yet processed)
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if this posting has been processed
     */
    public function isProcessed()
    {
        return $this->status === 'processed';
    }

    /**
     * Mark as processed
     */
    public function markAsProcessed()
    {
        $this->status = 'processed';
        $this->save();
        return $this;
    }

    /**
     * Mark as pending
     */
    public function markAsPending()
    {
        $this->status = 'pending';
        $this->save();
        return $this;
    }
}
