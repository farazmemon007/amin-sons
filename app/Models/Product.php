<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Stock;
use App\Models\Branch;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    // protected $fillable = [
    //     'creater_id', 'category_id', 'sub_category_id', 'item_code', 'item_name', 'size',
    //     'opening_carton_quantity', 'carton_quantity', 'loose_pieces', 'pcs_in_carton',
    //     'wholesale_price', 'retail_price', 'initial_stock', 'alert_quantity'
    // ];
    // app/Models/Product.php
 public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'warehouse_stocks', 'product_id', 'warehouse_id')
                    ->withPivot('quantity', 'price', 'remarks');
    }

    /**
     * Direct relationship to WarehouseStock (for branch-aware queries)
     */
    public function warehouseStocks()
    {
        return $this->hasMany(WarehouseStock::class, 'product_id', 'id');
    }

    // app/Models/Product.php

    public function activeDiscount()
    {
        return $this->hasOne(ProductDiscount::class, 'product_id')
            ->where('status', 1); // only active discount
    }




    public function category_relation()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function sub_category_relation()
    {
        return $this->belongsTo(Subcategory::class, 'sub_category_id');
    }


    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function type()
    {
        return $this->belongsTo(ProductType::class, 'type_id');
    }
    // public function stock()
    // {
    //     return $this->hasOne(Stock::class);
    // }
   // ✅ SINGLE stock relation
    public function stockproduct()
    {
        return $this->hasOne(Stock::class, 'product_id', 'id');
    }

    public function boms(){ return $this->hasMany(ProductBom::class,'product_id'); }
public function components(){ return $this->belongsToMany(Product::class,'product_boms','product_id','part_id')->withPivot('qty_per_unit'); }
public function movements(){ return $this->hasMany(StockMovement::class); }
public function scopeWithAvailable($q){
    return $q->withSum('movements as available_qty','qty'); // sum of ledger
}

public function stock()
{
    // Stock model me foreign key product_id hai
    return $this->hasOne(Stock::class, 'product_id', 'id');
}

  public function saleItems()
    {
        return $this->hasMany(SaleItem::class, 'product_id', 'id');
    }

    /**
     * Product has many purchase items
     */
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class, 'product_id', 'id');
    }

    /**
     * Product belongs to a Branch
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    /**
     * ✅ MULTI-BRANCH: Product branch codes (per branch item code)
     */
    public function branchProductCodes()
    {
        return $this->hasMany(BranchProductCode::class, 'product_id', 'id');
    }

    /**
     * ✅ Get branch-specific item code
     * Returns formatted code like "001", "002" for the given branch
     */
    public function getBranchItemCode($branchId)
    {
        $code = BranchProductCode::where('product_id', $this->id)
            ->where('branch_id', $branchId)
            ->first();
        
        return $code ? $code->item_code : null;
    }

    /**
     * ✅ Check if product is PRIMARY for a branch
     * PRIMARY: Has warehouse_stocks with quantity > 0
     */
    public function isPrimaryForBranch($branchId)
    {
        return $this->warehouseStocks()
            ->where('branch_id', $branchId)
            ->where('quantity', '>', 0)
            ->exists();
    }

    /**
     * ✅ Check if product is SECONDARY for a branch
     * SECONDARY: No warehouse_stocks entry for this branch
     */
    public function isSecondaryForBranch($branchId)
    {
        return !$this->warehouseStocks()
            ->where('branch_id', $branchId)
            ->exists();
    }

    /**
     * ✅ Get warehouse stocks for a specific branch
     */
    public function getStockForBranch($branchId)
    {
        return $this->warehouseStocks()
            ->where('branch_id', $branchId)
            ->sum('quantity');
    }
}
