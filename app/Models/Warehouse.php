<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user(){
        return $this->belongsTo(User::class, 'creater_id');
    }
public function products() {
    return $this->belongsToMany(Product::class, 'product_warehouse')
                ->withPivot('stock');
}

public function branches()
{
    return $this->belongsToMany(Branch::class, 'branch_warehouse');
}

/**
 * Users explicitly assigned to manage/access this warehouse.
 * Super Admin can assign cross-branch incharges here.
 */
public function assignedUsers()
{
    return $this->belongsToMany(User::class, 'user_warehouses')
                ->withPivot('is_incharge', 'notes')
                ->withTimestamps();
}

/**
 * Get the primary incharge of this warehouse (if any).
 */
public function incharge()
{
    return $this->assignedUsers()->wherePivot('is_incharge', true)->first();
}


}
