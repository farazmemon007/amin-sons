<?php

namespace App\Http\Controllers;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
class CategoryController extends Controller
{

    public function index()
    {
    $category = Category::get();
      
      return  view("admin_panel.category.index",compact('category'));
    }
    function catagorystore(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 422);
        }

        // Check if category already exists
        $exists = Category::where('name', $request->name)->exists();
        
        if ($exists) {
            return response()->json([
                'error' => 'Category name already exists'
            ], 409);
        }

        // Create new category
        $category = new Category();
        $category->name = $request->name;
        $category->save();

        return response()->json([
            'success' => 'Category Inserted Successfully'
        ]);
    }

    public function store(Request $request)
{

    // Validation
    $validator = Validator::make($request->all(), [
        'name' => 'required|unique:categories,name,' . $request->edit_id . ',id',
    ]);

     if ($validator->fails()) {

    return redirect()->back()
        ->withErrors($validator)
        ->withInput()
        ->with('catagory_swal_error', $validator->errors()->first());
}

    /**
     * UPDATE CATEGORY
     */
    if ($request->filled('edit_id')) {
        $category = Category::findOrFail($request->edit_id);
        $category->name = $request->name;
        $category->save();

        return response()->json([
            'success' => 'Category Updated Successfully',
            'reload'  => true
        ]);
    }

    /**
     * CREATE CATEGORY
     */
    $category = new Category();
    $category->name = $request->name;
    $category->save();

    /**
     * IF REQUEST FROM PRODUCT PAGE
     */
    $obj = Category::all();
       // RESPONSE FOR ALERT
       if ($request->page === 'product_edit') {
        return redirect()
            ->route('products.edit', $request->product_id)
            ->with('success', 'Category added successfully');

    }else if($request->page === 'product_page'){

    
    return redirect()
        ->route('store')
        ->with('success', 'Category added successfully');
}

    /**
     * NORMAL FLOW
     */
    return response()->json([
        'success'  => 'Category Created Successfully',
        'redirect' => route('Category.home')
    ]);
}

    public function delete($id)
    {

        $company = Category::find($id);
        if ($company) {
            $company->delete();
            $msg = [
                'success' => 'Category Deleted Successfully',
                'reload' =>  route('Category.home'),
            ];
        } else {
            $msg = ['error' => 'Category Not Found'];
        }
        return response()->json($msg);
    }


}
