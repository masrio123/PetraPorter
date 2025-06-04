<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Porter;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\OrderHistory;
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
        $order->order_status_id = 7;
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
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        // Cegah cancel jika order sudah diterima porter (status ID 1)
        if ($order->order_status_id == 1) {
            return response()->json([
                'success' => false,
                'message' => 'Order sudah diterima porter dan tidak bisa dibatalkan.',
            ], 400);
        }

        $canceledStatus = OrderStatus::where('order_status', 'canceled')->first();
        if (!$canceledStatus) {
            return response()->json(['success' => false, 'message' => 'Canceled status not found'], 500);
        }

        $history = OrderHistory::create([
            'customer_id' => $order->customer_id,
            'customer_name' => $order->customer->customer_name ?? 'Unknown Customer',
            'tenant_location_name' => $order->tenantLocation->location_name ?? 'Unknown Location',
            'order_status_id' => $canceledStatus->id,
            'total_price' => $order->total_price,
            'shipping_cost' => $order->shipping_cost,
            'grand_total' => $order->grand_total,
        ]);

        foreach ($order->items as $item) {
            $history->items()->create([
                'product_id' => $item->product_id,
                'product_name' => $item->product->name ?? 'Unknown Product',
                'tenant_name' => $item->product->tenant->name ?? 'Unknown Tenant',
                'quantity' => $item->quantity,
                'price' => $item->price,
                'total_price' => $item->subtotal,
            ]);
        }

        // Hapus order asli
        $order->items()->delete();
        $order->delete();

        return response()->json(['success' => true, 'message' => 'Order canceled and moved to history']);
    }
}
