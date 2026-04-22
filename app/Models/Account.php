<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',     // foreign key: branch (for branch-specific accounts)
        'head_id',       // foreign key: account head
        'account_code',  // account code
        'title',         // account title
        'type',          // Debit / Credit
        'total_debit',
        'total_credit',
        'status',        // active/inactive
        'opening_balance', // opening balance
    ];

    // Relation with AccountHead
    public function head()
    {
        return $this->belongsTo(AccountHead::class, 'head_id');
    }

    // Relation with Branch
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
    
}
