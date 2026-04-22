<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    
    // Add closing_balance to appends so it's included in JSON responses
    protected $appends = ['closing_balance'];
    
    // app/Models/Customer.php
    protected $fillable = [
        'branch_id','customer_id', 'customer_name', 'customer_name_ur', 'cnic', 'filer_type', 'zone',
        'contact_person', 'mobile', 'email_address', 'contact_person_2', 'mobile_2',
        'email_address_2', 'opening_balance', 'credit_upto', 'credit_limit', 'address' , 'status','customer_type', 'no_credit_limit'
    ];

    public function ledgers()
    {
        return $this->hasMany(CustomerLedger::class, 'customer_id');
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    // Get the latest ledger entry
    public function latestLedger()
    {
        return $this->hasOne(CustomerLedger::class, 'customer_id')->latest();
    }

    // Accessor for closing balance (gets latest ledger closing balance)
    public function getClosingBalanceAttribute()
    {
        return $this->latestLedger()->value('closing_balance') ?? $this->opening_balance;
    }
}
