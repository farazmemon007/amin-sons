<?php

namespace App\Services;

use App\Models\BranchProductCode;
use App\Models\Product;
use App\Models\Branch;

class BranchProductCodeService
{
    /**
     * ✅ Generate or get branch-specific item code
     * Format: "001", "002", "003" etc per branch
     */
    public static function generateBranchItemCode(Product $product, $branchId)
    {
        // Check if already exists
        $existing = BranchProductCode::where('product_id', $product->id)
            ->where('branch_id', $branchId)
            ->first();

        if ($existing && $existing->item_code) {
            return $existing->item_code;
        }

        // Get next sequence for this branch
        $nextSequence = BranchProductCode::where('branch_id', $branchId)->max('sequence') ?? 0;
        $nextSequence++;

        // Format as "001", "002" etc
        $itemCode = str_pad($nextSequence, 3, '0', STR_PAD_LEFT);

        // Create or update
        if ($existing) {
            $existing->update([
                'item_code' => $itemCode,
                'sequence' => $nextSequence,
            ]);
        } else {
            BranchProductCode::create([
                'product_id' => $product->id,
                'branch_id' => $branchId,
                'item_code' => $itemCode,
                'sequence' => $nextSequence,
            ]);
        }

        return $itemCode;
    }

    /**
     * ✅ Update primary/secondary status based on warehouse_stocks
     */
    public static function updatePrimaryStatus(Product $product, $branchId)
    {
        $isPrimary = $product->isPrimaryForBranch($branchId);

        BranchProductCode::where('product_id', $product->id)
            ->where('branch_id', $branchId)
            ->update(['is_primary' => $isPrimary]);

        return $isPrimary;
    }

    /**
     * ✅ Initialize product codes for all branches (when creating new product)
     */
    public static function initializeProductForAllBranches(Product $product)
    {
        $branches = Branch::all();

        foreach ($branches as $branch) {
            self::generateBranchItemCode($product, $branch->id);
            self::updatePrimaryStatus($product, $branch->id);
        }
    }

    /**
     * ✅ Get products grouped by primary/secondary for a branch
     */
    public static function getProductsByStatus($branchId, $isPrimary = true)
    {
        return Product::whereHas('branchProductCodes', function ($query) use ($branchId, $isPrimary) {
            $query->where('branch_id', $branchId)
                  ->where('is_primary', $isPrimary);
        })->get();
    }
}
