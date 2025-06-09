<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class ActivityController extends Controller
{
    // Fungsi untuk ambil data aktivitas semua porter (dipanggil dari index view)
    public function getAllActivities()
    {
        $orders = Order::with([
            'items.product',
            'items.tenant',
            'status',
            'customer.department',
            'tenantLocation',
            'porter.department',
            'porter.bankUser'
        ])->get();

        if ($orders->isEmpty()) {
            return null;
        }

        return $orders->map(function ($order) {
            $groupedItems = $order->items->groupBy('tenant_id')->map(function ($items, $tenantId) {
                return [
                    'tenant_id' => (int) $tenantId,
                    'tenant_name' => optional($items->first()->tenant)->name,
                    'items' => $items->map(function ($item) {
                        return [
                            'product_id' => $item->product_id,
                            'product_name' => $item->product->name,
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

                // Informasi lengkap customer
                'customer' => [
                    'id' => $order->customer->id,
                    'name' => $order->customer->customer_name,
                    'nrp' => $order->customer->nrp,
                    'department' => optional($order->customer->department)->department_name,
                ],

                // Informasi lengkap porter
                'porter' => $order->porter ? [
                    'id' => $order->porter->id,
                    'name' => $order->porter->porter_name,
                    'nrp' => $order->porter->porter_nrp,
                    'department' => optional($order->porter->department)->department_name,
                    'account_number' => optional($order->porter->bankUser)->account_number,
                ] : null,

                'tenant_location_id' => $order->tenantLocation->id,
                'tenant_location_name' => $order->tenantLocation->location_name,
                'order_status' => optional($order->status)->order_status,
                'order_date' => $order->created_at->format('Y-m-d H:i:s'),
                'items' => $groupedItems,
                'total_price' => $order->total_price,
                'shipping_cost' => $order->shipping_cost,
                'grand_total' => $order->grand_total,
            ];
        });
    }

    public function index()
    {
        $activities = $this->getAllActivities();
        $porterId = 'Semua Porter';

        return view('dashboard.activity.activity', compact('activities', 'porterId'));
    }
}
