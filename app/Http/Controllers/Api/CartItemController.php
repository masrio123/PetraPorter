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
    // File: app/Http/Controllers/Api/CartItemController.php

public function addItems(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'cart_id' => 'required|exists:carts,id',
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $cart = Cart::findOrFail($validatedData['cart_id']);
            // Ambil data produk dari katalog berdasarkan product_id
            $product = Product::findOrFail($validatedData['product_id']);
            
            // Cek lokasi tenant jika diperlukan
            if ($product->tenant->tenant_location_id !== $cart->tenant_location_id) {
                return response()->json(['message' => 'Tenant produk tidak berada di lokasi yang sama dengan keranjang Anda.'], 422);
            }

            $existingItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->first();

            if ($existingItem) {
                // Jika item sudah ada, cukup update kuantitasnya
                $existingItem->quantity += $validatedData['quantity'];
                $existingItem->save();
            } else {
                // Jika item baru, buat entri baru di cart_items
                // DAN SALIN NAMA & HARGA saat itu juga.
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'tenant_id' => $product->tenant_id,
                    'quantity' => $validatedData['quantity'],
                    'product_name' => $product->name, // <-- DATA DISALIN DI SINI
                    'price' => $product->price,       // <-- DATA DISALIN DI SINI
                ]);
            }

            return response()->json(['message' => 'Item berhasil ditambahkan ke cart.'], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat menambah item.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Menghapus atau mengurangi kuantitas item dari keranjang.
     */
    public function deleteByTenantAndProduct($tenantId, $productId)
    {
        try {
            $item = CartItem::where('tenant_id', $tenantId)
                ->where('product_id', $productId)
                ->firstOrFail();

            if ($item->quantity > 1) {
                $item->quantity -= 1;
                $item->save();
                return response()->json(['message' => 'Kuantitas item dikurangi 1.', 'item' => $item]);
            } else {
                $item->delete();
                return response()->json(['message' => 'Item dihapus dari keranjang karena kuantitas sisa 1.']);
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Item tidak ditemukan di keranjang.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus item.', 'error' => $e->getMessage()], 500);
        }
    }
}
