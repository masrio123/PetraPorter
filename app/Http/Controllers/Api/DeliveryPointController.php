<?php

namespace App\Http\Controllers\Api;

use App\Models\DeliveryPoint;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DeliveryPointController extends Controller
{
    // Ambil semua delivery point
    public function fetchDeliveryPoint()
    {
        $delivery_points = DeliveryPoint::all();
        return response()->json([
            'status' => 'success',
            'data' => $delivery_points
        ], 200);
    }

    // Tambah delivery point
    // Tambah Delivery Point
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'delivery_point_name' => 'required|string|max:255',
        ]);

        // Simpan data ke database
        $deliveryPoint = DeliveryPoint::create($validated);

        // Kembalikan response sukses
        return response()->json([
            'status' => 'success',
            'message' => 'Delivery point created successfully',
            'data' => $deliveryPoint,
        ], 201);
    }


    // Edit/update delivery point
    public function edit(Request $request, $id)
    {
        $delivery_point = DeliveryPoint::find($id);

        if (!$delivery_point) {
            return response()->json([
                'status' => 'error',
                'message' => 'Delivery point not found'
            ], 404);
        }

        $request->validate([
            'delivery_point_name' => 'required|string|max:255'
        ]);

        $delivery_point->update([
            'delivery_point_name' => $request->delivery_point_name
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Delivery point updated successfully',
            'data' => $delivery_point
        ], 200);
    }

    // Hapus delivery point
    public function destroy($id)
    {
        $delivery_point = DeliveryPoint::find($id);

        if (!$delivery_point) {
            return response()->json([
                'status' => 'error',
                'message' => 'Delivery point not found'
            ], 404);
        }

        $delivery_point->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Delivery point deleted successfully'
        ], 200);
    }
}
