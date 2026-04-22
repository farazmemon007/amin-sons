<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InwardGatepass extends Model
{
    use HasFactory;
     protected $fillable = [
        'branch_id','warehouse_id','vendor_id',
        'purchase_id','gatepass_date','gatepass_no',
        'remarks','status','created_by','note','transport_name','bilty_no'
    ];
    public function items()
    {
        return $this->hasMany(InwardGatepassItem::class);
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get vendor remaining items for this gatepass
     */
    public function vendorRemainings()
    {
        return $this->belongsToMany(VendorRemaining::class, 'inward_gatepass_remaining', 'inward_gatepass_id', 'vendor_remaining_id');
    }
}
