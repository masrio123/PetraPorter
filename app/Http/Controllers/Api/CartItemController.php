<?php

namespace App\Http\Controllers\Api;

use App\Models\CartItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CartItemController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cart_id' => 'required|exists:carts,id',
            'tenant_id'=> 'required|exist:tenant_id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric',
        ]);

        $total = $validated['quantity'] * $validated['price'];

        $item = CartItem::create([
            'cart_id' => $validated['cart_id'],
            'tenant_id'=> $validated['tenant_id'],
            'product_id' => $validated['product_id'],
            'quantity' => $validated['quantity'],
            'price' => $validated['price'],
            'total_price' => $total,
        ]);

        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        $item = CartItem::findOrFail($id);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $item->quantity = $validated['quantity'];
        $item->total_price = $item->quantity * $item->price;
        $item->save();

        return response()->json($item);
    }

    public function destroy($id)
    {
        $item = CartItem::findOrFail($id);
        $item->delete();

        return response()->json(['message' => 'Item removed']);
    }
}
