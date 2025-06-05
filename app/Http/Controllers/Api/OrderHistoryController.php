<?php

namespace App\Http\Controllers\Api;

use App\Models\OrderHistory;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class OrderHistoryController extends Controller
{
    public function getHistoryByCustId($customerId)
    {
        $histories = OrderHistory::with(['status', 'items'])->where('customer_id', $customerId)
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
                    'quantity' => $item->quantity ?? 0,
                    'price' => $item->price ?? 0,
                    'total_price' => $item->total_price ?? 0,
                ];
            }

            return [
                'history_id' => $history->id,
                'order_status' => optional($history->status)->order_status ?? 'Unknown Status',
                'tenant_location_name' => $history->tenant_location_name ?? 'Unknown Location',
                'porter_id' => $history->porter_id ?? null, // ✅ Ditambahkan di sini
                'total_price' => $history->total_price ?? 0,
                'shipping_cost' => $history->shipping_cost ?? 0,
                'grand_total' => $history->grand_total ?? 0,
                'created_at' => optional($history->created_at)->toDateTimeString() ?? '',
                'items' => array_values($groupedItems),
            ];
        });

        return response()->json([
            'success' => true,
            'customer_id' => $customerId,
            'customer_name' => $customerName,
            'data' => $formattedHistories,
        ]);
    }

    public function getHistoryByPorterId($porterId)
    {
        $histories = OrderHistory::with(['status', 'items'])
            ->where('porter_id', $porterId)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($histories->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada riwayat order untuk porter ini.',
            ], 404);
        }

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
                    'quantity' => $item->quantity ?? 0,
                    'price' => $item->price ?? 0,
                    'total_price' => $item->total_price ?? 0,
                ];
            }

            return [
                'history_id' => $history->id,
                'order_status' => optional($history->status)->order_status ?? 'Unknown Status',
                'tenant_location_name' => $history->tenant_location_name ?? 'Unknown Location',
                'customer_id' => $history->customer_id ?? null,
                'customer_name' => $history->customer_name ?? '-',
                'total_price' => $history->total_price ?? 0,
                'shipping_cost' => $history->shipping_cost ?? 0,
                'grand_total' => $history->grand_total ?? 0,
                'created_at' => optional($history->created_at)->toDateTimeString() ?? '',
                'items' => array_values($groupedItems),
            ];
        });

        return response()->json([
            'success' => true,
            'porter_id' => $porterId,
            'data' => $formattedHistories,
        ]);
    }
}
