<?php

namespace App\Models;

use App\Models\ProductBookingItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductBooking extends Model
{
    use HasFactory;
    
    // ✅ Explicitly define table name to match migration
    // Migration created: Schema::create('productbookings', ...)
    protected $table = 'productbookings';
    
    protected $fillable = [
        'invoice_no',
        'manual_invoice',
        'party_type',
        'customer_id',
        'sub_customer',
        'filer_type',
        'address',
        'tel',
        'remarks',
        'sub_total1',
        'sub_total2',
        'discount_percent',
        'discount_amount',
        'additional_discount',
        'extra_charges',
        'previous_balance',
        'total_balance',
        'receipt1',
        'receipt2',
        'final_balance1',
        'final_balance2',
        'weight',
        'status',
        'branch_id',
        'salesman_id',
    ];

    // Relation to sale items
    public function items()
    {
        return $this->hasMany(ProductBookingItem::class,'booking_id');
    }

    // Relation to Customer (agar model hai)
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesman()
    {
        return $this->belongsTo(SalesOfficer::class, 'salesman_id');
    }
   
}
