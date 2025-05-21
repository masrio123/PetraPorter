<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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

    public function getProductByTenant($id)
    {
        $products = Product::select([
            'products.id',
            'products.name as product_name',
            'products.price',
            'c.category_name',
            't.name as tenant',
            'products.isAvailable',
        ])
            ->join('categories as c', 'products.category_id', '=', 'c.id')
            ->join('tenants as t', 'products.tenant_id', '=', 't.id')
            ->where('t.id', $id)
            ->get();

        $data = array();

        foreach ($products as $product) {
            if (!isset($data[$product['category_name']])) {
                $data[$product['category_name']] = [];
            }

            $data[$product['category_name']][] = $product;
        }

        return response()->json([
            'success' => true,
            'message' => 'Products grouped by tenants',
            'data' => $data
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

            $validated = $request->validate([
                'name' => 'sometimes|string',
                'price' => 'sometimes|numeric',
                'tenant_id'    => 'required|exists:tenants,id',
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
