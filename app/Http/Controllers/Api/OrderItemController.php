<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Porter;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\PorterRating;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\DeliveryPoint;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class OrderItemController extends Controller
{

    public function searchPorter($orderId)
    {
        try {
            // PERBAIKAN 1: Hapus 'porter.bankUser' dari eager loading
            $order = Order::with([
                'customer.department',
                'status',
                'porter.department',
                'tenantLocation',
                'items.tenant'
            ])->find($orderId);

            if (!$order) {
                return response()->json(['success' => false, 'message' => 'Order tidak ditemukan.'], 404);
            }

            $orderStatusId = $order->order_status_id;
            $systemMessage = "Order sudah memiliki porter.";

            if ($orderStatusId == 5) {
                if (!$order->porter_id) {
                    // PERBAIKAN 2: Hapus 'bankUser' dari eager loading saat mencari porter
                    $porter = Porter::with('department')
                        ->where('porter_isOnline', true)
                        ->where(fn($q) => $q->whereNull('timeout_until')->orWhere('timeout_until', '<=', now()))
                        ->inRandomOrder()
                        ->first();

                    if (!$porter) {
                        // Kembalikan status 200 OK, tapi dengan pesan bahwa porter tidak ditemukan.
                        // 'success' => false menandakan operasi tidak berhasil, tapi request itu sendiri valid.
                        return response()->json([
                            'success' => false,
                            'message' => 'Tidak ada porter online saat ini. Mencari lagi...',
                            'status' => [], // Kirim array status kosong
                            'data' => null   // Tidak ada data porter untuk dikirim
                        ], 200); // Penting: Gunakan status 200 OK
                    }

                    $order->porter_id = $porter->id;
                    $order->save();
                    $systemMessage = "Mencari porter yang tersedia...";
                }
            }

            // PERBAIKAN 3: Hapus 'porter.bankUser' dari re-load
            $order->load(['porter.department']);

            $statusLabelMap = [
                1 => 'Pesanan diterima',
                2 => 'Sedang dalam perjalanan',
                3 => 'Telah sampai ke customer',
            ];

            $statusArray = collect($statusLabelMap)->map(function ($label, $id) use ($orderStatusId) {
                return ['label' => $label, 'key' => $orderStatusId === $id];
            })->values()->all();

            if ($order->porter_id) {
                // Pastikan relasi porter dimuat ulang setelah di-assign
                $order->refresh();

                $groupedItems = $order->items->groupBy('tenant_id')->map(function ($items) {
                    return [
                        'tenant_name' => optional($items->first()->tenant)->name ?? 'Tenant Dihapus',
                        'products' => $items->map(function ($item) {
                            return [
                                'product_name' => $item->product_name,
                                'quantity' => $item->quantity,
                                'price' => number_format($item->price, 2, '.', ''),
                                'subtotal' => number_format($item->subtotal, 2, '.', ''),
                                'notes' => $item->notes,
                            ];
                        })->values()
                    ];
                })->values();

                return response()->json([
                    'success' => true,
                    'message' => $systemMessage,
                    'status' => $statusArray,
                    'data' => [
                        'order_id' => $order->id,
                        'order_status' => optional($order->status)->order_status,
                        'tenant_location_name' => optional($order->tenantLocation)->location_name ?? '-',
                        'total_price' => number_format($order->total_price, 2, '.', ''),
                        'shipping_cost' => number_format($order->shipping_cost, 2, '.', ''),
                        'grand_total' => number_format($order->grand_total, 2, '.', ''),
                        'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                        'porter' => [
                            'porter_id' => $order->porter->id,
                            'name' => $order->porter->porter_name,
                            'nrp' => $order->porter->porter_nrp,
                            'department' => optional($order->porter->department)->department_name ?? '-',
                            // PERBAIKAN 4: Menggunakan 3 kolom baru dari model Porter
                            'bank_name' => $order->porter->bank_name,
                            'account_numbers' => $order->porter->account_numbers,
                            'username' => $order->porter->username,
                        ],
                        'items' => $groupedItems
                    ]
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Porter belum ditetapkan.', 'status' => $statusArray], 400);
        } catch (\Exception $e) {
            Log::error("Search Porter Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan internal.'], 500);
        }
    }


    public function cancelOrder($orderId)
    {
        $order = Order::find($orderId);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }
        if ($order->order_status_id != 5) {
            return response()->json(['success' => false, 'message' => 'Order hanya bisa dibatalkan jika status masih waiting.'], 400);
        }
        $canceledStatus = OrderStatus::where('order_status', 'canceled')->first();
        if (!$canceledStatus) {
            return response()->json(['success' => false, 'message' => 'Canceled status not found'], 500);
        }
        $order->order_status_id = $canceledStatus->id;
        $order->save();
        return response()->json(['success' => true, 'message' => 'Order berhasil dibatalkan.']);
    }

    public function ratePorter($orderId, Request $request)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000'
        ]);
        $order = Order::find($orderId);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order tidak ditemukan.'], 404);
        }
        if ($order->order_status_id != 3) {
            return response()->json(['success' => false, 'message' => 'Order belum diselesaikan, tidak bisa memberi rating.'], 400);
        }
        if (!$order->porter_id || !$order->porter) {
            return response()->json(['success' => false, 'message' => 'Order tidak memiliki porter.'], 400);
        }
        if (PorterRating::where('order_id', $orderId)->exists()) {
            return response()->json(['success' => false, 'message' => 'Rating sudah diberikan untuk order ini.'], 400);
        }
        PorterRating::create([
            'porter_id' => $order->porter_id,
            'order_id' => $order->id,
            'rating' => $request->rating,
            'review' => $request->review // <-- Tambahkan ini!
        ]);
        $avgRating = PorterRating::where('porter_id', $order->porter_id)->avg('rating');
        $porter = $order->porter;
        $porter->porter_rating = round($avgRating, 2);
        $porter->save();
        return response()->json([
            'success' => true,
            'message' => 'Rating berhasil diberikan',
            'new_average_rating' => $porter->porter_rating
        ]);
    }

    public function getTenantOrderNotifications($tenantId)
    {
        $orders = Order::whereHas('items', function ($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId);
        })
            ->with(['status', 'customer', 'items', 'tenantLocation', 'porter'])
            ->whereIn('order_status_id', [1, 2, 5])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($orders->isEmpty()) {
            return response()->json(['success' => true, 'message' => 'Tidak ada order aktif untuk tenant ini.', 'data' => []]);
        }

        $result = $orders->map(function ($order) use ($tenantId) {
            $items = $order->items
                ->filter(fn($item) => $item->tenant_id == $tenantId)
                ->map(fn($item) => [
                    'product_name' => $item->product_name, // --- PERUBAHAN ---
                    'quantity' => $item->quantity,
                    'price' => number_format($item->price, 2, '.', ''),
                    'total_price' => number_format($item->subtotal, 2, '.', ''),
                    'notes' => $item->notes,
                ])
                ->values();

            $cart = Cart::find($order->cart_id);
            $delivery_point_name = optional(optional($cart)->deliveryPoint)->delivery_point_name;

            return [
                'order_id' => $order->id,
                'customer_name' => optional($order->customer)->customer_name ?? '-',
                'order_status' => optional($order->status)->order_status,
                'tenant_location_name' => $delivery_point_name ?? '-',
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'items' => $items,
                'porter_name' => optional($order->porter)->porter_name ?? "Belum Ada Porter"
            ];
        });

        return response()->json(['success' => true, 'message' => 'Daftar order aktif ditemukan.', 'data' => $result]);
    }
}
