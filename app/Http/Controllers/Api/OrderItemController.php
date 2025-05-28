<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Porter;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Http\Controllers\Controller;

class OrderItemController extends Controller
{
    // Tampilkan semua order item
    public function index()
    {
        $items = OrderItem::with(['order', 'product', 'tenant'])->get();

        return response()->json([
            'success' => true,
            'message' => 'List of all order items',
            'data' => $items,
        ]);
    }

    // Tampilkan semua item dari satu order
    public function getByOrder($orderId)
    {
        $items = OrderItem::with(['product', 'tenant'])
            ->where('order_id', $orderId)
            ->get();

        if ($items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No items found for this order.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Items for order ID ' . $orderId,
            'data' => $items,
        ]);
    }

    // Cari porter untuk order tertentu
    public function searchPorter($orderId)
    {
        $order = Order::with(['customer.department', 'cart'])->find($orderId);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        // Cari porter yang sedang online (aktif)
        $porter = Porter::with(['department', 'bankUser'])
            ->where('porter_isOnline', true)
            ->inRandomOrder()
            ->first();

        if (!$porter) {
            return response()->json([
                'message' => 'Awaiting for porter...',
            ], 200);
        }

        // Simulasi update status order ke "received"
        $order->order_status_id = OrderStatus::where('status_name', 'received')->first()?->id ?? 1;
        $order->save();

        // Hapus order setelah diproses
        $order->delete();

        return response()->json([
            'message' => 'Porter Found!',
            'porter' => [
                'name' => $porter->porter_name,
                'nrp' => $porter->porter_nrp,
                'jurusan' => $porter->department->department_name ?? 'Unknown',
            ],
            'total_payment' => [
                'total_price' => $order->total_price,
                'shipping_fee' => $order->shipping_cost,
                'grand_total' => $order->grand_total,
            ],
            'bank_info' => [
                'account_number' => $porter->bankUser->account_number ?? '-',
                'username' => $porter->bankUser->username ?? '-',
            ],
            'order_status' => 'received'
        ]);
    }

    public function cancelOrder($orderId)
    {
        try {
            $order = Order::find($orderId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found or already processed.',
                ], 404);
            }

            // Optional: Ubah status jadi canceled jika ada di table order_statuses
            $canceledStatus = OrderStatus::where('order_status', 'canceled')->first();
            if ($canceledStatus) {
                $order->order_status_id = $canceledStatus->id;
                $order->save();
            }

            // Hapus order
            $order->delete();

            return response()->json([
                'success' => true,
                'message' => 'Order has been successfully canceled.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while canceling the order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
