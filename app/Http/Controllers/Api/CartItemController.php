<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CartItemController extends Controller
{
    public function addItem(Request $request)
    {
        try {
            $request->validate([
                'cart_id' => 'required|exists:carts,id',
                'product_id' => 'required|exists:products,id',
                'tenant_id' => 'required|exists:tenants,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $cart = Cart::findOrFail($request->cart_id);

            // Ambil tenant yang dipilih
            $tenant = \App\Models\Tenant::findOrFail($request->tenant_id);

            // Cek apakah tenant location tenant sama dengan tenant_location_id di cart
            if ($tenant->tenant_location_id !== $cart->tenant_location_id) {
                // Ambil daftar tenant di gedung yang sama dengan cart
                $allowedTenants = \App\Models\Tenant::where('tenant_location_id', $cart->tenant_location_id)
                    ->get(['id', 'name']); // ambil id dan nama tenant saja

                return response()->json([
                    'message' => 'Jangan beda gedung! Kasian Porternya!',
                    'allowed_tenants_in_same_building' => $allowedTenants,
                ], 422);
            }

            $item = CartItem::where('cart_id', $request->cart_id)
                ->where('product_id', $request->product_id)
                ->first();

            if ($item) {
                $item->quantity += $request->quantity;
                $item->save();
            } else {
                $item = CartItem::create([
                    'cart_id' => $request->cart_id,
                    'product_id' => $request->product_id,
                    'tenant_id' => $request->tenant_id,
                    'quantity' => $request->quantity,
                ]);
            }

            return response()->json(['message' => 'Item added to cart', 'item' => $item]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong.',
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
