<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Brand;
use App\Models\Unit;
use App\Models\ProductBom;
use App\Models\StockMovement;
use App\Models\Stock;
use App\Models\WarehouseStock;
use App\Models\Warehouse;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Milon\Barcode\DNS1D;
use App\Http\Controllers\Concerns\BranchScope;


class ProductController extends Controller
{
    use BranchScope;
    public function getProductDetails($id){
        $product = Product::with(['stock'])->find($id);
        if ($product) {
            return response()->json([
                'status' => 'success',
                'product' => $product
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found'
            ], 404);
        }
    }

    public function getPrice(Request $request)
    {
        $product = Product::find($request->product_id);

        return response()->json([
            'retail_price' => $product?->price ?? 0
        ]);
        
    }

    public function productget()
    {
        $products=Product::all();
        return response()->json($products);
    }

    private function upsertStocks(int $productId, float $qtyDelta, int $branchId = 1): void
    {
        // try update
        $updated = Stock::where([
            'branch_id'    => $branchId,
            
            'product_id'   => $productId,
        ])->update([
            'qty'        => DB::raw('qty + ('.($qtyDelta+0).')'),
            'updated_at' => now(),
        ]);

        if (!$updated) {
            // insert if row doesn't exist
            Stock::create([
                'branch_id'    => $branchId,
               
                'product_id'   => $productId,
                'qty'          => $qtyDelta,
                'reserved_qty' => 0,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }



    public function assemblyReport(Request $request, $id)
    {
        // optional: user ne target units pass kiye (e.g. kitne banana chahte ho)
        $targetUnits = (float) $request->get('target', 0);

        // product + BOM rows
        $product = Product::findOrFail($id);
        $bomRows = ProductBom::with(['part:id,item_name,unit_id'])
            ->where('product_id', $id)
            ->get(['id', 'product_id', 'part_id', 'qty_per_unit']);

        // sab involved product_ids (finished + parts)
        $allIds = collect([$id])->merge($bomRows->pluck('part_id'))->unique()->values();

        // available map: product_id => available_qty
        $avail = StockMovement::whereIn('product_id', $allIds)
            ->selectRaw('product_id, COALESCE(SUM(qty),0) as qty_sum')
            ->groupBy('product_id')
            ->pluck('qty_sum', 'product_id');

        $readyStock = (float)($avail[$id] ?? 0);

        // assemblePossible = min(floor(available_part / qty_per_unit))
        $assemblePossible = $bomRows->count() ? $bomRows->map(function ($r) use ($avail) {
            $a = (float)($avail[$r->part_id] ?? 0);
            $rpu = (float)$r->qty_per_unit;
            return $rpu > 0 ? floor($a / $rpu) : INF;
        })->min() : 0;

        // agar user ne target diya hai to usko use karo; warna assemblePossible ko target maan lo
        $target = $targetUnits > 0 ? $targetUnits : $assemblePossible;

        // per-part breakdown
        $parts = $bomRows->map(function ($r) use ($avail, $target) {
            $available = (float)($avail[$r->part_id] ?? 0);
            $needed    = (float)$r->qty_per_unit * (float)$target;
            $shortage  = max(0, $needed - $available);
            return [
                'part_id'        => $r->part_id,
                'part_name'      => $r->part->item_name ?? 'N/A',
                'qty_per_unit'   => (float)$r->qty_per_unit,
                'available'      => $available,
                'needed'         => $needed,
                'shortage'       => $shortage,
            ];
        });

        // response
        return response()->json([
            'product_id'      => $product->id,
            'product_name'    => $product->item_name,
            'ready_stock'     => $readyStock,
            'assemble_possible' => (float)$assemblePossible,
            'total_sellable'  => (float)($readyStock + $assemblePossible),
            'target_used'     => (float)$target,
            'parts'           => $parts,
            'short_parts'     => $parts->filter(fn($p) => $p['shortage'] > 0)->values(),
        ]);
    }
    public function assemblySummary()
    {
        // sirf wo products jinke BOM rows hain (assembled)
        $assembledIds = ProductBom::select('product_id')->distinct()->pluck('product_id');

        // sab related product_ids (finished + unke parts)
        $partIds = ProductBom::whereIn('product_id', $assembledIds)->pluck('part_id');
        $allIds  = $assembledIds->merge($partIds)->unique()->values();

        // available map
        $avail = StockMovement::whereIn('product_id', $allIds)
            ->selectRaw('product_id, COALESCE(SUM(qty),0) as qty_sum')
            ->groupBy('product_id')
            ->pluck('qty_sum', 'product_id');

        // build rows
        $rows = $assembledIds->map(function ($pid) use ($avail) {
            $p = Product::find($pid);
            $bom = ProductBom::where('product_id', $pid)->get();
            if (!$p || $bom->isEmpty()) {
                return null;
            }
            $assemblePossible = $bom->map(function ($r) use ($avail) {
                $a   = (float)($avail[$r->part_id] ?? 0);
                $rpu = (float)$r->qty_per_unit;
                return $rpu > 0 ? floor($a / $rpu) : INF;
            })->min();
            $ready = (float)($avail[$pid] ?? 0);

            return [
                'product_id'        => $pid,
                'product_name'      => $p->item_name,
                'ready_stock'       => $ready,
                'assemble_possible' => (float)$assemblePossible,
                'total_sellable'    => (float)($ready + $assemblePossible),
            ];
        })->filter()->values();

        return view('admin_panel.product.assembly_summary', compact('rows'));
    }





    // ===== Product search (general) =====


public function searchProducts(Request $request)
{
    $q = $request->get('q');

    // ✅ GLOBAL PRODUCTS - Show all products from all branches
    // But mark Primary/Secondary based on login branch stock
    $query = Product::with(['brand','unit','stockproduct']);

    // NO branch restriction - products are GLOBAL

    $products = $query
        ->where(function ($query) use ($q) {
            $query->where('item_name', 'like', "%{$q}%")
                  ->orWhere('item_code', 'like', "%{$q}%");
        })
        ->get()
        ->map(function ($product) {
            // ✅ Check if product has stock in login branch
            $userBranchId = Auth::check() ? Auth::user()->branch_id : null;
            $isPrimary = false;
            
            if ($userBranchId) {
                // Check stocks table for branch-level stock
                $stockRecord = DB::table('stocks')
                    ->where('product_id', $product->id)
                    ->where('branch_id', $userBranchId)
                    ->first();
                
                // Product is Primary if qty > 0 in login branch
                $isPrimary = ($stockRecord && $stockRecord->qty > 0);
            }
            
            return [
                'id'           => $product->id,
                'item_name'    => $product->item_name,
                'item_code'    => $product->item_code,
                'brand_name'   => $product->brand->name ?? '',
                'unit_name'    => $product->unit->name ?? '',
                'unit_id'      => $product->unit_id,
                'stock'        => $product->stockproduct->qty ?? 0,
                'price'        => $product->price ?? 0,
                'is_primary'   => $isPrimary,  // ✅ NEW - Primary/Secondary status
            ];
        });

    return response()->json($products);
}

public function searchProductsForSalebypagination(Request $request)
{
    // Use page-based pagination which integrates cleanly with Select2's
    // `params.page` value. This avoids fragile last_id state handling.
    $page = max(1, (int) $request->get('page', 1));
    $perPage = 10;

    // Allow searching by product name or code
    $q = trim((string) $request->get('q', ''));

    // ✅ GLOBAL PRODUCTS - Show all products from all branches
    // But mark Primary/Secondary based on login branch stock
    $query = Product::with(['stock', 'brand', 'unit']);

    // NO branch restriction - products are GLOBAL

    if ($q !== '') {
        $query->where(function ($builder) use ($q) {
            $builder->where('item_name', 'like', "%{$q}%")
                    ->orWhere('item_code', 'like', "%{$q}%");
        });
    }

    $products = $query
        ->orderBy('id', 'asc')
        ->skip(($page - 1) * $perPage)
        ->take($perPage)
        ->get();

    // Add ownership information and Primary/Secondary status for each product
    $userBranchId = Auth::check() ? Auth::user()->branch_id : null;
    $productsWithOwnership = $products->map(function($p) use ($userBranchId) {
        // ✅ Check if product has stock in login branch
        $isPrimary = false;
        if ($userBranchId) {
            // Check stocks table for branch-level stock
            $stockRecord = DB::table('stocks')
                ->where('product_id', $p->id)
                ->where('branch_id', $userBranchId)
                ->first();
            
            // Product is Primary if qty > 0 in login branch
            $isPrimary = ($stockRecord && $stockRecord->qty > 0);
        }
        
        return [
            'id' => $p->id,
            'item_name' => $p->item_name,
            'item_code' => $p->item_code,
            'stock' => $p->stock,
            'price' => $p->price,
            'retail_price' => $p->retail_price ?? $p->price,
            'branch_id' => $p->branch_id,
            'is_owner' => ($userBranchId && $p->branch_id == $userBranchId),
            'brand_name' => $p->brand->name ?? '',
            'unit_name' => $p->unit->name ?? '',
            'unit_id' => $p->unit_id,
            'is_primary' => $isPrimary,  // ✅ NEW - Primary/Secondary status
        ];
    });

    return response()->json([
        'products' => $productsWithOwnership,
        'last_id'  => $products->last()->id ?? null,
        'has_more' => $products->count() == $perPage,
        'page' => $page,
        'user_branch_id' => $userBranchId,
    ]);
}





    // ===== List page =====
    

    public function product()
    {
        // Get current user's branch
        $userBranch = Auth::check() ? Auth::user()->branch_id : null;
        $isSuperAdmin = Auth::check() ? Auth::user()->hasRole('super admin') : false;

        // Determine which branches the current user is allowed to see
        $allowedBranchIds = [];

        if (Auth::check()) {
            $user = Auth::user();

            if ($user->hasRole('super admin')) {
                // super admin sees all branches
                $allowedBranchIds = Branch::pluck('id')->toArray();
            } else {
                // ✅ IMPORTANT: Only include user's own branch + explicitly granted permissions
                // always include own branch
                $allowedBranchIds[] = $user->branch_id;

                // ✅ Check for explicit branch-view permissions
                // permission name pattern: branch.wise.product.view.{id}
                // This ensures users don't see other branches unless explicitly granted
                $branchIds = Branch::pluck('id');
                foreach ($branchIds as $bid) {
                    if ($bid !== $user->branch_id && $user->can('branch.wise.product.view.' . $bid)) {
                        $allowedBranchIds[] = $bid;
                    }
                }

                // make unique
                $allowedBranchIds = array_unique($allowedBranchIds);
            }
        }

        // Build query with relationships - INCLUDE branch product codes
        $query = Product::with([
            'category_relation', 
            'sub_category_relation', 
            'unit', 
            'brand', 
            'branch',
            'branchProductCodes', // ✅ Load branch-specific codes
            'warehouseStocks' // ✅ Load all warehouse stocks for super admin
        ]);

        if (!empty($allowedBranchIds)) {
            $query->whereIn('branch_id', $allowedBranchIds);
        } else {
            // no branch allowed, return empty
            $query->whereRaw('1=0');
        }

        $products = $query->get()
            ->map(function ($product) use ($userBranch, $isSuperAdmin) {
                // ✅ FOR SUPER ADMIN: Show all branches' stock information
                if ($isSuperAdmin) {
                    // Super admin: Show products from their primary branch first, then all warehouse stocks
                    $product->branch_item_code = $product->getBranchItemCode($product->branch_id);
                    $product->is_primary = $product->isPrimaryForBranch($product->branch_id);
                    $product->is_secondary = $product->isSecondaryForBranch($product->branch_id);
                    $product->branch_stock_qty = $product->getStockForBranch($product->branch_id);
                    
                    // For super admin, get all warehouse stocks across branches from loaded relationship
                    // Group by branch_id and sum quantities
                    $stocksByBranch = [];
                    if ($product->warehouseStocks && $product->warehouseStocks->count() > 0) {
                        foreach ($product->warehouseStocks as $ws) {
                            if (!isset($stocksByBranch[$ws->branch_id])) {
                                $stocksByBranch[$ws->branch_id] = [
                                    'branch_id' => $ws->branch_id,
                                    'branch_name' => $ws->branch->name ?? 'Unknown',
                                    'quantity' => 0
                                ];
                            }
                            $stocksByBranch[$ws->branch_id]['quantity'] += $ws->quantity;
                        }
                    }
                    $product->all_warehouse_stocks = array_values($stocksByBranch);
                    $product->user_branch_stock_qty = 0;
                    $product->has_stock_in_user_branch = false;
                } elseif ($userBranch) {
                    // ✅ FOR REGULAR USERS: Show only their branch data with correct item code
                    // Get branch-specific item code from BranchProductCode table
                    $product->branch_item_code = $product->getBranchItemCode($userBranch);
                    
                    // ✅ ERP STANDARD: PRIMARY/SECONDARY STATUS based on warehouse_stocks
                    // PRIMARY: Product has warehouse_stocks entry with quantity > 0 for this branch
                    // SECONDARY: Product exists globally but NO warehouse_stocks for this branch yet
                    $product->is_primary = $product->isPrimaryForBranch($userBranch);
                    $product->is_secondary = $product->isSecondaryForBranch($userBranch);
                    
                    // Get total warehouse stock for this branch
                    $product->branch_stock_qty = $product->getStockForBranch($userBranch);

                    // Check stock in user's branch from stocks table (aggregate branch-level stock)
                    $branchStock = Stock::where([
                        'product_id' => $product->id,
                        'branch_id' => $userBranch
                    ])->first();
                    $product->user_branch_stock_qty = $branchStock?->qty ?? 0;
                    $product->has_stock_in_user_branch = ($branchStock && $branchStock->qty > 0);
                    $product->all_warehouse_stocks = null;
                } else {
                    $product->branch_item_code = null;
                    $product->is_primary = false;
                    $product->is_secondary = true;
                    $product->branch_stock_qty = 0;
                    $product->user_branch_stock_qty = 0;
                    $product->has_stock_in_user_branch = false;
                    $product->all_warehouse_stocks = null;
                }
                return $product;
            });

        $categories = Category::get();
        // supply branch list for add modal (super admin can choose branch)
        $branchesList = Branch::get();
        
        // pass userBranch, isSuperAdmin, and allowedBranchIds to view
        return view('admin_panel.product.index', compact('products', 'categories', 'allowedBranchIds', 'branchesList', 'userBranch', 'isSuperAdmin'));
    }

 public function productview($id)
{
    // ✅ ERP PROPER: Include branch relationship for super admin display
    $product = Product::with([
        'category_relation',
        'sub_category_relation',
        'brand',
        'unit',
        'stock',
        'branch'  // Include branch for super admin visibility
    ])->find($id);

    return response()->json($product);
}

////////////////////////



///////////////////////////


    // ===== Create page =====
    public function view_store()
    {
        // ✅ ERP STANDARD: Role-based branch visibility
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super admin');
        
        // Build branch options based on role
        if ($isSuperAdmin) {
            // Super admin sees all branches
            $branches = Branch::all();
        } else {
            // Regular user only sees their own branch
            $userBranch = $user->branch_id ? Branch::find($user->branch_id) : null;
            $branches = $userBranch ? collect([$userBranch]) : collect();
        }
        
        $categories = Category::select('id', 'name')->get();
        $units      = Unit::select('id', 'name')->get();
        $brands     = Brand::select('id', 'name')->get();
        $types      = \App\Models\ProductType::all();
        $warehouses = Warehouse::with('branches')->get();
        
        return view('admin_panel.product.create', compact(
            'categories', 'units', 'brands', 'types', 'branches', 'warehouses',
            'isSuperAdmin', 'user'
        ));
    }

    // ===== Dependent subcategories =====
    public function getSubcategories($category_id)
    {
        $subcategories = Subcategory::where('category_id', $category_id)->get();
        return response()->json($subcategories);
    }

    // ===== Barcode =====
    public function generateBarcode(Request $request)
    {
        $barcodeNumber = $request->filled('code') ? $request->code : rand(100000000000, 999999999999);
        $barcodePNG    = (new DNS1D)->getBarcodePNG($barcodeNumber, 'C39', 3, 50);
        $barcodeImage  = "data:image/png;base64," . $barcodePNG;

        return response()->json([
            'barcode_number' => $barcodeNumber,
            'barcode_image'  => $barcodeImage
        ]);
    }

    // ===== Store product (2-Phase Workflow) =====
    // ✅ PHASE 1: Basic product profile (name, code, brand, unit, category, type)
    // ✅ PHASE 2: Opening stock configuration (opening_stock, alert_qty, prices, warehouse assignments)
    public function store_product(Request $request)
    {       
        if (!Auth::id()) return redirect()->back();

        $userId = Auth::id();
        $phase = $request->input('phase', 'phase1'); // Default to Phase 1

        // ============ PHASE 1: CREATE BASIC PRODUCT PROFILE ============
        if ($phase === 'phase1') {
            return $this->storeProductPhase1($request, $userId);
        }

        // ============ PHASE 2: CONFIGURE OPENING STOCK & WAREHOUSE ASSIGNMENTS ============
        if ($phase === 'phase2') {
            return $this->storeProductPhase2($request);
        }

        return redirect()->back()->with('error', 'Invalid phase specified');
    }

    /**
     * PHASE 1: Store Basic Product Profile Only
     * Creates product with: name, code, category, brand, unit, image, is_part, is_assembled
     * Sets completion_status = 'profile_only'
     * Redirects to opening stock form
     */
    private function storeProductPhase1(Request $request, int $userId)
    {
        // Image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/products'), $filename);
            $imagePath = $filename;
        }

        $productId = DB::transaction(function () use ($request, $userId, $imagePath) {

            // ============ BRANCH ASSIGNMENT LOGIC (ERP BUSINESS LOGIC) ============
            $branchId = 1; // Default fallback
            
            if (Auth::check()) {
                $user = Auth::user();
                
                // Super admin can select branch via form
                if ($user->hasRole('super admin')) {
                    $branchId = (int) ($request->input('branch_id') ?? $user->branch_id ?? 1);
                    $branch = Branch::find($branchId);
                    if (!$branch) {
                        $branchId = 1;
                    }
                } else {
                    // Regular users: use their own branch
                    $branchId = (int) ($user->branch_id ?? 1);
                }
            }

            // Generate per-branch item_code
            $lastForBranch = Product::where('branch_id', $branchId)
                ->whereNotNull('item_code')
                ->where('item_code', 'like', 'ITEM-%')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastForBranch && preg_match('/ITEM-0*([0-9]+)$/', $lastForBranch->item_code, $matches)) {
                $seq = intval($matches[1]) + 1;
            } else {
                $seq = 1;
            }
            $nextCode = 'ITEM-' . str_pad($seq, 4, '0', STR_PAD_LEFT);

            // Handle packing type logic
            $packingType = $request->packing_type;
            $packQty = 0;
            $piecePerPack = 0;
            $loosePiece = 0;

            if ($packingType === 'Customize') {
                $packQty = (float) ($request->packing_qty ?? 0);
                $piecePerPack = (float) ($request->piece_per_pack ?? 0);
                $loosePiece = (float) ($request->loose_piece ?? 0);
            }

            // ✅ PHASE 1: Create product form basic profile only
            // SKIP: opening_stock, prices, warehouse_assignments
            $product = Product::create([
                'branch_id'       => $branchId,
                'creater_id'      => $userId,
                'category_id'     => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'type_id'         => $request->type_id,
                'item_code'       => $nextCode,
                'item_name'       => $request->product_name,
                'barcode_path'    => $request->barcode_path ?? rand(100000000000, 999999999999),
                'unit_id'         => $request->unit,
                'brand_id'        => $request->brand_id,
                'wholesale_price' => 0,              // ← PHASE 2
                'price'           => 0,              // ← PHASE 2
                'alert_quantity'  => 0,              // ← PHASE 2
                'model'           => $request->model,
                'hs_code'         => $request->hs_code,
                'pack_type'       => $packingType,
                'pack_qty'        => $packQty,
                'piece_per_pack'  => $piecePerPack,
                'loose_piece'     => $loosePiece,
                'image'           => $imagePath,
                'color'           => $request->color ? json_encode($request->color) : null,
                'is_part'         => $request->has('is_part') ? 1 : 0,
                'is_assembled'    => $request->has('is_assembled') ? 1 : 0,
                'completion_status' => 'profile_only', // ← Mark as incomplete
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            // BOM save (Phase 1: can configure BOM with profile)
            if ($request->boolean('is_assembled') && $request->filled('bom_json')) {
                $rows = collect(json_decode($request->bom_json, true))
                    ->filter(fn($r) => !empty($r['part_id']) && (float)($r['required_per_unit'] ?? 0) > 0)
                    ->map(fn($r) => [
                        'product_id'   => $product->id,
                        'part_id'      => $r['part_id'],
                        'qty_per_unit' => (float)$r['required_per_unit'],
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ])->values();

                if ($rows->count()) {
                    DB::table('product_boms')->insert($rows->all());
                }
            }

            return $product->id;
        });

        // ✅ Redirect to product create page (store route) instead of opening stock form
        // User will manually open opening stock form from navbar
        return redirect()->route('store')
                         ->with('success', 'Product profile created successfully! You can now configure opening stock from "Opening Stocks" in the navbar.');
    }

    /**
     * PHASE 2: Configure Opening Stock, Alert Qty, Prices, Warehouse Assignments
     * Updates existing product with completion details
     * Sets completion_status = 'complete'
     */
    private function storeProductPhase2(Request $request)
    {
        $productId = $request->input('product_id');
        $product = Product::findOrFail($productId);

        // ✅ OWNERSHIP CHECK: Prevent cross-branch updates
        $userBranchId = Auth::check() ? Auth::user()->branch_id : null;
        $isSuperAdmin = Auth::check() && Auth::user()->hasRole('super admin');
        
        if (!$isSuperAdmin && $userBranchId !== $product->branch_id) {
            return redirect()->back()->with('error', 'You can only complete products from your own branch');
        }

        DB::transaction(function () use ($request, $product) {
            // Update product with Phase 2 data
            $product->update([
                'alert_quantity'  => (int) ($request->alert_quantity ?? 0),
                'wholesale_price' => (float) ($request->wholesale_price ?? 0),
                'price'           => (float) ($request->retail_price ?? 0),
                'initial_stock'   => (float) ($request->opening_stock ?? 0),  // ✅ ERP STANDARD: Store opening stock
                'completion_status' => 'complete', // ← Mark as complete
                'updated_at'      => now(),
            ]);

            $opening = (float) ($request->opening_stock ?? 0);

            // ✅ Parse allocation data (multi-row allocation system)
            $allocationDataJson = $request->input('allocation_data', '[]');
            $allocationData = json_decode($allocationDataJson, true) ?? [];

            // Opening stock → single movement + stocks upsert
            if ($opening > 0) {
                // Create stock movement with branch tracking
                StockMovement::create([
                    'product_id' => $product->id,
                    'branch_id'  => $product->branch_id,  // ✅ ERP STANDARD: Track which branch
                    'type'       => 'in',
                    'qty'        => $opening,
                    'ref_type'   => 'OPENING',
                    'ref_id'     => null,
                    'note'       => 'Opening stock',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // ✅ Update stocks table for branch (ERP Standard: dual-table sync)
                // For multi-allocation, we use branch-level only (no warehouse_id in stocks table)
                $this->upsertStocks(
                    productId: $product->id,
                    qtyDelta: $opening,
                    branchId: $product->branch_id,
                );
            }

            // ✅ ERP STANDARD: Create warehouse_stocks entries
            $branch = Branch::find($product->branch_id);
            $allWarehouses = $branch ? $branch->warehouses : collect();

            // ✅ STEP 1: Create branch-level entry (warehouse_id = NULL)
            $branchLevelQty = 0;
            
            // Calculate branch-level quantity from allocations
            foreach ($allocationData as $allocation) {
                if ($allocation['location_type'] === 'shop') {
                    $branchLevelQty += (float) ($allocation['quantity'] ?? 0);
                }
            }

            WarehouseStock::firstOrCreate([
                'branch_id'    => $product->branch_id,
                'warehouse_id' => null,
                'product_id'   => $product->id,
            ], [
                'quantity' => $branchLevelQty,
                'price'    => $request->retail_price ?? 0,
            ]);

            // ✅ STEP 2: Create entries for ALL warehouses in the branch
            foreach ($allWarehouses as $warehouse) {
                // Calculate warehouse quantity from allocations
                $warehouseQuantity = 0;
                
                foreach ($allocationData as $allocation) {
                    if ($allocation['location_type'] === 'warehouse' && 
                        (int)($allocation['warehouse_id'] ?? 0) === $warehouse->id) {
                        $warehouseQuantity += (float) ($allocation['quantity'] ?? 0);
                    }
                }

                WarehouseStock::firstOrCreate([
                    'branch_id'    => $product->branch_id,
                    'warehouse_id' => $warehouse->id,
                    'product_id'   => $product->id,
                ], [
                    'quantity' => $warehouseQuantity,
                    'price'    => $request->retail_price ?? 0,
                ]);
            }
        });

        return redirect()->route('product')
                         ->with('success', 'Product opening stock configured successfully!');
    }


    // ===== Parts search (for BOM modal) with real available qty =====
    //     public function searchPartName(Request $request)
    // {
    //     $q = $request->get('q', '');

    //     $parts = Product::where('is_part', 1)
    //         ->leftJoin('stocks', 'stocks.product_id', '=', 'products.id')
    //         ->where(function ($x) use ($q) {
    //             $x->where('products.item_name', 'like', "%{$q}%")
    //               ->orWhere('products.item_code', 'like', "%{$q}%");
    //         })
    //         ->groupBy('products.id', 'products.item_name', 'products.item_code', 'products.unit_id')
    //         ->selectRaw('products.id, products.item_name, products.item_code, products.unit_id, COALESCE(SUM(stocks.qty),0) as available_qty')
    //         ->limit(20)
    //         ->get();

    //     return response()->json($parts->map(function ($p) {
    //         return [
    //             'id'            => $p->id,
    //             'item_name'     => $p->item_name,
    //             'item_code'     => $p->item_code,
    //             'unit'          => optional(Unit::find($p->unit_id))->name ?? '',
    //             'available_qty' => (float)$p->available_qty,
    //         ];
    //     }));
    // }

    /**
     * PHASE 2 (FORM): Show Opening Stock Configuration Form
     * For products with completion_status = 'profile_only'
     */
    public function createOpeningStock($product_id)
    {
        $product = Product::with(['unit', 'brand', 'category_relation'])
                          ->where('completion_status', 'profile_only')
                          ->findOrFail($product_id);

        // ✅ OWNERSHIP CHECK
        $userBranchId = Auth::check() ? Auth::user()->branch_id : null;
        $isSuperAdmin = Auth::check() && Auth::user()->hasRole('super admin');
        
        if (!$isSuperAdmin && $userBranchId !== $product->branch_id) {
            abort(403, 'You can only complete products from your own branch');
        }

        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super admin');
        
        // Get warehouses for the product's branch
        $branch = $product->branch;
        $warehouses = $branch ? $branch->warehouses : collect();
        
        return view('admin_panel.product.opening-stock', compact(
            'product', 'warehouses', 'isSuperAdmin', 'user'
        ));
    }

    /**
     * Index: Show all incomplete products (completion_status = 'profile_only')
     * For navbar "Opening Stocks" link
     */
    public function incompleteProducts()
    {
        $userBranch = Auth::check() ? Auth::user()->branch_id : null;

        $query = Product::where('completion_status', 'profile_only')
                        ->with(['unit', 'brand', 'category_relation', 'branch']);

        if (Auth::check() && !Auth::user()->hasRole('super admin')) {
            $query->where('branch_id', $userBranch);
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(50);

        return view('admin_panel.product.incomplete-products', compact('products', 'userBranch'));
    }

    public function searchPartName(Request $request)
    {
        $q = $request->get('q', '');

        $parts = Product::query()
            ->where('is_part', 1)
            ->leftJoin('v_stock_onhand as v', 'v.product_id', '=', 'products.id')
            ->where(function ($x) use ($q) {
                $x->where('products.item_name', 'like', "%{$q}%")
                    ->orWhere('products.item_code', 'like', "%{$q}%");
            })
            ->select([
                'products.id',
                'products.item_name',
                'products.item_code',
                'products.unit_id',
                DB::raw('COALESCE(v.onhand_qty,0) as available_qty'),
            ])
            ->limit(20)
            ->get();

        return response()->json($parts->map(function ($p) {
            return [
                'id'            => $p->id,
                'item_name'     => $p->item_name,
                'item_code'     => $p->item_code,
                'unit'          => optional(Unit::find($p->unit_id))->name ?? '',
                'available_qty' => (float)$p->available_qty,
            ];
        }));
    }



    // ===== Update product =====
    public function update(Request $request, $id)
    {
        // ✅ OWNERSHIP CHECK: Prevent non-owners from updating
        $product = Product::findOrFail($id);
        $userBranchId = Auth::check() ? Auth::user()->branch_id : null;
        $isOwner = ($userBranchId && $product->branch_id == $userBranchId);
        $isSuperAdmin = Auth::check() && Auth::user()->hasRole('super admin');
        
        if (!$isOwner && !$isSuperAdmin) {
            abort(403, 'You can only edit products from your own branch');
        }
        
        // dd($request->all());
        $userId = auth()->id();

        // image handle
        $imagePath = Product::where('id', $id)->value('image');
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/products'), $imageName);
            $imagePath = $imageName; // keep only filename for consistency
        }

        DB::transaction(function () use ($request, $id, $userId, $imagePath) {

            Product::where('id', $id)->update([
                'creater_id'      => $userId,
                'category_id'     => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'type_id'         => $request->type_id,
                'item_code'       => $request->item_code,
                'item_name'       => $request->product_name,
                'barcode_path'    => $request->barcode_path ?? rand(100000000000, 999999999999),
                'unit_id'         => $request->unit,
                'brand_id'        => $request->brand_id,
                'wholesale_price' => $request->wholesale_price,
                'price'           => $request->retail_price,
                'alert_quantity'  => $request->alert_quantity,
                'model'  => $request->model,
                'hs_code'  => $request->hs_code,
                'pack_type'       =>  $request->packing_type,
                'pack_qty'       =>  $request->packing_qty,
                'piece_per_pack'   =>  $request->piece_per_pack,
                'loose_piece'   =>  $request->loose_piece,
                'image'           => $imagePath,
                'is_part'         => $request->has('is_part') ? 1 : 0,
                'is_assembled'    => $request->has('is_assembled') ? 1 : 0,
                'updated_at'      => now(),
            ]);

            // BOM re-save (replace all for this product)
            DB::table('product_boms')->where('product_id', $id)->delete();

            if ($request->has('is_assembled') && $request->is_assembled && $request->filled('bom_json')) {
                $rows = collect(json_decode($request->bom_json, true))
                    ->filter(fn($r) => !empty($r['part_id']) && (float)($r['required_per_unit'] ?? 0) > 0)
                    ->map(fn($r) => [
                        'product_id'   => $id,
                        'part_id'      => $r['part_id'],
                        'qty_per_unit' => (float)$r['required_per_unit'],
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ])->values();

                if ($rows->count()) {
                    DB::table('product_boms')->insert($rows->all());
                }
            }

            // Optional: stock adjustment field handle
            if ($request->filled('stock_adjust') && (float)$request->stock_adjust != 0) {
                StockMovement::create([
                    'product_id' => $id,
                    'type'       => 'adjustment',
                    'qty'        => (float)$request->stock_adjust, // can be negative
                    'ref_type'   => 'ADJ',
                    'note'       => 'Manual stock adjustment',
                ]);
            }
        });

        return redirect()->back()->with('success', 'Product updated successfully');
    }

    // ===== Edit view =====
    public function edit($id)
    {
        $product = Product::with('category_relation', 'sub_category_relation', 'unit', 'brand','stock')->findOrFail($id);
        
        // ✅ OWNERSHIP CHECK: Prevent non-owners from editing
        $userBranchId = Auth::check() ? Auth::user()->branch_id : null;
        $isOwner = ($userBranchId && $product->branch_id == $userBranchId);
        $isSuperAdmin = Auth::check() && Auth::user()->hasRole('super admin');
        
        if (!$isOwner && !$isSuperAdmin) {
            abort(403, 'You can only edit products from your own branch');
        }
        
        $categories    = Category::all();
        $subcategories = SubCategory::all();
        $brands        = Brand::all();
        $units         = Unit::select('id', 'name')->get();
        return view('admin_panel.product.edit', compact('product', 'categories', 'subcategories', 'brands', 'units'));
    }

    // ===== Barcode view =====
    public function barcode($id)
    {
        $product = Product::findOrFail($id);
        return view('admin_panel.product.barcode', compact('product'));
    }

    // ===== Get warehouses with product stock =====
    public function warehouses(Request $request)
    {
        try {
            $productId = $request->query('product_id');
            
            if (!$productId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product ID required'
                ], 400);
            }

            // Get warehouses with available stock for this product
            $warehouses = DB::table('warehouse_stocks as ws')
                ->join('warehouses as w', 'ws.warehouse_id', '=', 'w.id')
                ->where('ws.product_id', $productId)
                ->where('ws.quantity', '>', 0)
                ->select(
                    'ws.warehouse_id',
                    'w.warehouse_name',
                    'ws.quantity'
                )
                ->get();

            return response()->json([
                'success' => true,
                'warehouses' => $warehouses
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading warehouses: ' . $e->getMessage()
            ], 500);
        }
    }

    // ===== Stock locations report view =====
    public function stockLocations()
    {
        $allowedBranchIds = $this->getAllowedBranchIds();
        $branches = Branch::whereIn('id', $allowedBranchIds)->get();

        // Show branch selector to super admin or when user has access to multiple branches.
        $showBranchSelect = false;
        if (Auth::check() && Auth::user()->hasRole('super admin')) {
            $showBranchSelect = true;
        } elseif ($branches->count() > 1) {
            $showBranchSelect = true;
        }

        $selectedBranchId = null;
        if (!$showBranchSelect) {
            $selectedBranchId = $branches->first()->id ?? null;
        }

        return view('admin_panel.stock.locations', compact('branches', 'showBranchSelect', 'selectedBranchId'));
    }

    // ===== Stock locations data (for selected branch + product) =====
    public function stockLocationsData(Request $request)
    {
        $branchId = (int) $request->query('branch_id');
        $productId = (int) $request->query('product_id');

        if (!$branchId || !$productId) {
            return response()->json([
                'success' => false,
                'message' => 'Branch and product are required'
            ], 400);
        }

        // Ensure the user is allowed to view this branch
        $allowedBranchIds = $this->getAllowedBranchIds();
        if (!in_array($branchId, $allowedBranchIds, true)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view stock for this branch.'
            ], 403);
        }

        try {
            // Branch stock (warehouse_id = NULL)
            $branchStock = DB::table('warehouse_stocks')
                ->where('branch_id', $branchId)
                ->whereNull('warehouse_id')
                ->where('product_id', $productId)
                ->select(DB::raw("'Branch Stock' as location"), 'quantity')
                ->first();

            // Warehouse stocks
            $warehouseStocks = DB::table('warehouse_stocks as ws')
                ->leftJoin('warehouses as w', 'ws.warehouse_id', '=', 'w.id')
                ->where('ws.branch_id', $branchId)
                ->where('ws.product_id', $productId)
                ->whereNotNull('ws.warehouse_id')
                ->select(DB::raw("COALESCE(w.warehouse_name, 'Unknown Warehouse') as location"), 'ws.quantity')
                ->get();

            $locations = [];
            if ($branchStock) {
                $locations[] = ['location' => $branchStock->location, 'quantity' => (float)$branchStock->quantity];
            }

            foreach ($warehouseStocks as $row) {
                $locations[] = ['location' => $row->location, 'quantity' => (float)$row->quantity];
            }

            // If no records exist, show an empty row (handled in JS)
            return response()->json([
                'success' => true,
                'locations' => $locations,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading stock locations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Determine which branches the current user is allowed to access.
     *
     * Super admin: all branches.
     * Other users: own branch + any branches granted via 'branch.wise.product.view.{id}' permissions.
     */
    private function getAllowedBranchIds(): array
    {
        if (!Auth::check()) {
            return [];
        }

        $user = Auth::user();

        if ($user->hasRole('super admin')) {
            return Branch::pluck('id')->toArray();
        }

        $allowed = [$user->branch_id];
        $branchIds = Branch::pluck('id');
        foreach ($branchIds as $bid) {
            if ($user->can('branch.wise.product.view.' . $bid)) {
                $allowed[] = $bid;
            }
        }

        return array_unique($allowed);
    }
        // ===== Check for duplicate product name and model =====
    public function checkProductName(Request $request)
    {
        $productName = $request->query('name');
        $productModel = $request->query('model');

        if (!$productName && !$productModel) {
            return response()->json(['exists' => false]);
        }

        $exists = false;

        // Check for duplicate name
        if ($productName) {
            $exists = Product::where('item_name', $productName)->exists();
        }

        // Check for duplicate model
        if (!$exists && $productModel) {
            $exists = Product::where('model', $productModel)->exists();
        }

        return response()->json(['exists' => $exists]);
    }

}
