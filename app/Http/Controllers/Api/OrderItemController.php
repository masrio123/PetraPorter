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
            'porter.department',
            'porter.bankUser',
            'tenantLocation',
            'items.product.tenant'
        ])->find($orderId);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }

        // Tangani berdasarkan status
        switch ($order->order_status_id) {
            case 1:
            case 2:
                break; // lanjut tampilkan detail kalau status 1/2 dan porter sudah ada

            case 3:
                return response()->json([
                    'success' => false,
                    'message' => 'Order ini sudah selesai.',
                ], 400);

            case 4:
                return response()->json([
                    'success' => false,
                    'message' => 'Order ini sudah dibatalkan.',
                ], 400);

            case 5:
                // lanjut ke proses cari porter jika belum ada
                break;

            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Status order tidak dikenali.',
                ], 400);
        }

        // Kalau sudah ada porter, tampilkan detail
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
                    'subtotal' => number_format($item->price * $item->quantity, 2, '.', '')
                ];
            }

            return response()->json([
                'success' => false,
                'message' => 'Order ini sudah memiliki porter yang menangani.',
                'data' => [
                    'order_id' => $order->id,
                    'order_status' => $order->status->order_status,
                    'tenant_location_name' => $order->tenantLocation->location_name ?? '-',
                    'total_price' => number_format($order->total_price, 2, '.', ''),
                    'shipping_cost' => number_format($order->shipping_cost, 2, '.', ''),
                    'grand_total' => number_format($order->grand_total, 2, '.', ''),
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'porter' => [
                        'porter_id' => $order->porter->id,
                        'name' => $order->porter->porter_name,
                        'nrp' => $order->porter->porter_nrp,
                        'department' => $order->porter->department->department_name ?? '-',
                        'account_number' => $order->porter->bankUser->account_number ?? '-',
                    ],
                    'items' => array_values($groupedItems)
                ]
            ]);
        }

        // Kalau belum ada porter, cari porter online
        $porter = Porter::with(['department', 'bankUser'])
            ->where('porter_isOnline', true)
            ->inRandomOrder()
            ->first();

        if (!$porter) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada porter online saat ini.',
            ], 404);
        }

        // Tetapkan porter dan update order
        $order->porter_id = $porter->id;
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

        // Hanya izinkan cancel jika status "waiting" (id = 5)
        if ($order->order_status_id != 5) {
            return response()->json([
                'success' => false,
                'message' => 'Order hanya bisa dibatalkan jika status masih waiting.'
            ], 400);
        }

        // Ambil status canceled
        $canceledStatus = OrderStatus::where('order_status', 'canceled')->first();
        if (!$canceledStatus) {
            return response()->json([
                'success' => false,
                'message' => 'Canceled status not found'
            ], 500);
        }

        // Update status ke canceled
        $order->order_status_id = $canceledStatus->id;
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Order berhasil dibatalkan.',
            'order_id' => $order->id,
            'new_status' => $canceledStatus->order_status
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

        return response()->json([
            'success' => true,
            'message' => 'Rating berhasil diberikan',
            'porter_id' => $porter->id,
            'new_average_rating' => $porter->porter_rating,
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
