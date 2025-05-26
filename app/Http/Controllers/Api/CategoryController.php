<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{

    public function fetchCategories(): JsonResponse
    {
        $categories = Category::select('id', 'category_name as name')->get();

        return response()->json([
            'success' => true,
            'message' => 'List of categories',
            'data' => $categories
        ]);
    }
}
