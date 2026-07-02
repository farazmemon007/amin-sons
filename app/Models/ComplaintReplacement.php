<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplaintReplacement extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function issuedProduct()
    {
        return $this->belongsTo(Product::class, 'issued_product_id');
    }

    public function collectedDamagedProduct()
    {
        return $this->belongsTo(Product::class, 'collected_damaged_product_id');
    }

    public function sourceWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    public function transferredWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'transferred_warehouse_id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function claimedByUser()
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }
}
