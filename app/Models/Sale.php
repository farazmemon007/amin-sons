<?php

// app/Models/Sale.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Branch;
use App\Models\ProductBooking;
use Illuminate\Support\Facades\Auth;

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

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    /**
     * ✅ Direct relationship to source ProductBooking
     * Added after sale is created from booking
     */
    public function booking()
    {
        return $this->belongsTo(ProductBooking::class, 'booking_id', 'id');
    }

    /**
     * ✅ Find related draft-posted booking
     * Used to determine if sale needs warehouse selection before DC generation
     * 
     * IMPROVED (March 29, 2026):
     * - Now uses direct booking relationship (via booking_id column)
     * - Fallback to time-based matching for backward compatibility with old data
     */
    public function getDraftBooking()
    {
        // Method 1: Use direct booking_id relationship (NEW)
        if ($this->booking_id) {
            $booking = ProductBooking::find($this->booking_id);
            if ($booking && $booking->status === 'draft_posted') {
                return $booking;
            }
        }

        // Method 2: Fallback to time-based matching for backward compatibility
        return ProductBooking::where('customer_id', $this->customer_id)
            ->where('branch_id', $this->branch_id)
            ->where('status', 'draft_posted')
            ->where('created_at', '>=', $this->created_at->subHours(1))
            ->where('created_at', '<=', $this->created_at->addMinutes(10))
            ->latest('created_at')
            ->first();
    }

    
    public static function generateInvoiceNo()
    {
        // Backwards-compatible: accept optional branch context by reading auth user
        $branchId = Auth::check() ? (Auth::user()->branch_id ?? null) : null;

        if ($branchId) {
            $branch = Branch::find($branchId);
            $counter = $branch ? ((int) ($branch->invoice_counter ?? 0)) : 0;
            $next = $counter + 1;
            return 'INV-' . str_pad($next, 4, '0', STR_PAD_LEFT);
        }

        // Fallback: original global behavior
        $lastSale = self::orderBy('id', 'desc')->first();
        $next = 1;
        if ($lastSale && !empty($lastSale->invoice_no)) {
            if (preg_match('/(\d+)$/', $lastSale->invoice_no, $m)) {
                $next = (int) $m[1] + 1;
            }
        }
        return 'INV-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
