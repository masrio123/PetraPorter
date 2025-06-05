<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Porter;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\OrderHistory;
use App\Models\PorterRating;
use Illuminate\Http\Request;
use App\Models\OrderHistoryItem;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class OrderItemController extends Controller
{

    // Cari porter untuk order tertentu
    public function searchPorter($orderId)
    {
        $order = Order::with(['customer.department'])->find($orderId);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        // Cek apakah order sudah punya porter assigned
        if ($order->porter_id) {
            return response()->json([
                'success' => false,
                'message' => 'Order ini sudah memiliki porter yang ditugaskan.',
            ], 400);
        }

        // Cari porter online secara acak
        $porter = Porter::with('department')
            ->where('porter_isOnline', true)
            ->inRandomOrder()
            ->first();

        if (!$porter) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada porter online saat ini.',
            ], 404);
        }

        // Simpan penunjukan porter & ubah status order ke ID 7 (waiting_for_acceptance)
        $order->porter_id = $porter->id;
        $order->order_status_id = 5;
        $order->save();

        // Pesan sistem
        $systemMessage = "Sistem menunjuk porter bernama {$porter->porter_name}, {$porter->porter_nrp} dari jurusan {$porter->department->department_name}";

        return response()->json([
            'success' => true,
            'message' => $systemMessage,
        ]);
    }


    public function cancelOrder($orderId)
    {
        $order = Order::with(['items.product.tenant', 'customer', 'tenantLocation'])->find($orderId);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        // Cegah cancel jika order sudah diterima porter (status ID 1)
        if ($order->order_status_id == 1) {
            return response()->json([
                'success' => false,
                'message' => 'Order sudah diterima porter dan tidak bisa dibatalkan.'
            ], 400);
        }

        $canceledStatus = OrderStatus::where('order_status', 'canceled')->first();
        if (!$canceledStatus) {
            return response()->json([
                'success' => false,
                'message' => 'Canceled status not found'
            ], 500);
        }

        // Simpan ke order_histories
        $history = OrderHistory::create([
            'customer_id' => $order->customer_id,
            'customer_name' => optional($order->customer)->customer_name ?? 'Unknown Customer',
            'tenant_location_name' => optional($order->tenantLocation)->location_name ?? 'Unknown Location',
            'order_status_id' => $canceledStatus->id,
            'porter_id' => $order->porter_id ?? null,
            'total_price' => $order->total_price,
            'shipping_cost' => $order->shipping_cost,
            'grand_total' => $order->grand_total,
        ]);

        // Simpan item-item
        foreach ($order->items as $item) {
            $history->items()->create([
                'product_id' => $item->product_id,
                'product_name' => optional($item->product)->name ?? 'Unknown Product',
                'tenant_name' => optional(optional($item->product)->tenant)->name ?? 'Unknown Tenant',
                'quantity' => $item->quantity,
                'price' => $item->price,
                'total_price' => $item->price * $item->quantity,
            ]);
        }

        // Hapus order asli
        $order->items()->delete();
        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order canceled and moved to history'
        ]);
    }

    public function ratePorter($orderId, Request $request)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $order = Order::with(['porter', 'customer', 'items.product.tenant', 'tenantLocation'])->find($orderId);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }

        if ($order->order_status_id != 3) {
            return response()->json([
                'success' => false,
                'message' => 'Order belum diselesaikan, tidak bisa memberi rating.',
            ], 400);
        }

        if (!$order->porter_id || !$order->porter) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak memiliki porter.',
            ], 400);
        }

        if (PorterRating::where('order_id', $orderId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Rating sudah diberikan untuk order ini.',
            ], 400);
        }

        // Simpan rating
        PorterRating::create([
            'porter_id' => $order->porter_id,
            'order_id' => $order->id,
            'rating' => $request->rating,
        ]);

        // Hitung dan update rata-rata rating porter
        $avgRating = PorterRating::where('porter_id', $order->porter_id)->avg('rating');

        $porter = $order->porter;
        $porter->porter_rating = round($avgRating, 2);
        $porter->save();

        // Masukkan ke order_histories
        $orderHistory = new OrderHistory();
        $orderHistory->order_status_id = 3; // finished
        $orderHistory->customer_id = $order->customer_id;
        $orderHistory->customer_name = optional($order->customer)->customer_name ?? 'Unknown Customer';
        $orderHistory->porter_id = $order->porter_id;
        $orderHistory->tenant_location_name = optional($order->tenantLocation)->location_name ?? 'Unknown Location';
        $orderHistory->shipping_cost = $order->shipping_cost;
        $orderHistory->total_price = $order->total_price;
        $orderHistory->grand_total = $order->grand_total;
        $orderHistory->save();

        // Salin item ke order_history_items
        foreach ($order->items as $item) {
            $orderHistory->items()->create([
                'product_id' => $item->product_id,
                'product_name' => optional($item->product)->name ?? 'Unknown Product',
                'tenant_name' => optional(optional($item->product)->tenant)->name ?? 'Unknown Tenant',
                'quantity' => $item->quantity,
                'price' => $item->price,
                'total_price' => $item->price * $item->quantity,
            ]);
        }

        // Hapus order & items
        $order->items()->delete(); // jika tidak cascade
        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rating berhasil diberikan. Order sudah dipindahkan ke riwayat.',
            'porter_id' => $porter->id,
            'new_average_rating' => $porter->porter_rating,
            'history_id' => $orderHistory->id,
        ]);
    }
}
