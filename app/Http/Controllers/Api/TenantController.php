<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Models\TenantLocation;

class TenantController extends Controller
{
    // View all tenants
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'tenant_location_id' => 'required|exists:tenant_locations,id',
        ]);

        Tenant::create([
            'name' => $request->name,
            'tenant_location_id' => $request->tenant_location_id,
            'isOpen' => $request->has('isOpen'),
        ]);

        // Redirect dengan query param location=gedung_id
        return redirect()->route('tenants.admin', ['location' => $request->tenant_location_id])
            ->with('success', 'Tenant berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $tenant = Tenant::find($id);
        if (!$tenant) {
            return redirect()->back()->with('error', 'Tenant tidak ditemukan.');
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'tenant_location_id' => 'sometimes|required|exists:tenant_locations,id',
        ]);
        $validated['isOpen'] = $request->has('isOpen');

        $tenant->update($validated);

        return redirect()->route('tenants.admin', ['location' => $tenant->tenant_location_id])
            ->with('success', 'Tenant berhasil diupdate.');
    }

    public function destroy($id)
    {
        $tenant = Tenant::find($id);
        if (!$tenant) {
            return redirect()->back()->with('error', 'Tenant tidak ditemukan.');
        }

        // Simpan dulu lokasi sebelum hapus
        $location_id = $tenant->tenant_location_id;
        $tenant->delete();

        return redirect()->route('tenants.admin', ['location' => $location_id])
            ->with('success', 'Tenant berhasil dihapus.');
    }

    public function viewTable()
    {
        $locations = TenantLocation::with('tenants')->get();
        return view('admin', compact('locations'));
    }
}
