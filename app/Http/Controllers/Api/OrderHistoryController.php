<?php

namespace App\Http\Controllers\Api;

use App\Models\OrderHistory;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class OrderHistoryController extends Controller
{
    public function getHistoryByCustId($customerId)
    {
        $histories = OrderHistory::with(['status', 'items.product.tenant', 'customer'])
            ->where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($histories->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No order history found for this customer',
            ], 404);
        }

        $customerName = $histories->first()->customer->customer_name ?? 'Unknown Customer';

        $formattedHistories = $histories->map(function ($history) {
            return [
                'history_id' => $history->id,
                'order_status' => $history->status->order_status ?? 'Unknown Status',
                'tenant_location_name' => $history->tenant_location_name,
                'total_price' => $history->total_price,
                'shipping_cost' => $history->shipping_cost,
                'grand_total' => $history->grand_total,
                'created_at' => $history->created_at->toDateTimeString(),
                'items' => $history->items->map(function ($item) {
                    return [
                        'product_name' => $item->product->name ?? 'Unknown Product',
                        'quantity' => $item->quantity,
                        'tenant_name' => $item->product->tenant->name ?? 'Unknown Tenant',
                        'price' => 0,  // fixed price sesuai request
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'customer_id' => $customerId,
            'customer_name' => $customerName,
            'data' => $formattedHistories,
        ]);
    }
}
