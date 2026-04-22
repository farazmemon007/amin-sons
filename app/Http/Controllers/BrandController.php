<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
class BrandController extends Controller
{
     public function index()
    {
        // $userId = Auth::id();
      $Brand = Brand::get();
      return  view("admin_panel.brand.index",compact('Brand'));


    }

    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|unique:brands,name,' . $request->edit_id,
    ]);

  if ($validator->fails()) {

    return redirect()->back()
        ->withErrors($validator)
        ->withInput()
        ->with('swal_error', $validator->errors()->first());
}


    // UPDATE
    if ($request->filled('edit_id')) {

        $brand = Brand::find($request->edit_id);

        if (!$brand) {
            return response()->json([
                'status' => 'error',
                'message' => 'Brand not found'
            ], 404);
        }

        $message = 'Brand Updated Successfully';
    }
    // CREATE
    else {
        $brand = new Brand();
        $message = 'Brand Created Successfully';
    }

    $brand->name = $request->name;
    $brand->save();

    // RESPONSE FOR ALERT
       if ($request->page === 'product_edit') {
        return redirect()
            ->route('products.edit', $request->product_id)
            ->with('success', 'Brand added successfully');

    }else if($request->page === 'product_page'){

    
    return redirect()
        ->route('store')
        ->with('success', 'Brand added successfully');
}

    // NORMAL RESPONSE
    return response()->json([
        'status' => 'success',
        'message' => $message,
        'reload' => true
    ]);
}


    public function delete($id)
    {

        $company = Brand::find($id);
        if ($company) {
            $company->delete();
            $msg = [
                'success' => 'Brand Deleted Successfully',
                'reload' =>  route('Brand.home'),
            ];
        } else {
            $msg = ['error' => 'Brand Not Found'];
        }
        return response()->json($msg);
    }
}
