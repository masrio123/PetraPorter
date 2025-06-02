<?php

namespace App\Http\Controllers\Api;

use App\Models\OrderHistory;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class OrderHistoryController extends Controller
{
    public function getHistoryByCustId($customerId)
    {
        $histories = OrderHistory::with(['status', 'items'])
            ->where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($histories->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No order history found for this customer',
            ], 404);
        }

        $customerName = $histories->first()->customer_name ?? 'Unknown Customer';

        $formattedHistories = $histories->map(function ($history) {
            $groupedItems = [];

            foreach ($history->items as $item) {
                $tenantName = $item->tenant_name ?? 'Unknown Tenant';

                if (!isset($groupedItems[$tenantName])) {
                    $groupedItems[$tenantName] = [
                        'tenant_name' => $tenantName,
                        'products' => [],
                    ];
                }

                $groupedItems[$tenantName]['products'][] = [
                    'product_name' => $item->product_name ?? 'Unknown Product',
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total_price' => $item->total_price,
                ];
            }

            return [
                'history_id' => $history->id,
                'order_status' => $history->status->order_status ?? 'Unknown Status',
                'tenant_location_name' => $history->tenant_location_name ?? 'Unknown Location',
                'total_price' => $history->total_price,
                'shipping_cost' => $history->shipping_cost,
                'grand_total' => $history->grand_total,
                'created_at' => $history->created_at->toDateTimeString(),
                'items' => array_values($groupedItems), // reset indeks ke array numerik
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
