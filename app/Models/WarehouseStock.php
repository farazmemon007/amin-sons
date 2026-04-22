<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'warehouse_id',
        'product_id',
        'quantity',
        'price',
        'remarks'
    ];
// App\Models\WarehouseStock.php


    public function warehouse() {
        return $this->belongsTo(Warehouse::class);
    }

    public function branch() {
        return $this->belongsTo(Branch::class);
    }

    // App\Models\WarehouseStock.php
 //  Rename relation
    public function stockWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }


    // public function product() {
    //     return $this->belongsTo(Product::class);
    // }

     public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }


  public function products()
    {
        return $this->belongsToMany(Product::class, 'warehouse_stocks', 'warehouse_id', 'product_id')
                    ->withPivot('quantity', 'price', 'remarks');
    }


}
