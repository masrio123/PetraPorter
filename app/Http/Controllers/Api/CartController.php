<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
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


            $cart = Cart::create([
                'customer_id' => $request->customer_id,
                'tenant_location_id' => $request->tenant_location_id,
                // order_status_id dibiarkan null
            ]);

            return response()->json([
                'message' => 'Cart created successfully.',
                'cart' => $cart->only(['id', 'customer_id', 'tenant_location_id']),
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

            $totalPrice = $cart->items->sum(fn($item) => $item->quantity * $item->product->price);
            $totalQuantity = $cart->items->sum('quantity');

            $shippingCost = match (true) {
                $totalQuantity <= 2 => 2000,
                $totalQuantity <= 4 => 5000,
                $totalQuantity <= 10 => 10000,
                default => 10000 + (ceil($totalQuantity - 10) * 10000),
            };

            return response()->json([
                'cart_id' => $cart->id,
                'customer_id' => $cart->customer_id ?? null,
                'tenant_location' => $cart->tenantLocation->location_name ?? null,
                'cart_items' => $cartItemsDisplay,
                'total_payment' => [
                    'total_price' => $totalPrice,
                    'shipping_cost' => $shippingCost,
                    'grand_total' => $totalPrice + $shippingCost,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Cart tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat mengambil data cart.', 'error' => $e->getMessage()], 500);
        }
    }

        public function checkoutCart($id)
        {
            try {
                $cart = Cart::with(['items.product', 'tenantLocation'])->findOrFail($id);

                if ($cart->items->isEmpty()) {
                    return response()->json(['message' => 'Cart is empty, cannot checkout.'], 400);
                }

                $totalPrice = $cart->items->sum(fn($item) => $item->quantity * $item->product->price);
                $totalQuantity = $cart->items->sum('quantity');

                $shippingCost = match (true) {
                    $totalQuantity <= 2 => 2000,
                    $totalQuantity <= 4 => 5000,
                    $totalQuantity <= 10 => 10000,
                    default => 10000 + (ceil($totalQuantity - 10) * 10000),
                };

                $grandTotal = $totalPrice + $shippingCost;

                $porter = Porter::with('department', 'bankUser')->inRandomOrder()->first();

                if (!$porter) {
                    return response()->json(['message' => 'No porter available.'], 500);
                }

                DB::transaction(function () use ($cart, $totalPrice, $shippingCost, $grandTotal) {
                    $order = Order::create([
                        'cart_id' => $cart->id,
                        'customer_id' => $cart->customer_id,
                        'tenant_location_id' => $cart->tenant_location_id,
                        'order_status_id' => 5, // pending
                        'total_price' => $totalPrice,
                        'shipping_cost' => $shippingCost,
                        'grand_total' => $grandTotal,
                    ]);

                    foreach ($cart->items as $item) {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $item->product_id,
                            'tenant_id' => $item->tenant_id,
                            'quantity' => $item->quantity,
                            'price' => $item->product->price,
                            'subtotal' => $item->quantity * $item->product->price,
                        ]);
                    }

                    $cart->update(['order_status_id' => 1]); // Set status juga di cart
                    $cart->items()->delete(); // Kosongkan cart
                });

                return response()->json(['message' => 'Checkout berhasil, order telah dibuat.']);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return response()->json(['message' => 'Cart tidak ditemukan.'], 404);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Checkout gagal.', 'error' => $e->getMessage()], 500);
            }
        }

    public function getAllCarts()
    {
        try {
            $carts = Cart::with(['items.product', 'items.tenant', 'tenantLocation', 'customer'])->get();

            if ($carts->isEmpty()) {
                return response()->json(['message' => 'Tidak ada cart ditemukan.', 'data' => []]);
            }

            $result = [];

            foreach ($carts as $cart) {
                $itemsGrouped = $cart->items->groupBy('tenant_id');
                $cartItemsDisplay = [];

                foreach ($itemsGrouped as $tenantId => $items) {
                    $tenantName = $items->first()->tenant->name ?? '-';
                    $listItems = [];

                    foreach ($items as $item) {
                        $listItems[] = [
                            'product_id' => $item->product_id,
                            'product_name' => $item->product->name ?? '-',
                            'tenant_id' => $item->tenant_id,
                            'quantity' => $item->quantity,
                            'price' => $item->product->price ?? 0,
                            'subtotal' => $item->quantity * ($item->product->price ?? 0),
                        ];
                    }

                    $cartItemsDisplay[] = [
                        'tenant_id' => $tenantId,
                        'tenant_name' => $tenantName,
                        'items' => $listItems,
                    ];
                }

                $totalPrice = $cart->items->sum(fn($item) => $item->quantity * ($item->product->price ?? 0));
                $totalQuantity = $cart->items->sum('quantity');

                $shippingCost = match (true) {
                    $totalQuantity <= 2 => 2000,
                    $totalQuantity <= 4 => 5000,
                    $totalQuantity <= 10 => 10000,
                    default => 10000 + (ceil($totalQuantity - 10) * 10000),
                };

                $result[] = [
                    'cart_id' => $cart->id,
                    'customer_id' => $cart->customer_id,
                    'customer_name' => $cart->customer->customer_name ?? '-',
                    'tenant_location' => $cart->tenantLocation->location_name ?? '-',
                    'cart_items' => $cartItemsDisplay,
                    'total_payment' => [
                        'total_price' => $totalPrice,
                        'shipping_cost' => $totalQuantity > 0 ? $shippingCost : 0,
                        'grand_total' => $totalQuantity > 0 ? $totalPrice + $shippingCost : 0,
                    ],
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'List of all carts',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data cart.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteCart($id)
    {
        try {
            $cart = Cart::with('items')->findOrFail($id);

            // Hapus semua item dalam cart terlebih dahulu
            $cart->items()->delete();

            // Hapus cart-nya
            $cart->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cart dan item-item di dalamnya berhasil dihapus.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cart tidak ditemukan.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus cart.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
