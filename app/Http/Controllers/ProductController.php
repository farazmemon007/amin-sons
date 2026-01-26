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
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Milon\Barcode\DNS1D;


class ProductController extends Controller
{
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

    private function upsertStocks(int $productId, float $qtyDelta, int $branchId = 1, int $warehouseId = 1): void
    {
        // try update
        $updated = Stock::where([
            'branch_id'    => $branchId,
            'warehouse_id' => $warehouseId,
            'product_id'   => $productId,
        ])->update([
            'qty'        => DB::raw('qty + ('.($qtyDelta+0).')'),
            'updated_at' => now(),
        ]);

        if (!$updated) {
            // insert if row doesn't exist
            Stock::create([
                'branch_id'    => $branchId,
                'warehouse_id' => $warehouseId,
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

    $products = Product::with(['brand','stockproduct'])
        ->where(function ($query) use ($q) {
            $query->where('item_name', 'like', "%{$q}%")
                  ->orWhere('item_code', 'like', "%{$q}%")
                  ->orWhere('barcode_path', 'like', "%{$q}%")
                  ->orWhere('barcode_path', 'like', "%{$q}%");
        })
        ->get()
        ->map(function ($product) {

            return [
                'id'           => $product->id,
                'item_name'    => $product->item_name,
                'item_code'    => $product->item_code,
                'brand'        => $product->brand->name ?? '',
                'stock'        => $product->stockproduct->qty ?? 0, // ✅ FIX
                'retail_price' => $product->price ?? 0,
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

    $products = Product::with('stock')
        ->orderBy('id', 'asc')
        ->skip(($page - 1) * $perPage)
        ->take($perPage)
        ->get();

    return response()->json([
        'products' => $products,
        'last_id'  => $products->last()->id ?? null,
        'has_more' => $products->count() == $perPage,
        'page' => $page,
    ]);
}





    // ===== List page =====
    public function product()
    {
        $products = Product::with(['category_relation', 'sub_category_relation', 'unit', 'brand'])
            ->get()
            ->map(function ($product) {
                // Get stock onhand
                $stock = DB::table('v_stock_onhand')
                    ->where('product_id', $product->id)
                    ->first();
                
                $product->onhand_qty = $stock->onhand_qty ?? 0;
                return $product;
            });

        $categories = Category::get();
        return view('admin_panel.product.index', compact('products', 'categories'));
    }

 public function productview($id)
{
    $product = Product::with([
        'category_relation',
        'sub_category_relation',
        'brand',
        'stock'
    ])->find($id);

    return response()->json($product);
}

////////////////////////



///////////////////////////


    // ===== Create page =====
    public function view_store()
    {
        $categories = Category::select('id', 'name')->get();
        $units      = Unit::select('id', 'name')->get();
        $brands     = Brand::select('id', 'name')->get();
        return view('admin_panel.product.create', compact('categories', 'units', 'brands'));
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

    // ===== Store product =====
    public function store_product(Request $request)
    {
        if (!Auth::id()) return redirect()->back();

        $userId = Auth::id();

        // Auto item_code
        $lastProduct = Product::orderBy('id', 'desc')->first();
        $nextCode = $lastProduct ? ('ITEM-' . str_pad($lastProduct->id + 1, 4, '0', STR_PAD_LEFT)) : 'ITEM-0001';

        // Image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/products'), $filename);
            $imagePath = $filename;
        }

        DB::transaction(function () use ($request, $userId, $nextCode, $imagePath) {

            // Create product
            $product = Product::create([
                'creater_id'      => $userId,
                'category_id'     => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'item_code'       => $nextCode,
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
                'color'           => $request->color ? json_encode($request->color) : null,
                'is_part'         => $request->has('is_part') ? 1 : 0,
                'is_assembled'    => $request->has('is_assembled') ? 1 : 0,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            // Opening stock → single movement + stocks upsert
            $opening = (float) ($request->Stock ?? 0);
            if ($opening > 0) {
                // movement
                StockMovement::create([
                    'product_id' => $product->id,
                    'type'       => 'in',
                    'qty'        => $opening,
                    'ref_type'   => 'OPENING',
                    'ref_id'     => null,
                    'note'       => 'Opening stock',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                // stocks (branch/warehouse default if null)
                $this->upsertStocks(
                    productId: $product->id,
                    qtyDelta: $opening,
                    branchId: (int)($request->branch_id ?? 1),
                    warehouseId: (int)($request->warehouse_id ?? 2)
                );
            }

            // BOM save
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
        });

        return redirect()->back()->with('success', 'Product created successfully');
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
        $product = Product::with('category_relation', 'sub_category_relation', 'unit', 'brand')->findOrFail($id);
        $categories    = Category::all();
        $subcategories = SubCategory::all();
        $brands        = Brand::all();
        return view('admin_panel.product.edit', compact('product', 'categories', 'subcategories', 'brands'));
    }

    // ===== Barcode view =====
    public function barcode($id)
    {
        $product = Product::findOrFail($id);
        return view('admin_panel.product.barcode', compact('product'));
    }
}
