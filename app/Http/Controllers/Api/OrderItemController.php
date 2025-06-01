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
        DB::beginTransaction();

        try {
            $order = Order::with([
                'items.product.tenant.tenantLocation',
                'customer',
                'status',
                'orderHistories.items'
            ])->find($orderId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found.',
                ], 404);
            }

            $processedStatuses = ['completed', 'delivered'];
            $currentStatus = $order->status->order_status ?? '';

            if (in_array($currentStatus, $processedStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order already processed and cannot be canceled.',
                ], 400);
            }

            $canceledStatus = OrderStatus::where('order_status', 'canceled')->first();
            if (!$canceledStatus) {
                return response()->json([
                    'success' => false,
                    'message' => 'Canceled status not found.',
                ], 500);
            }

            // Hapus histori lama
            foreach ($order->orderHistories as $oldHistory) {
                $oldHistory->items()->delete();
                $oldHistory->delete();
            }

            // Tambahkan histori pembatalan
            $history = OrderHistory::create([
                'order_id' => $order->id,
                'order_status_id' => $canceledStatus->id,
                'porter_id' => $order->porter_id ?? null,
            ]);

            foreach ($order->items as $item) {
                $product = $item->product;
                $tenant = $product->tenant;

                OrderHistoryItem::create([
                    'order_history_id'     => $history->id,
                    'customer_id'          => $order->customer_id,
                    'user_id'              => $order->customer->user_id ?? null,
                    'tenant_location_name' => $tenant->tenantLocation->location_name ?? 'N/A',
                    'tenant_name'          => $tenant->name ?? 'Unknown Tenant',
                    'product_name'         => $product->name,
                    'quantity'             => $item->quantity,
                    'price'                => 0,
                    'total_price'          => 0,
                    'shipping_cost'        => 0,
                    'grand_total'          => 0,
                ]);
            }

            // Update status dan kosongkan biaya
            $order->status()->associate($canceledStatus);
            $order->shipping_cost = 0;
            $order->grand_total = 0;
            $order->total_price = 0;
            $order->save();

            // Hapus item order
            $order->items()->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order has been successfully canceled.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while canceling the order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function showCanceledOrders()
    {
        $canceledStatus = OrderStatus::where('order_status', 'canceled')->first();

        if (!$canceledStatus) {
            return response()->json([
                'success' => false,
                'message' => 'Canceled status not found.',
            ], 500);
        }

        $orders = OrderHistory::with([
            'items', // semua item produk dalam histori ini
            'customer.department', // relasi ke customer & department
        ])
            ->where('order_status_id', $canceledStatus->id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }
}
