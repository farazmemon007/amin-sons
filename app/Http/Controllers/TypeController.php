<?php

namespace App\Http\Controllers;

use App\Models\ProductType;
use Illuminate\Http\Request;

class TypeController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:product_types,name',
        ]);

        ProductType::create([
            'name' => $request->name,
        ]);

        $productTypes = ProductType::all();

        return response()->json($productTypes);
    }

    public function select(Request $request)
    {
        $productTypes = ProductType::all();

        return response()->json($productTypes);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:product_types,id',
            'name' => 'required|string|max:255|unique:product_types,name,' . $request->id,
        ]);

        $productType = ProductType::find($request->id);
        $productType->update([
            'name' => $request->name,
        ]);

        return response()->json(['success' => 'Product type updated successfully.']);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:product_types,id',
        ]);

        ProductType::find($request->id)->delete();

        return response()->json(['success' => 'Product type deleted successfully.']);
    }
}
