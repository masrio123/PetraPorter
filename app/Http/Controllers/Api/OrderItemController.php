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
        $order = Order::with([
            'customer.department',
            'status',
            'porter',
            'tenantLocation',
            'items.product.tenant'
        ])->find($orderId);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        // Jika sudah ada porter yang ditugaskan
        if ($order->porter_id) {
            $groupedItems = [];

            foreach ($order->items as $item) {
                $tenantName = $item->product->tenant->name;

                if (!isset($groupedItems[$tenantName])) {
                    $groupedItems[$tenantName] = [
                        'tenant_name' => $tenantName,
                        'products' => []
                    ];
                }

                $groupedItems[$tenantName]['products'][] = [
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => number_format($item->price, 2, '.', ''),
                    'total_price' => number_format($order->total_price, 2, '.', '')
                ];
            }

            return response()->json([
                'success' => false,
                'message' => 'Order ini sudah memiliki porter yang menangani.',
                'data' => [
                    'order_id' => $order->id,
                    'order_status' => $order->status->order_status,
                    'tenant_location_name' => $order->tenantLocation->location_name ?? '-',
                    'porter_id' => $order->porter_id,
                    'total_price' => number_format($order->total_price, 2, '.', ''),
                    'shipping_cost' => number_format($order->shipping_cost, 2, '.', ''),
                    'grand_total' => number_format($order->grand_total, 2, '.', ''),
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'items' => array_values($groupedItems)
                ]
            ]);
        }

        // Jika belum ada porter, cari secara acak
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

        // Tetapkan porter & ubah status order
        $order->porter_id = $porter->id;
        $order->order_status_id = 5; // waiting_for_acceptance
        $order->save();

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

    public function getTenantOrderNotifications($tenantId)
    {
        $orders = Order::whereHas('items.product', function ($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId);
        })
            ->with([
                'status',
                'customer',
                'items.product',
                'tenantLocation'
            ])
            ->whereNotIn('order_status_id', [6, 8]) // 6 = canceled, 8 = finished (anggap ini status selesai)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada order aktif untuk tenant ini.',
                'data' => []
            ]);
        }

        $result = $orders->map(function ($order) use ($tenantId) {
            $items = $order->items
                ->filter(fn($item) => $item->product->tenant_id == $tenantId)
                ->map(fn($item) => [
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => number_format($item->price, 2, '.', ''),
                    'total_price' => number_format($item->total_price, 2, '.', '')
                ])
                ->values();

            return [
                'order_id' => $order->id,
                'customer_name' => $order->customer->customer_name ?? '-',
                'order_status' => $order->status->order_status,
                'tenant_location_name' => $order->tenantLocation->location_name ?? '-',
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'items' => $items
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar order aktif ditemukan.',
            'data' => $result
        ]);
    }
}
