<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchTransaction extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function relatedBranch()
    {
        return $this->belongsTo(Branch::class, 'related_branch_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeDebit($query)
    {
        return $query->where('type', 'debit');
    }

    public function scopeCredit($query)
    {
        return $query->where('type', 'credit');
    }

    public function scopeWithRelatedBranch($query, $branchId)
    {
        return $query->where('related_branch_id', $branchId);
    }

    public function scopeOfType($query, $referenceType)
    {
        return $query->where('reference_type', $referenceType);
    }

    // Helpers
    public function isDebit()
    {
        return $this->type === 'debit';
    }

    public function isCredit()
    {
        return $this->type === 'credit';
    }

    public static function createFromTransfer($stockTransferId, $fromBranchId, $toBranchId, $amount)
    {
        // Credit entry for sender (receivable)
        self::create([
            'branch_id' => $fromBranchId,
            'related_branch_id' => $toBranchId,
            'type' => 'credit',
            'amount' => $amount,
            'reference_type' => 'transfer',
            'reference_id' => $stockTransferId,
            'description' => "Stock transfer to Branch #{$toBranchId}",
            'created_by' => auth()->id(),
        ]);

        // Debit entry for receiver (payable)
        self::create([
            'branch_id' => $toBranchId,
            'related_branch_id' => $fromBranchId,
            'type' => 'debit',
            'amount' => $amount,
            'reference_type' => 'transfer',
            'reference_id' => $stockTransferId,
            'description' => "Stock transfer from Branch #{$fromBranchId}",
            'created_by' => auth()->id(),
        ]);

        // Update account balances
        BranchAccount::where('branch_id', $fromBranchId)->first()?->updateBalance();
        BranchAccount::where('branch_id', $toBranchId)->first()?->updateBalance();
    }

    /**
     * Get display amount for a transaction:
     * If the amount is 0 and it is an inter-branch transfer, dynamically calculate the total value.
     */
    public function getDisplayAmountAttribute()
    {
        if ($this->amount > 0) {
            return (float) $this->amount;
        }

        if ($this->reference_type === 'transfer' && $this->reference_id) {
            $total = \App\Models\StockRequestItem::where('stock_request_id', $this->reference_id)
                ->get()
                ->sum(function ($item) {
                    $price = $item->unit_price;
                    if (!$price || $price <= 0) {
                        $purchasePrice = \App\Models\PurchaseItem::where('product_id', $item->product_id)
                            ->where('price', '>', 0)
                            ->latest('id')
                            ->value('price');
                        if ($purchasePrice && $purchasePrice > 0) {
                            $price = $purchasePrice;
                        } else {
                            $product = \App\Models\Product::find($item->product_id);
                            $price = $product ? ($product->wholesale_price ?: $product->price ?: 0) : 0;
                        }
                    }
                    return $item->approved_qty * $price;
                });

            if ($total > 0) {
                return (float) $total;
            }
        }

        return (float) $this->amount;
    }

    /**
     * Get display description for a transaction:
     * If it is an inter-branch transfer, dynamically update the text to show the correct stock value.
     */
    public function getDisplayDescriptionAttribute()
    {
        if ($this->reference_type === 'transfer' && $this->reference_id) {
            $amount = $this->display_amount;
            $relatedName = $this->relatedBranch->name ?? $this->relatedBranch->branch_name ?? 'Branch #' . $this->related_branch_id;
            
            if ($this->type === 'credit') {
                return "Stock transfer (Stock Value): " . number_format($amount, 2) . " to {$relatedName}. Request ID: {$this->reference_id}";
            } else {
                return "Stock transfer (Stock Value): " . number_format($amount, 2) . " from {$relatedName}. Request ID: {$this->reference_id}";
            }
        }
        return $this->description;
    }
}
