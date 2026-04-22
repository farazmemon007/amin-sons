<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchAccount extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'current_balance' => 'decimal:2',
    ];

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function transactions()
    {
        return $this->hasMany(BranchTransaction::class, 'branch_id');
    }

    // Helpers
    public function totalDebit()
    {
        return BranchTransaction::where('branch_id', $this->branch_id)
            ->where('type', 'debit')
            ->sum('amount');
    }

    public function totalCredit()
    {
        return BranchTransaction::where('branch_id', $this->branch_id)
            ->where('type', 'credit')
            ->sum('amount');
    }

    public function calculateBalance()
    {
        // Balance = Total Credit (receivable) - Total Debit (payable)
        return $this->totalCredit() - $this->totalDebit();
    }

    public function updateBalance()
    {
        $this->update(['current_balance' => $this->calculateBalance()]);
    }
}
