<?php

// app/Models/Sale.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    // protected $fillable = [
    //     'customer', 'product', 'reference', 'product_code', 'brand', 'unit', 'per_price', 
    //     'per_discount', 'qty', 'per_total', 'total_amount_Words', 'total_bill_amount',
    //     'total_extradiscount', 'total_net', 'cash', 'card', 'change', 'total_discount',
    //     'total_subtotal', 'total_items','color'
    // ];
    protected $guarded=[];
    public function saleItems()
{
    return $this->hasMany(\App\Models\SaleItem::class, 'sale_id');
}


  public function customer()
{
    return $this->belongsTo(Customer::class, 'customer_id', 'id');
}


   public function product()
{
    return $this->belongsTo(Product::class, 'product_id', 'id');
}

    
    public static function generateInvoiceNo()
    {
        $lastSale = self::orderBy('id', 'desc')->first();

        // default start
        $next = 1;
        if ($lastSale && !empty($lastSale->invoice_no)) {
            if (preg_match('/(\d+)$/', $lastSale->invoice_no, $m)) {
                $next = (int) $m[1] + 1;
            }
        }

        return 'INV-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
