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
            "tenants.id",
            "tenants.name",
            "tenant_locations.location_name as location",
            "tenants.isOpen",
        ])
            ->join('tenant_locations', 'tenants.tenant_location_id', '=', 'tenant_locations.id')
            ->get()
            ->map(function ($tenant) {
                return [
                    'id' => $tenant->id,
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
        $tenant = Tenant::select([
            'tenants.id',
            'tenants.name',
            'tl.location_name as location',
            'tenants.isOpen'
        ])
            ->join('tenant_locations as tl', 'tenants.tenant_location_id', '=', 'tl.id')
            ->where('tenants.id', $id) // ❗ Filter sesuai ID
            ->first();

        if (!$tenant) {
            return response()->json(['message' => 'Tenant tidak ditemukan'], 404);
        }

        return response()->json([
            'id' => $tenant->id,
            'name' => $tenant->name,
            'location' => $tenant->location,
            'isOpen' => $tenant->isOpen,
        ]);
    }

    public function update(Request $request, string $id)
    {
        try {
            $tenant = Tenant::find($id);

            if (!$tenant) {
                return response()->json(['message' => 'Tenant tidak ditemukan'], 404);
            }

            // Validasi sesuai dengan data dari Flutter
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'tenant_location_id' => 'required|integer|exists:tenant_locations,id',
                'is_open' => 'required|boolean',
            ]);

            // Update tenant
            $tenant->update([
                'name' => $validated['name'],
                'tenant_location_id' => $validated['tenant_location_id'],
                'is_open' => $validated['is_open'],
            ]);

            return response()->json([
                'message' => 'Tenant berhasil diperbarui.',
                'data' => $tenant->load('tenantLocation'), // opsional: kalau kamu pakai relasi eager loading
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui tenant.',
                'error' => $e->getMessage()
            ], 500);
        }
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

    public function toggleIsOpen(string $id)
    {
        try {
            $tenant = Tenant::find($id);

            if (!$tenant) {
                return response()->json(['message' => 'Tenant tidak ditemukan'], 404);
            }

            $tenant->isOpen = !$tenant->isOpen;
            $tenant->save();

            return response()->json([
                'message' => 'Status isOpen berhasil diubah.',
                'data' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'isOpen' => $tenant->isOpen
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengubah status isOpen.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function fetchTenantsByLocation($tenantLocationId)
    {
        $tenantLocation = TenantLocation::with('tenants:id,name,tenant_location_id')->find($tenantLocationId);

        if (!$tenantLocation) {
            return response()->json([
                'success' => false,
                'message' => 'Location not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            $tenantLocation->location_name => $tenantLocation->tenants
        ]);
    }
}
