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
                // Sesuaikan kolom yang diminta di tenantLocation, misal 'location_name' bukan 'name'
                $allowedTenants = Tenant::with('tenantLocation:id,location_name') // pakai kolom sebenarnya
                    ->where('tenant_location_id', $cart->tenant_location_id)
                    ->get(['id', 'name', 'tenant_location_id']) // 'name' ini nama tenant, bukan location
                    ->map(function ($t) {
                        return [
                            'id' => $t->id,
                            'name' => $t->name, // nama tenant
                            'location' => $t->tenantLocation->location_name ?? '(tidak diketahui)', // nama lokasi gedung
                        ];
                    });

                return response()->json([
                    'message' => 'Jangan beda gedung! Kasian Porternya!',
                    'allowed_tenants_in_same_building' => $allowedTenants,
                ], 422);
            }

            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->where('tenant_id', $tenant->id)
                ->first();

            if ($cartItem) {
                $cartItem->quantity += $request->quantity;
                $cartItem->save();
            } else {
                $cartItem = CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'tenant_id' => $tenant->id,
                    'quantity' => $request->quantity,
                ]);
            }

            return response()->json([
                'message' => 'Item added to cart',
                'item' => [
                    'id' => $cartItem->id,
                    'tenant_id' => $cartItem->tenant_id,
                    'cart_id' => $cartItem->cart_id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Data tidak ditemukan.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menambahkan item ke cart.',
                'error' => $e->getMessage(),
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
