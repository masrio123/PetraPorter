<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\DeliveryPoint;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Tampilkan semua order
    public function showAll()
    {
        try {
            $orders = Order::with([
                'items.product',
                'items.tenant', // pastikan relasi tenant ada di OrderItem
                'status',
                'customer',
                'tenantLocation',
            ])->get();

            $formattedOrders = $orders->map(function ($order) {
                // Kelompokkan items berdasarkan tenant_id
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

                return [
                    'order_id' => $order->id,
                    'cart_id' => $order->cart_id,
                    'customer_id' => $order->customer->id,
                    'customer_name' => $order->customer->customer_name,
                    'tenant_location_id' => $order->tenantLocation->id,
                    'tenant_location_name' => $order->tenantLocation->location_name,
                    'items' => $groupedItems,
                    'total_price' => $order->total_price,
                    'order_status' => optional($order->status)->order_status,
                    'shipping_cost' => $order->shipping_cost,
                    'grand_total' => $order->grand_total,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'List of all orders',
                'data' => $formattedOrders,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data orders.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getDeliverypoints()
    {
        $delivery = DeliveryPoint::where('isActive', 1)->get();

        return response()->json([
            'success' => true,
            'message' => 'List of active delivery point',
            'data' => $delivery,
        ], 200);
    }


    public function fetchOrderById($id)
    {
        try {
            $order = Order::with([
                'items.product',
                'items.tenant',
                'status',
                'customer',
                'tenantLocation',
            ])->find($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

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

            $formattedOrder = [
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

            return response()->json([
                'success' => true,
                'message' => 'Order detail from order_id: ' . $order->id,
                'data' => $formattedOrder,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getCustomerActivity($customerId)
    {
        try {
            $orders = Order::with(['items.tenant', 'status', 'customer', 'tenantLocation', 'porter.department'])
                ->where('customer_id', $customerId)->get();

            if ($orders->isEmpty()) {
                return response()->json(['success' => true, 'message' => 'No orders found for this customer.', 'data' => []]);
            }

            $formattedOrders = $orders->map(function ($order) {
                $groupedItems = $order->items->groupBy('tenant_id')->map(function ($items, $tenantId) {
                    return [
                        'tenant_id' => (int) $tenantId,
                        'tenant_name' => optional($items->first()->tenant)->name ?? 'Tenant Dihapus',
                        'items' => $items->map(function ($item) {
                            return [
                                'product_name' => $item->product_name,
                                'quantity' => $item->quantity,
                                'price' => $item->price,
                                'subtotal' => $item->subtotal,
                                'notes' => $item->notes,
                            ];
                        })->values(),
                    ];
                })->values();

                return [
                    'order_id' => $order->id,
                    'cart_id' => $order->cart_id,
                    'customer_id' => optional($order->customer)->id,
                    'customer_name' => optional($order->customer)->customer_name,
                    'tenant_location_id' => optional($order->tenantLocation)->id,
                    'tenant_location_name' => optional($order->tenantLocation)->location_name,
                    'porter' => $order->porter ? [
                        'porter_id' => $order->porter->id,
                        'name' => $order->porter->porter_name,
                        'nrp' => $order->porter->porter_nrp,
                        'department' => optional($order->porter->department)->department_name ?? '-',
                        'bank_name' => $order->porter->bank_name,
                        'account_number' => $order->porter->account_numbers,
                        'username' => $order->porter->username,
                    ] : null,
                    'order_status' => optional($order->status)->order_status ?? 'Unknown',
                    'order_date' => $order->created_at->format('Y-m-d H:i:s'),
                    'items' => $groupedItems,
                    'total_price' => $order->total_price,
                    'shipping_cost' => $order->shipping_cost,
                    'grand_total' => $order->grand_total,
                ];
            });

            return response()->json(['success' => true, 'message' => 'List of orders for customer_id: ' . $customerId, 'data' => $formattedOrders]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data order customer.', 'error' => $e->getMessage()], 500);
        }
    }
}
