<?php

namespace App\Http\Controllers\Api;

use App\Models\Tenant;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

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

    public function getProductByTenant($id): JsonResponse
    {
        $tenant = Tenant::find($id);
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.',
                'data' => null,
            ], 404);
        }

        $products = Product::select([
            'products.id',
            'products.name',
            'products.price',
            'products.isAvailable',
            'c.id as category_id',
            'c.category_name',
        ])
            ->join('categories as c', 'products.category_id', '=', 'c.id')
            ->where('products.tenant_id', $id)
            ->get();

        $grouped = [];

        foreach ($products as $product) {
            $categoryId = $product->category_id;

            if (!isset($grouped[$categoryId])) {
                $grouped[$categoryId] = [
                    'id' => $product->category_id,
                    'category_name' => $product->category_name,
                    'products' => [],
                ];
            }

            $grouped[$categoryId]['products'][] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'is_available' => $product->isAvailable,
            ];
        }

        $data = [
            'tenant_name' => $tenant->name,
            'categories' => array_values($grouped),
        ];

        return response()->json([
            'data' => $data,
        ]);
    }
    public function storeProduct(Request $request)
    {
        try {
            $validated = $request->validate([
                'category_id'  => 'required|exists:categories,id',
                'tenant_id'    => 'required|exists:tenants,id',
                'name'         => 'required|string|max:255',
                'price'        => 'required|numeric|min:0',
                'isAvailable'  => 'required|boolean',
            ]);

            $product = Product::create($validated);
            $product->load('category');

            return response()->json([
                'success' => true,
                'message' => 'Product added successfully',
                'data'    => $product
            ], 201);
        } catch (ValidationException $e) {
            // Tangani kesalahan validasi
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Tangani error umum
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(), // hilangkan ini di production jika sensitif
            ], 500);
        }
    }

    public function updateProduct(Request $request, $id): JsonResponse
    {
        try {
            $product = Product::findOrFail($id);

            Log::info('Request all:', $request->all());

            $validated = $request->validate([
                'name' => 'sometimes|string',
                'price' => 'sometimes|numeric',
                'isAvailable' => 'sometimes|boolean',
            ]);

            Log::info('Validated data:', $validated);

            $product->update($validated);
            $product->load('category');

            Log::info('Product after update:', $product->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error update product:', ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    public function deleteProduct($productId): JsonResponse
    {
        try {
            $product = Product::findOrFail($productId);
            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil dihapus'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }
    }
}
