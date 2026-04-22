<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userhasmany()
    {
        return $this->hasMany(User::class, 'branch_id');
    }

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'branch_warehouse');
    }

    // Inter-branch Stock Request relationships
    public function sentRequests()
    {
        return $this->hasMany(StockRequest::class, 'from_branch_id');
    }

    public function receivedRequests()
    {
        return $this->hasMany(StockRequest::class, 'to_branch_id');
    }

    // Branch Account relationship
    public function account()
    {
        return $this->hasOne(BranchAccount::class);
    }

    // Branch Transactions
    public function transactions()
    {
        return $this->hasMany(BranchTransaction::class);
    }

    // ✅ Branch Accounts (Chart of Accounts)
    public function accounts()
    {
        return $this->hasMany(Account::class, 'branch_id');
    }


    public function incomingTransactions()
    {
        return $this->hasMany(BranchTransaction::class, 'related_branch_id');
    }

    // Helpers
    public function getBalance()
    {
        return $this->account?->current_balance ?? 0;
    }

    public function getTotalDebit()
    {
        return BranchTransaction::where('branch_id', $this->id)
            ->where('type', 'debit')
            ->sum('amount');
    }

    public function getTotalCredit()
    {
        return BranchTransaction::where('branch_id', $this->id)
            ->where('type', 'credit')
            ->sum('amount');
    }
}
