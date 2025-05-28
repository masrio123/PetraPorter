<?php

namespace App\Http\Controllers\Api;

use App\Models\Tenant;
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

    public function fetchCategoriesWithMenusByTenant($tenantId): JsonResponse
    {
        $tenant = Tenant::select('id', 'name')->find($tenantId);

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found',
                'data' => null
            ], 404);
        }

        $categories = Category::with(['products' => function ($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId)->select('id', 'name', 'price', 'category_id');
        }])
            ->whereHas('products', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->select('id', 'category_name as name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'List of categories with menus for tenant',
            'tenant' => $tenant,
            'data' => $categories
        ]);
    }
}
