<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\Porter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class CartController extends Controller
{
    public function createCart(Request $request)
    {
        try {
            $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'tenant_location_id' => 'required|exists:tenant_locations,id',
            ]);

            $existingCart = Cart::where('customer_id', $request->customer_id)
                ->where('tenant_location_id', $request->tenant_location_id)
                ->first();

            if ($existingCart) {
                return response()->json([
                    'message' => 'Cart already exists for this user in this location.',
                    'cart' => $existingCart
                ], 200);
            }

            $cart = Cart::create([
                'customer_id' => $request->customer_id,
                'tenant_location_id' => $request->tenant_location_id,
            ]);

            return response()->json([
                'message' => 'Cart created successfully.',
                'cart' => $cart
            ], 201);
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

    public function showCart($id)
    {
        try {
            $cart = Cart::with(['items.product', 'items.tenant', 'tenantLocation'])->findOrFail($id);

            if ($cart->items->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada item dalam cart ini.'
                ], 404);
            }

            // Group items by tenant_id (biar bisa ambil tenant_id juga)
            $itemsGrouped = $cart->items->groupBy('tenant_id');

            $cartItemsDisplay = [];

            foreach ($itemsGrouped as $tenantId => $items) {
                $tenantName = $items->first()->tenant->name;

                $listItems = [];

                foreach ($items as $item) {
                    $listItems[] = [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'tenant_id' => $item->tenant_id,
                        'quantity' => $item->quantity,
                        'price' => $item->product->price,
                        'subtotal' => $item->quantity * $item->product->price,
                    ];
                }

                $cartItemsDisplay[] = [
                    'tenant_id' => $tenantId,
                    'tenant_name' => $tenantName,
                    'items' => $listItems,
                ];
            }

            // Hitung total harga dan jumlah porsi
            $totalPrice = $cart->items->sum(function ($item) {
                return $item->quantity * $item->product->price;
            });

            $totalQuantity = $cart->items->sum('quantity');

            // Hitung ongkos kirim berdasarkan jumlah porsi
            if ($totalQuantity <= 2) {
                $shippingCost = 2000;
            } elseif ($totalQuantity <= 4) {
                $shippingCost = 5000;
            } elseif ($totalQuantity <= 10) {
                $shippingCost = 10000;
            } else {
                $extra = ceil($totalQuantity - 10);
                $shippingCost = 10000 + ($extra * 10000);
            }

            return response()->json([
                'cart_id' => $cart->id,
                'user_id' => $cart->user_id ?? null,
                'customer_id' => $cart->customer_id ?? null,
                'tenant_location' => $cart->tenantLocation->name ?? null,
                'cart_items' => $cartItemsDisplay,
                'total_payment' => [
                    'total_price' => $totalPrice,
                    'shipping_cost' => $shippingCost,
                    'grand_total' => $totalPrice + $shippingCost,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Cart tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data cart.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function deleteCart($id)
    {
        $cart = Cart::findOrFail($id);
        $cart->items()->delete();
        $cart->delete();

        return response()->json(['message' => 'Cart and all items deleted.']);
    }

    public function checkoutCart($id)
    {
        $cart = Cart::with(['items.product', 'tenantLocation'])->findOrFail($id);

        if ($cart->items->isEmpty()) {
            return response()->json([
                'message' => 'Cart is empty, cannot checkout.'
            ], 400);
        }

        $totalPrice = $cart->items->sum(fn($item) => $item->quantity * $item->product->price);
        $totalQuantity = $cart->items->sum('quantity');

        if ($totalQuantity <= 2) {
            $shippingCost = 2000;
        } elseif ($totalQuantity <= 4) {
            $shippingCost = 5000;
        } elseif ($totalQuantity <= 10) {
            $shippingCost = 10000;
        } else {
            $extra = ceil(($totalQuantity - 10));
            $shippingCost = 10000 + ($extra * 10000);
        }

        $grandTotal = $totalPrice + $shippingCost;

        // Pastikan relasi department dimuat
        $porter = Porter::with('department')->inRandomOrder()->first();

        if (!$porter) {
            return response()->json([
                'message' => 'No porter available.'
            ], 500);
        }

        // Kosongkan cart setelah checkout
        DB::transaction(fn() => $cart->items()->delete());

        return response()->json([
            'message' => 'Porter Found!',
            'porter' => [
                'name' => $porter->porter_name,
                'nrp' => $porter->porter_nrp,
                'department' => $porter->department->department_name ?? '-',
            ],
            'total_payment' => [
                'total_price' => $totalPrice,
                'shipping_cost' => $shippingCost,
                'grand_total' => $grandTotal,
            ],
            'bank_info' => [
                'account_number' => $porter->bankUser->account_number ?? '-',
                'username' => $porter->bankUser->username ?? '-',
            ],
        ]);
    }
}
