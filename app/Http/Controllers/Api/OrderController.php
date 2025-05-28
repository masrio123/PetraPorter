<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Tampilkan semua order
    public function index()
    {
        $orders = Order::with(['items.product', 'status', 'customer', 'tenantLocation'])->get();

        return response()->json([
            'success' => true,
            'message' => 'List of all orders',
            'data' => $orders,
        ]);
    }

    // Tampilkan detail order berdasarkan ID
    public function show($id)
    {
        $order = Order::with(['items.product', 'status', 'customer', 'tenantLocation'])->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order detail',
            'data' => $order,
        ]);
    }

    // Hapus (clear) order berdasarkan ID
    public function destroy($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        // Bisa ditambah validasi, misal hanya bisa hapus order yang belum selesai

        $order->items()->delete();
        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order and its items deleted successfully',
        ]);
    }
}
