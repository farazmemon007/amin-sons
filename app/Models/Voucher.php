<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_type', 'date', 'sales_officer', 'type', 'person',
        'sub_head', 'narration', 'amount',
        // New fields for inter-branch system
        'from_branch_id', 'to_branch_id', 'method', 'reference', 'created_by', 'remarks'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    // Relationships for inter-branch system
    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeReceipts($query)
    {
        return $query->where('type', 'receipt');
    }

    public function scopePayments($query)
    {
        return $query->where('type', 'payment');
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('from_branch_id', $branchId);
    }

    // Helpers
    public function isReceipt()
    {
        return $this->type === 'receipt';
    }

    public function isPayment()
    {
        return $this->type === 'payment';
    }

    public static function createPayment($fromBranchId, $toBranchId, $amount, $method = 'cash')
    {
        return self::create([
            'type' => 'payment',
            'from_branch_id' => $fromBranchId,
            'to_branch_id' => $toBranchId,
            'amount' => $amount,
            'method' => $method,
            'created_by' => auth()->id(),
        ]);
    }

    public static function createReceipt($fromBranchId, $toBranchId, $amount, $method = 'cash')
    {
        return self::create([
            'type' => 'receipt',
            'from_branch_id' => $toBranchId,
            'to_branch_id' => $fromBranchId,
            'amount' => $amount,
            'method' => $method,
            'created_by' => auth()->id(),
        ]);
    }
}
