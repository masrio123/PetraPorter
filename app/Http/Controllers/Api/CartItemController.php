<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\Tenant;
use App\Models\Product;
use App\Models\CartItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CartItemController extends Controller
{
    public function addItems(Request $request)
    {
        try {
            $request->validate([
                'cart_id' => 'required|exists:carts,id',
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $cart = Cart::with('tenantLocation')->findOrFail($request->cart_id);
            
            $product = Product::findOrFail($request->product_id);
            $tenant = Tenant::where('id', $product->tenant_id)->first();
            
            // Cek apakah tenant berada di gedung yang sama
            if ($tenant->tenant_location_id !== $cart->tenant_location_id) {
                $allowedTenants = Tenant::where('tenant_location_id', $cart->tenant_location_id)
                    ->select('id', 'tenant_name')
                    ->get();

                return response()->json([
                    'message' => 'Tenant tidak berada di lokasi yang sama dengan cart.',
                    'allowed_tenants' => $allowedTenants
                ], 422);
            }

            // ✅ Tambahkan atau update item
            $existingItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->first();

            if ($existingItem) {
                $existingItem->quantity += $request->quantity;
                $existingItem->save();
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'tenant_id' => $tenant->id,
                    'quantity' => $request->quantity,
                ]);
            }

            return response()->json([
                'message' => 'Item berhasil ditambahkan ke cart.'
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function deleteByTenantAndProduct($tenantId, $productId)
    {
        $item = CartItem::where('tenant_id', $tenantId)
            ->where('product_id', $productId)
            ->first();

        if (!$item) {
            return response()->json([
                'message' => 'Item not found in cart.'
            ], 404);
        }

        if ($item->quantity > 1) {
            $item->quantity -= 1;
            $item->save();

            return response()->json([
                'message' => 'Item quantity decreased by 1.',
                'item' => $item
            ]);
        } else {
            $item->delete();

            return response()->json([
                'message' => 'Item removed from cart because quantity was 1.'
            ]);
        }
    }
}
