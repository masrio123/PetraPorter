<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Porter;
use App\Models\BankUser;
use App\Models\Department;
use App\Models\OrderHistory;
use App\Models\Cart;
use App\Models\DeliveryPoint;
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
                                'product_name' => $item->product_name,
                                'quantity' => $item->quantity,
                                'price' => $item->price,
                                'subtotal' => $item->price * $item->quantity,
                                'notes' => $item->notes
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
        // Ambil order beserta relasi penting
        $order = Order::with([
            'customer.department',
            'items.product.tenant',
            'tenantLocation',
        ])->find($orderId);

        // Validasi order ada
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }

        // Hanya bisa terima order dengan status "waiting" (5)
        if ($order->order_status_id != 5) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak dalam status menunggu penerimaan.',
            ], 400);
        }

        // Pastikan porter belum mengerjakan order lain
        $existingOrder = Order::where('porter_id', $order->porter_id)
            ->where('order_status_id', 1) // sedang dikerjakan
            ->where('id', '!=', $order->id)
            ->first();

        if ($existingOrder) {
            return response()->json([
                'success' => false,
                'message' => 'Porter sudah memiliki order yang sedang berjalan.',
            ], 400);
        }

        // Update status order ke "accepted" (ID: 1)
        $order->order_status_id = 1;
        $order->save();

        // Update status porter
        $porter = Porter::find($order->porter_id);
        if ($porter) {
            $porter->isWorking = true;
            $porter->porter_isOnline = false;
            $porter->save();
        }

        // Kelompokkan order_items per tenant
        $groupedItems = $order->items->groupBy('tenant_id')->map(function ($items, $tenantId) {
            return [
                'tenant_id' => (int) $tenantId,
                'tenant_name' => optional(optional($items->first()->product)->tenant)->name ?? '-',
                'items' => $items->map(function ($item) {
                    return [
                        'product_name' => $item->product_name,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'subtotal' => $item->subtotal,
                    ];
                })->values(),
            ];
        })->values();

        // Response sukses
        return response()->json([
            'success' => true,
            'message' => 'Order berhasil diterima dan diproses porter',
            'data' => [
                'order_id' => $order->id,
                'customer_name' => optional($order->customer)->customer_name,
                'department' => optional($order->customer->department)->department_name ?? '-',
                'tenant_location_name' => optional($order->tenantLocation)->location_name ?? '-',
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

        if ($order->order_status_id != 5) {
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

        // Tambah jumlah penolakan
        $porter->rejected_count = ($porter->rejected_count ?? 0) + 1;

        if ($porter->rejected_count >= 4) {
            // Timeout 2 hari
            $porter->timeout_until = now()->addDays(2);
            $porter->rejected_count = 0; // reset
            $porter->isWorking = false;
            $porter->porter_isOnline = false;
            $porter->save();

            // Set order ke status waiting lagi
            $order->order_status_id = 5;
            $order->porter_id = null; // kosongkan porter
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Order ditolak. Anda telah menolak 4 kali, dan sekarang dalam masa timeout 2 hari.',
                'data' => [
                    'timeout_until' => $porter->timeout_until->format('Y-m-d H:i:s'),
                    'order_id' => $order->id,
                    'order_status' => 'waiting',
                ],
            ]);
        } else {
            // Porter masih boleh menolak
            $kesempatanTersisa = 4 - $porter->rejected_count;

            $porter->isWorking = false;
            $porter->porter_isOnline = true;
            $porter->save();

            // Reset order agar bisa dicari porter lain
            $order->order_status_id = 5;
            $order->porter_id = null;
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil ditolak.',
                'peringatan' => "Anda masih memiliki $kesempatanTersisa kesempatan sebelum terkena timeout 2 hari.",
                'data' => [
                    'order_id' => $order->id,
                    'order_status' => 'waiting',
                    'updated_at' => $order->updated_at->toDateTimeString(),
                    'kesempatan_tersisa' => $kesempatanTersisa,
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
            ->where('order_status_id', '!=', 3) // status: received
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
                                'product_name' => $item->product_name,
                                'quantity' => $item->quantity,
                                'price' => $item->price,
                                'subtotal' => $item->price * $item->quantity,
                                'notes' => $item->notes
                            ];
                        })->values(),
                    ];
                })->values();

                $cart = Cart::where('id', $order->cart_id)->first();
                $delivery_point_name = DeliveryPoint::where('id', $cart->delivery_point_id)->first()->delivery_point_name;

                return [
                    'order_id' => $order->id,
                    'cart_id' => $order->cart_id ?? null,
                    'customer_id' => $order->customer->id ?? null,
                    'customer_name' => $order->customer->customer_name ?? '-',
                    'tenant_location_id' => $order->tenantLocation->id ?? null,
                    'tenant_location_name' => $delivery_point_name ?? '-',
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
        $order = Order::with('items')->find($orderId);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }

        // Cek jika sudah finished
        if ($order->order_status_id == 3) {
            return response()->json([
                'success' => false,
                'message' => 'Order ini telah diantar sebelumnya.',
            ], 400);
        }

        // Pastikan order memang sedang dikerjakan
        if ($order->order_status_id != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak dalam status sedang dikerjakan.',
            ], 400);
        }

        // Ubah status order jadi delivered
        $order->order_status_id = 2;
        $order->save();

        // Update status porter
        $porter = Porter::find($order->porter_id);
        if ($porter) {
            $porter->isWorking = false;
            $porter->porter_isOnline = true;
            $porter->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Order dlm status diantar.',
            'data' => [
                'order_id' => $order->id,
                'new_status' => $order->order_status_id,
            ],
        ]);
    }

    public function finishOrder($orderId)
    {
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }

        // Pastikan status saat ini adalah delivered (4)
        if ($order->order_status_id != 2) {
            return response()->json([
                'success' => false,
                'message' => 'Order belum diantar, tidak bisa diselesaikan.',
            ], 400);
        }

        // Cek jika sudah selesai sebelumnya
        if ($order->order_status_id == 3) {
            return response()->json([
                'success' => false,
                'message' => 'Order ini sudah ditandai sebagai selesai.',
            ], 400);
        }

        // Ubah status menjadi finished
        $order->order_status_id = 3;
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Order selesai.',
            'data' => [
                'order_id' => $order->id,
                'new_status' => $order->order_status_id,
            ],
        ]);
    }

    public function getPorterActivity($porterId)
    {
        try {
            $orders = Order::with([
                'items.product',
                'items.tenant',
                'status',
                'customer',
                'tenantLocation'
            ])->where('porter_id', $porterId)->get();

            if ($orders->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No orders found for this porter.',
                ], 404);
            }

            $formattedOrders = $orders->map(function ($order) {
                $groupedItems = $order->items->groupBy('tenant_id')->map(function ($items, $tenantId) {
                    return [
                        'tenant_id' => (int) $tenantId,
                        'tenant_name' => optional($items->first()->tenant)->name,
                        'items' => $items->map(function ($item) {
                            return [
                                'product_name' => $item->product_name,
                                'quantity' => $item->quantity,
                                'price' => $item->price,
                                'subtotal' => $item->subtotal,
                                'notes' => $item->notes
                            ];
                        })->values(),
                    ];
                })->values();

                return [
                    'order_id' => $order->id,
                    'cart_id' => $order->cart_id,
                    'customer_id' => $order->customer->id,
                    'customer_name' => $order->customer->customer_name,
                    'tenant_location_id' => $order->tenantLocation->id,
                    'tenant_location_name' => $order->tenantLocation->location_name,
                    'order_status' => optional($order->status)->order_status,
                    'items' => $groupedItems,
                    'total_price' => $order->total_price,
                    'shipping_cost' => $order->shipping_cost,
                    'grand_total' => $order->grand_total,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'List of orders for porter_id: ' . $porterId,
                'data' => $formattedOrders,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data order untuk porter.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function workSummary($porterId)
    {
        try {
            // Ambil semua order yang memiliki porter_id sama
            $orders = Order::where('porter_id', $porterId)->get();

            // Jika tidak ada order ditemukan
            if ($orders->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No orders found for this porter.',
                ], 404);
            }

            // Hitung total order dan total pendapatan dari shipping cost
            $totalOrders = $orders->count();
            $totalIncome = $orders->sum('shipping_cost');

            return response()->json([
                'success' => true,
                'message' => 'Summary of orders handled by porter_id: ' . $porterId,
                'data' => [
                    'total_orders_handled' => $totalOrders,
                    'total_income' => $totalIncome,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate porter summary.',
                'error' => $e->getMessage(),
            ], 500);
        }
    } // Di controller Laravel Anda
    public function profileApi($id)
    {
        // Menggunakan try-catch adalah praktik yang baik jika findOrFail bisa gagal
        try {
            $porter = Porter::with(['department', 'bankUser'])->findOrFail($id);

            // Bungkus respons dalam struktur yang diharapkan oleh Flutter
            return response()->json([
                'success' => true,
                'message' => 'Profil porter berhasil diambil.',
                'data'    => [
                    'porter_name'    => $porter->porter_name,
                    'porter_nrp'     => $porter->porter_nrp,
                    'department'     => $porter->department?->department_name,
                    'account_number' => $porter->bankUser?->account_number,
                ]
            ], 200); // Kode status OK

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data porter tidak ditemukan.',
                'data'    => null
            ], 404); // Kode status Not Found
        }
    }

    public function getToggleIsOpen($id)
    {
        $porter = Porter::find($id);

        if (!$porter) {
            return response()->json([
                'success' => false,
                'message' => 'Porter tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'porter_isOnline' => $porter->porter_isOnline == 1 ? true : false
            ]
        ]);
    }

    // PUT: Ubah status isOnline porter
    public function updateToggleIsOpen(Request $request, $id)
    {
        $request->validate([
            'porter_isOnline' => 'required|boolean'
        ]);

        $porter = Porter::find($id);

        if (!$porter) {
            return response()->json([
                'success' => false,
                'message' => 'Porter tidak ditemukan.'
            ], 404);
        }

        $porter->porter_isOnline = $request->porter_isOnline;
        $porter->save();

        return response()->json([
            'success' => true,
            'message' => 'Status online berhasil diperbarui.',
            'data' => [
                'porter_isOnline' => $porter->porter_isOnline == 1 ? true : false
            ]
        ]);
    }
}
