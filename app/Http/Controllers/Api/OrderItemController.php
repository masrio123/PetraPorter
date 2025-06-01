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
        $order = Order::with(['items.product.tenant.tenantLocation', 'customer', 'status'])->find($orderId);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $canceledStatus = OrderStatus::where('order_status', 'canceled')->first();
        if (!$canceledStatus) {
            return response()->json(['success' => false, 'message' => 'Canceled status not found'], 500);
        }

        // Simpan history order tanpa order_id
        $history = OrderHistory::create([
            'customer_id' => $order->customer_id,
            'customer_name' => $order->customer->customer_name,
            'tenant_location_name' => $order->tenantLocation->location_name ?? 'N/A',
            'order_status_id' => $canceledStatus->id,
            'total_price' => 0,
            'shipping_cost' => 0,
            'grand_total' => 0,
        ]);

        foreach ($order->items as $item) {
            $history->items()->create([
                'product_id' => $item->product_id,          // wajib diisi
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'price' => 0,
                'total_price' => 0,
                'shipping_cost' => 0,
                'grand_total' => 0,
            ]);
        }

        // Hapus order asli beserta itemnya
        $order->items()->delete();
        $order->delete();

        return response()->json(['success' => true, 'message' => 'Order canceled and moved to history']);
    }
}
