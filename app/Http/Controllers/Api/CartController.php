<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CartController extends Controller
{
    public function getOrCreateCart(Request $request)
    {
        $customerId = $request->user()->id; // atau dari token/login

        $cart = Cart::firstOrCreate(    
            ['customer_id' => $customerId, 'status' => 'active']
        );

        return response()->json($cart);
    }

    public function checkout($cartId)
    {
        $cart = Cart::findOrFail($cartId);
        $cart->status = 'checked_out';
        $cart->save();

        return response()->json(['message' => 'Cart checked out successfully']);
    }
}
