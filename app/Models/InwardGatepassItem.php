<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InwardGatepassItem extends Model
{
    use HasFactory;

    //   protected $fillable = ['inward_gatepass_id','product_id','qty', 'packing_type', 'packing_qty', 'item_per_piece', 'loose_piece', 'unit'];
protected $guarded = [];
    public function gatepass()
    {
        return $this->belongsTo(InwardGatepass::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
