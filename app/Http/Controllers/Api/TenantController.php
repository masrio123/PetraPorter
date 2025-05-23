<?php

namespace App\Http\Controllers\Api;

use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Models\TenantLocation;
use App\Http\Controllers\Controller;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::select([
            "tenants.name",
            "tenant_locations.location_name as location",
            "tenants.isOpen",
        ])
            ->join('tenant_locations', 'tenants.tenant_location_id', '=', 'tenant_locations.id')
            ->get()
            ->map(function ($tenant) {
                return [
                    'name' => $tenant->name,
                    'location' => $tenant->location,
                    'isOpen' => $tenant->isOpen,
                ];
            });

        return response()->json($tenants);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tenant_location_id' => 'required|exists:tenant_locations,id',
            'isOpen' => 'required|boolean',
        ]);

        $tenant = Tenant::create($validated);

        return response()->json([
            'message' => 'Tenant berhasil ditambahkan.',
            'data' => $tenant
        ], 201);
    }

    public function show(string $id)
    {
        $tenant = Tenant::with('location')->find($id);

        if (!$tenant) {
            return response()->json(['message' => 'Tenant tidak ditemukan'], 404);
        }

        return response()->json([
            'name' => $tenant->name,
            'location' => $tenant->location->location_name ?? null,
            'isOpen' => $tenant->isOpen,
        ]);
    }


    public function update(Request $request, string $id)
    {
        $tenant = Tenant::find($id);
        if (!$tenant) {
            return response()->json(['message' => 'Tenant tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tenant_location_id' => 'required|exists:tenant_locations,id',
            'isOpen' => 'required|boolean',
        ]);

        $tenant->update($validated);

        return response()->json([
            'message' => 'Tenant berhasil diperbarui.',
            'data' => $tenant
        ]);
    }

    public function destroy($id)
    {
        $tenant = Tenant::find($id);
        if (!$tenant) {
            return response()->json(['message' => 'Tenant tidak ditemukan'], 404);
        }

        if ($tenant->products()->count() > 0) {
            return response()->json(['message' => 'Tenant tidak dapat dihapus karena masih memiliki produk.'], 400);
        }

        $tenant->delete();

        return response()->json(['message' => 'Tenant berhasil dihapus.']);
    }
}
