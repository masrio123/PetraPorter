<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\TenantLocation;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tenants = Tenant::select([
            "tenants.id",
            "tenants.name",
            "tenant_locations.location_name as location",
            "tenants.isOpen",
        ])
            ->join('tenant_locations', 'tenants.tenant_location_id', '=', 'tenant_locations.id')
            ->get();

        return view("dashboard.tenant.index", [
            'tenants' => $tenants

        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tenantLocations = TenantLocation::all();
        return view('dashboard.tenant.create', [
            'tenantLocations' => $tenantLocations
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi input dari form
        $request->validate([
            'name' => 'required|string|max:255',
            'tenant_location_id' => 'required|exists:tenant_locations,id',
            'isOpen' => 'required|boolean',
        ]);

        // Simpan data ke database
        Tenant::create([
            'name' => $request->name,
            'tenant_location_id' => $request->tenant_location_id,
            'isOpen' => $request->isOpen,
        ]);

        // Redirect kembali ke halaman index tenant
        return redirect()->route('dashboard.tenants.index')->with('success', 'Tenant berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $tenant = Tenant::with('location')->findOrFail($id);

        return view('dashboard.tenant.show', [
            'tenant' => $tenant,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenantLocations = TenantLocation::all();

        return view('dashboard.tenant.edit', [
            'tenant' => $tenant,
            'tenantLocations' => $tenantLocations,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'tenant_location_id' => 'required|exists:tenant_locations,id',
            'isOpen' => 'required|boolean',
        ]);

        $tenant = Tenant::findOrFail($id);
        $tenant->update([
            'name' => $request->name,
            'tenant_location_id' => $request->tenant_location_id,
            'isOpen' => $request->isOpen,
        ]);

        return redirect()->route('dashboard.tenants.index')->with('success', 'Tenant berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $tenant = Tenant::findOrFail($id);

        if ($tenant->products()->count() > 0) {
            return redirect()->back()->with('error', 'Tenant tidak dapat dihapus karena masih memiliki produk.');
        }

        $tenant->delete();
        return redirect()->route('dashboard.tenants.index')->with('success', 'Tenant berhasil dihapus.');
    }
}
