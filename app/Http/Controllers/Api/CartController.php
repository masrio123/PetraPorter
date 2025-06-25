<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Porter;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log; // --- PERUBAHAN --- Tambahkan ini

class CartController extends Controller
{
    public function createCart(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'tenant_location_id' => 'required|exists:tenant_locations,id',
                'delivery_id' => 'required|exists:delivery_points,id'
            ]);

            $cart = Cart::create([
                'customer_id' => $validatedData['customer_id'],
                'tenant_location_id' => $validatedData['tenant_location_id'],
                'delivery_point_id' => $validatedData['delivery_id']
            ]);

            return response()->json([
                'message' => 'Cart created successfully.',
                'cart' => $cart->only(['id', 'customer_id', 'tenant_location_id']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal membuat cart.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Menampilkan isi dari satu keranjang belanja.
     */
    public function showCart($id)
    {
        try {
            $cart = Cart::with(['items.tenant', 'tenantLocation'])->findOrFail($id);

            if ($cart->items->isEmpty()) {
                return response()->json(['message' => 'Tidak ada item dalam cart ini.'], 404);
            }

            $itemsGrouped = $cart->items->groupBy('tenant_id');
            $cartItemsDisplay = [];

            foreach ($itemsGrouped as $tenantId => $items) {
                $tenantName = optional($items->first()->tenant)->name ?? 'Unknown Tenant';
                $listItems = [];

                foreach ($items as $item) {
                    $listItems[] = [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product_name,
                        'tenant_id' => $item->tenant_id,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'subtotal' => $item->quantity * $item->price,
                    ];
                }

                $cartItemsDisplay[] = [
                    'tenant_id' => $tenantId,
                    'tenant_name' => $tenantName,
                    'items' => $listItems,
                ];
            }

            $totalPrice = $cart->items->sum(fn($item) => $item->quantity * $item->price);
            $totalQuantity = $cart->items->sum('quantity');

            $shippingCost = match (true) {
                $totalQuantity <= 2 => 2000,
                $totalQuantity <= 4 => 5000,
                $totalQuantity <= 10 => 10000,
                default => 10000 + (ceil($totalQuantity - 10) * 10000),
            };

            return response()->json([
                'cart_id' => $cart->id,
                'customer_id' => $cart->customer_id,
                'tenant_location' => optional($cart->tenantLocation)->location_name,
                'cart_items' => $cartItemsDisplay,
                'total_payment' => [
                    'total_price' => $totalPrice,
                    'shipping_cost' => $shippingCost,
                    'grand_total' => $totalPrice + $shippingCost,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengambil data cart.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Memproses checkout: memindahkan data dari cart_items ke order_items.
     */
    public function checkoutCart(Request $request, $id)
    {
        $validatedData = $request->validate([
            'notes' => 'nullable|array',
            'notes.*.product_id' => 'required_with:notes|integer',
            'notes.*.note' => 'nullable|string',
        ]);

        try {
            $order = DB::transaction(function () use ($id, $validatedData) {
                $cart = Cart::with('items')->findOrFail($id);

                if ($cart->items->isEmpty()) {
                    throw new \Exception('Cart is empty.');
                }

                $totalPrice = 0;
                $totalQuantity = $cart->items->sum('quantity');

                $shippingCost = match (true) {
                    $totalQuantity <= 2 => 2000,
                    $totalQuantity <= 4 => 5000,
                    $totalQuantity <= 10 => 10000,
                    default => 10000 + (ceil($totalQuantity - 10) * 10000),
                };
                $createdOrder = Order::create([
                    'cart_id' => $cart->id,
                    'customer_id' => $cart->customer_id,
                    'delivery_point_id' => $cart->delivery_point_id,                    
                    'tenant_location_id' => $cart->tenant_location_id,
                    'order_status_id' => 5,
                    'total_price' => 0,
                    'shipping_cost' => $shippingCost,
                    'grand_total' => 0,
                ]);

                $notesMap = collect($validatedData['notes'] ?? [])->pluck('note', 'product_id');

                $finalTotalPrice = 0;

                foreach ($cart->items as $item) {
                    $productName = $item->product_name;
                    $productPrice = $item->price;

                    if (empty($productName) || is_null($productPrice)) {
                        $freshProduct = Product::find($item->product_id);
                        if ($freshProduct) {
                            $productName = $freshProduct->name;
                            $productPrice = $freshProduct->price;
                        } else {
                            $productName = 'Produk Dihapus';
                            $productPrice = 0;
                        }
                    }

                    $subtotal = $item->quantity * $productPrice;
                    $finalTotalPrice += $subtotal;

                    OrderItem::create([
                        'order_id' => $createdOrder->id,
                        'product_id' => $item->product_id,
                        'tenant_id' => $item->tenant_id,
                        'quantity' => $item->quantity,
                        'product_name' => $productName,
                        'price' => $productPrice,
                        'subtotal' => $subtotal,
                        'notes' => $notesMap[$item->product_id] ?? null,
                    ]);
                }

                $createdOrder->total_price = $finalTotalPrice;
                $createdOrder->grand_total = $finalTotalPrice + $shippingCost;
                $createdOrder->save();

                $cart->items()->delete(); // Hapus item-itemnya saja
                // $cart->delete();      // <-- BARIS INI DIHAPUS/DIKOMENTARI

                return $createdOrder;
            });

            return response()->json(['message' => 'Checkout berhasil, order telah dibuat.', 'order' => $order]);
        } catch (\Exception $e) {
            Log::error('Checkout Failed:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Checkout Gagal: ' . $e->getMessage()], 500);
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
                            'product_name' => $item->product_name ?? '-',
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

            $cart->items()->delete();
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
