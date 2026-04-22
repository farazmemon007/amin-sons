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
}
