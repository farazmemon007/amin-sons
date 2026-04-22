<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockRequest extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function items()
    {
        return $this->hasMany(StockRequestItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('to_branch_id', $branchId);
    }

    // Helpers
    public function totalQuantity()
    {
        return $this->items()->sum('requested_qty');
    }

    public function totalApprovedQty()
    {
        return $this->items()->sum('approved_qty');
    }

    public function totalAmount()
    {
        return $this->items()->selectRaw('SUM(approved_qty * unit_price) as total')
            ->value('total') ?? 0;
    }

    public function canApprove()
    {
        return $this->status === 'pending';
    }

    public function approve($userId)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    public function reject($userId, $reason)
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $userId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }
}
