<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Models\TenantLocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;

class TenantController extends Controller
{
    /**
     * Menampilkan daftar semua tenant, dikelompokkan berdasarkan lokasi.
     * Halaman ini juga berisi modal untuk mengedit data.
     *
     * @return View
     */
    public function index(): View
    {
        $tenants = Tenant::with(['products', 'tenantLocation'])
            ->latest('created_at')
            ->get();

        $tenantLocations = TenantLocation::all();

        return view("dashboard.tenant.index", [
            'tenants' => $tenants,
            'tenantLocations' => $tenantLocations,
        ]);
    }

    public function create(): View
    {
        $tenantLocations = TenantLocation::all();
        return view('dashboard.tenant.create', [
            'tenantLocations' => $tenantLocations
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'tenant_location_id' => 'required|exists:tenant_locations,id',
        ]);

        // Memulai transaksi database
        DB::beginTransaction();
        try {
            // 1. Buat User baru
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make('tenant123'),
            ]);

            // 2. [INI PERBAIKANNYA] Berikan role 'tenant' kepada user baru
            $user->assignRole('tenant');

            // 3. Buat Tenant baru dan hubungkan dengan user_id
            $tenant = new Tenant();
            $tenant->name = $request->name;
            $tenant->tenant_location_id = $request->tenant_location_id;
            $tenant->user_id = $user->id;
            $tenant->save();

            // Commit transaksi jika semua berhasil
            DB::commit();

            return redirect()->route('dashboard.tenants.index')->with('success', 'Tenant dan Akun User berhasil ditambahkan.');
        } catch (\Exception $e) {
            // Batalkan semua operasi jika terjadi error
            DB::rollBack();
            report($e);
            return back()->with('error', 'Gagal menambahkan tenant. Terjadi kesalahan pada server.')->withInput();
        }
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        // Untuk saat ini, update belum diubah. Anda bisa menambahkan logika update user jika diperlukan.
        $request->validate([
            'name' => 'required|string|max:255',
            'tenant_location_id' => 'required|exists:tenant_locations,id',
        ]);

        $tenant = Tenant::findOrFail($id);
        $tenant->update($request->only(['name', 'tenant_location_id', 'isOpen']));

        // Juga update nama user terkait jika nama tenant diubah
        if ($tenant->user) {
            $tenant->user()->update(['name' => $request->name]);
        }

        return redirect()->route('dashboard.tenants.index')->with('success', 'Tenant berhasil diperbarui.');
    }

    public function destroy(Request $request, Tenant $tenant)
    {
        // Validasi bahwa alasan wajib diisi
        $request->validate([
            'deletion_reason' => 'required|string|min:10',
        ], [
            'deletion_reason.required' => 'Alasan penonaktifan wajib diisi.',
            'deletion_reason.min' => 'Alasan harus minimal 10 karakter.',
        ]);

        // Simpan alasan sebelum melakukan soft delete
        $tenant->deletion_reason = $request->input('deletion_reason');
        $tenant->save();

        // Lakukan soft delete (event di model akan men-soft-delete produknya juga)
        $tenant->delete();

        return redirect()->route('dashboard.tenants.index')->with('success', 'Tenant berhasil dinonaktifkan.');
    }

    public function trashed()
    {
        // Ambil HANYA data tenant yang sudah di-soft-delete
        $trashedTenants = Tenant::onlyTrashed()->latest('deleted_at')->get();

        return view('dashboard.tenant.trashed', ['trashedTenants' => $trashedTenants]);
    }

    /**
     * Memulihkan tenant dari Recycle Bin.
     */
    public function restore($id)
    {
        // Cari tenant di dalam "sampah", jika tidak ada akan error 404
        $tenant = Tenant::withTrashed()->findOrFail($id);

        // Kosongkan alasan sebelum restore
        $tenant->deletion_reason = null;
        $tenant->save();

        // Pulihkan tenant (event di model akan memulihkan produknya juga)
        $tenant->restore();

        return redirect()->route('dashboard.tenants.trashed')->with('success', 'Tenant berhasil dipulihkan.');
    }
}
