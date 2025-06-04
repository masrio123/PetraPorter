<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Porter;
use App\Models\BankUser;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PorterController extends Controller
{
    public function orderList($porterId)
    {
        $orders = Order::with([
            'customer.department',
            'tenantLocation',        // Asumsikan ini relasi tenant location seperti di fetchOrderById
            'status',                // Asumsikan relasi order status
            'items.product',         // 'items' adalah relasi OrderItem, dengan relasi product
            'items.tenant',          // tenant per item
        ])
            ->where('porter_id', $porterId)
            ->where('order_status_id', 7) // status: waiting_for_acceptance
            ->latest()
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Tidak ada tawaran order untuk porter ini.',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $orders->map(function ($order) {
                // group order items by tenant_id
                $groupedItems = $order->items->groupBy('tenant_id')->map(function ($items, $tenantId) {
                    return [
                        'tenant_id' => (int) $tenantId,
                        'tenant_name' => optional($items->first()->tenant)->name,
                        'items' => $items->map(function ($item) {
                            return [
                                'product_id' => $item->product_id,
                                'product_name' => optional($item->product)->name,
                                'quantity' => $item->quantity,
                                'price' => $item->price,
                                'subtotal' => $item->price * $item->quantity,
                            ];
                        })->values(),
                    ];
                })->values();

                return [
                    'order_id' => $order->id,
                    'cart_id' => $order->cart_id ?? null,
                    'customer_id' => $order->customer->id ?? null,
                    'customer_name' => $order->customer->customer_name ?? '-',
                    'tenant_location_id' => $order->tenantLocation->id ?? null,
                    'tenant_location_name' => $order->tenantLocation->location_name ?? '-',
                    'order_status' => optional($order->status)->order_status ?? '-',
                    'items' => $groupedItems,
                    'total_price' => $order->total_price,
                    'shipping_cost' => $order->shipping_cost ?? 0,
                    'grand_total' => $order->grand_total ?? $order->total_price,
                ];
            }),
        ]);
    }

    public function acceptOrder($orderId)
    {
        $order = Order::with(['customer.department', 'items.product', 'items.tenant', 'tenant'])
            ->find($orderId);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }

        // Pastikan order masih status waiting (id 7)
        if ($order->order_status_id != 7) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak dalam status menunggu penerimaan.',
            ], 400);
        }

        // Pastikan porter belum menerima order lain
        $existingOrder = Order::where('porter_id', $order->porter_id)
            ->whereIn('order_status_id', [1]) // status sedang kerja / accepted
            ->first();
        if ($existingOrder && $existingOrder->id != $order->id) {
            return response()->json([
                'success' => false,
                'message' => 'Porter sudah memiliki order yang sedang berjalan.',
            ], 400);
        }

        // Update status order ke "received" (ID: 1 misalnya)
        $order->order_status_id = 1;
        $order->save();

        // Update porter jadi sedang kerja
        $porter = Porter::find($order->porter_id);
        if ($porter) {
            $porter->isWorking = true;
            $porter->porter_isOnline = false;
            $porter->save();
        }

        // Kelompokkan order_items seperti format fetchOrderById
        $groupedItems = $order->items->groupBy('tenant_id')->map(function ($items, $tenantId) {
            return [
                'tenant_id' => (int) $tenantId,
                'tenant_name' => optional($items->first()->tenant)->name,
                'items' => $items->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'product_name' => optional($item->product)->name,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'subtotal' => $item->subtotal,
                    ];
                })->values(),
            ];
        })->values();

        // Response
        return response()->json([
            'success' => true,
            'message' => 'Order berhasil diterima.',
            'data' => [
                'order_id' => $order->id,
                'customer_name' => optional($order->customer)->customer_name,
                'department' => optional($order->customer->department)->department_name ?? '-',
                'tenant_name' => optional($order->tenant)->name,
                'total_price' => $order->total_price,
                'status' => $order->order_status_id,
                'created_at' => $order->created_at->toDateTimeString(),
                'order_items' => $groupedItems,
            ]
        ]);
    }

    public function rejectOrder($orderId)
    {
        $order = Order::with(['customer.department', 'items.product', 'items.tenant', 'tenant'])
            ->find($orderId);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }

        if ($order->order_status_id != 7) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak dalam status menunggu penerimaan.',
            ], 400);
        }

        if (!$order->porter_id) {
            return response()->json([
                'success' => false,
                'message' => 'Porter belum terhubung dengan order ini.',
            ], 400);
        }

        $porter = Porter::find($order->porter_id);

        if (!$porter) {
            return response()->json([
                'success' => false,
                'message' => 'Data porter tidak ditemukan.',
            ], 404);
        }

        // Cek apakah sedang timeout
        if ($porter->timeout_until && now()->lt($porter->timeout_until)) {
            return response()->json([
                'success' => false,
                'message' => 'Porter sedang dalam masa timeout hingga ' . $porter->timeout_until->format('Y-m-d H:i:s'),
            ], 403);
        }

        // Hitung jumlah penolakan
        $porter->rejected_count = $porter->rejected_count + 1;

        if ($porter->rejection_count >= 4) {
            $porter->timeout_until = now()->addDays(2); // Timeout 2 hari
            $porter->rejected_count = 0; // reset
            $porter->isWorking = false;
            $porter->porter_isOnline = false;
            $porter->save();

            $order->order_status_id = 6; // Ditolak
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Order ditolak. Anda telah menolak 4 kali, dan sekarang dalam masa timeout 2 hari.',
                'data' => [
                    'timeout_until' => $porter->timeout_until->format('Y-m-d H:i:s'),
                ],
            ]);
        } else {
            $kesempatanTersisa = 4 - $porter->rejected_count;

            $porter->isWorking = false;
            $porter->porter_isOnline = true;
            $porter->save();

            $order->order_status_id = 6;
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil ditolak.',
                'peringatan' => "Anda masih memiliki $kesempatanTersisa kesempatan sebelum terkena timeout 2 hari.",
                'data' => [
                    'order_id' => $order->id,
                    'status' => $order->order_status_id,
                    'updated_at' => $order->updated_at->toDateTimeString(),
                ]
            ]);
        }
    }


    public function viewAcceptedOrders($porterId)
    {
        $orders = Order::with([
            'customer.department',
            'tenantLocation',
            'status',
            'items.product',
            'items.tenant',
        ])
            ->where('porter_id', $porterId)
            ->where('order_status_id', 1) // status: accepted
            ->latest()
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Belum ada order yang sedang berjalan.',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $orders->map(function ($order) {
                $groupedItems = $order->items->groupBy('tenant_id')->map(function ($items, $tenantId) {
                    return [
                        'tenant_id' => (int) $tenantId,
                        'tenant_name' => optional($items->first()->tenant)->name,
                        'items' => $items->map(function ($item) {
                            return [
                                'product_id' => $item->product_id,
                                'product_name' => optional($item->product)->name,
                                'quantity' => $item->quantity,
                                'price' => $item->price,
                                'subtotal' => $item->price * $item->quantity,
                            ];
                        })->values(),
                    ];
                })->values();

                return [
                    'order_id' => $order->id,
                    'cart_id' => $order->cart_id ?? null,
                    'customer_id' => $order->customer->id ?? null,
                    'customer_name' => $order->customer->customer_name ?? '-',
                    'tenant_location_id' => $order->tenantLocation->id ?? null,
                    'tenant_location_name' => $order->tenantLocation->location_name ?? '-',
                    'order_status' => optional($order->status)->order_status ?? '-',
                    'items' => $groupedItems,
                    'total_price' => $order->total_price,
                    'shipping_cost' => $order->shipping_cost ?? 0,
                    'grand_total' => $order->grand_total ?? $order->total_price,
                ];
            }),
        ]);
    }

    public function deliverOrder($orderId)
    {
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }

        // Pastikan order dalam status "sedang dikerjakan" (received)
        if ($order->order_status_id != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak dalam status sedang dikerjakan.',
            ], 400);
        }

        // Ubah status order ke "delivered" (misalnya ID 2)
        $order->order_status_id = 4;
        $order->save();

        // Update porter jadi tidak sedang kerja dan bisa online lagi
        $porter = Porter::find($order->porter_id);
        if ($porter) {
            $porter->isWorking = false;
            $porter->porter_isOnline = true;
            $porter->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Order berhasil ditandai sebagai terkirim.',
            'data' => [
                'order_id' => $order->id,
                'new_status' => $order->order_status_id,
            ],
        ]);
    }
}
