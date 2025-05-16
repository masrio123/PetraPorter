<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function fetchAllProducts(): JsonResponse
    {
        $categories = Category::with('products')->get();

        $data = $categories->map(function ($category) {
            return [
                'id' => $category->id, // <-- penting untuk model Flutter
                'category_name' => $category->category_name,
                'products' => $category->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'price' => $product->price,
                        'is_available' => $product->isAvailable, // <-- sesuaikan key agar snake_case
                        'created_at' => $product->created_at,
                        'updated_at' => $product->updated_at,
                    ];
                })->toArray(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Products grouped by category',
            'data' => $data
        ]);
    }

    public function storeProduct(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string',
            'price' => 'required|numeric',
            'isAvailable' => 'required|boolean',
        ]);

        $product = Product::create($validated);
        $product->load('category'); // penting agar categoryName bisa dikembalikan

        return response()->json([
            'success' => true,
            'message' => 'Product added successfully',
            'data' => $product
        ]);
    }

    public function updateProduct(Request $request, $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'isAvailable' => 'sometimes|boolean',
            'category_id' => 'sometimes|exists:categories,id',
        ]);

        $product->update($validated);
        $product->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product
        ]);
    }


    public function deleteProduct($id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }

    public function toggleAvailability(Request $request, $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->isAvailable = $request->input('isAvailable');
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Availability updated successfully',
            'data' => $product,
        ]);
    }
}
