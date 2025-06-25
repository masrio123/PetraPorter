<?php

namespace App\Http\Controllers;

use App\Models\DeliveryPoint;
use Illuminate\Http\Request;

class DeliveryPointController extends Controller
{
    // Tampilkan daftar delivery point
    public function index()
    {
        $delivery_points = DeliveryPoint::all();
        return view('dashboard.delivery-point.index', compact('delivery_points'));
    }

    // Tampilkan form tambah delivery point
    public function create()
    {
        return view('dashboard.delivery-point.create');
    }

    // Simpan delivery point baru
    public function store(Request $request)
    {
        $request->validate([
            'delivery_point_name' => 'required|string|max:255',
        ]);
        DeliveryPoint::create([
            'delivery_point_name' => $request->delivery_point_name,
        ]);

        return redirect()->route('dashboard.delivery-points.index')
            ->with('success', 'Delivery point berhasil ditambahkan.');
    }

    // Update delivery point
    public function update(Request $request, $id)
    {
        $request->validate([
            'delivery_point_name' => 'required|string|max:255',
        ]);

        $delivery_point = DeliveryPoint::findOrFail($id);
        $delivery_point->update([
            'delivery_point_name' => $request->delivery_point_name,
        ]);

        return redirect()->route('dashboard.delivery-points.index')
            ->with('success', 'Delivery point berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $delivery_point = DeliveryPoint::findOrFail($id);
        $delivery_point->delete();

        return redirect()->route('dashboard.delivery-points.index')
            ->with('success', 'Delivery point berhasil dihapus.');
    }

    public function toggleStatus($id)
    {
        $delivery_point = DeliveryPoint::findOrFail($id);

        // Toggle nilai is_active (true -> false, false -> true)
        $delivery_point->isActive = !$delivery_point->isActive;
        $delivery_point->save();

        return redirect()->route('dashboard.delivery-points.index')
            ->with('success', 'Status delivery point berhasil diperbarui.');
    }
}
