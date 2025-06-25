<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Carbon\Carbon;


class ActivityController extends Controller
{
    // Fungsi untuk ambil data aktivitas semua porter (dipanggil dari index view)
    public function getAllActivities()
    {
        // PENYEMPURNAAN: Relasi porter.department dipastikan dipanggil dengan benar
        $orders = Order::with([
            'items.product',
            'items.tenant',
            'status',
            'customer.department', // Memuat relasi department dari customer
            'tenantLocation',
            'porter.department',   // Memuat relasi department dari porter
        ])->latest()->get(); // PENYEMPURNAAN: Mengurutkan dari yang terbaru

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
                'customer' => [
                    'id' => optional($order->customer)->id,
                    'name' => optional($order->customer)->customer_name,
                    'identity_number' => optional($order->customer)->identity_number,
                    'department' => optional(optional($order->customer)->department)->department_name,
                    'account_numbers' => $order->customer->account_numbers,
                ],

                'porter' => $order->porter ? [
                    'id' => $order->porter->id,
                    'name' => $order->porter->porter_name,
                    'nrp' => $order->porter->porter_nrp,
                    'department' => optional($order->porter->department)->department_name,
                    'account_numbers' => $order->porter->account_numbers,
                ] : null,

                'tenant_location_id' => optional($order->tenantLocation)->id,
                'tenant_location_name' => optional($order->tenantLocation)->location_name,
                'order_status' => optional($order->status)->order_status,
                'order_date' => $order->created_at->format('Y-m-d H:i:s'),
                'items' => $groupedItems,
                'total_price' => $order->total_price,
                'shipping_cost' => $order->shipping_cost,
                'grand_total' => $order->grand_total,
            ];
        });
    }

    public static function getDailySummary()
    {
        $today = Carbon::today();

        $completedOrders = Order::with('status')
            ->whereHas('status', function ($query) {
                // PENYEMPURNAAN: Sebaiknya gunakan ID status jika memungkinkan (lebih cepat)
                // Misalnya: $query->where('id', 3);
                $query->where('order_status', 'finished');
            })
            ->whereDate('created_at', $today)
            ->get();

        return [
            'date' => $today->toDateString(),
            'total_orders_completed' => $completedOrders->count(),
            'total_income' => $completedOrders->sum('shipping_cost'),
        ];
    }

    public function index()
    {
        $activities = $this->getAllActivities();
        $summary = $this->getDailySummary();
        $porterId = 'Semua Porter';

        return view('dashboard.activity.activity', compact('activities', 'porterId', 'summary'));
    }
}