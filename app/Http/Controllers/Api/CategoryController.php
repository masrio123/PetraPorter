<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function fetchCategories(): JsonResponse
    {
        $categories = Category::all();

        return response()->json([
            'success' => true,
            'message' => 'List of categories',
            'data' => $categories
        ]);
    }

    public function getProductByTenant($id) {
        $products = Product::select([
            'products.id',
            'products.name as product_name',
            'product.price',
            'c.category_name',
            't.name as tenant',
            'products.isAvailable',
        ])
        ->join('categories as c','products.category_id','=','c.id')
        ->join('tenants as t','products.tenant_id','=','t.id')
        ->where('t.id', $id)
        ->get();

        return response()->json([
            'success'=> true,
            'message'=> 'Products grouped by tenants',
            'data' => $products
            ]);
     }
}

