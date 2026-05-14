<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'email',
        'phone',
        'address',
        'opening_balance',
        'company_names',
        'brand_ids'
    ];

    protected $casts = [
        'company_names' => 'array',
        'brand_ids' => 'array'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function ledger()
    {
        return $this->hasOne(VendorLedger::class);
    }
}
