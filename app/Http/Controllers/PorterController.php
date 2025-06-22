<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Porter;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PorterController extends Controller
{
    /**
     * Menampilkan daftar semua porter dengan filter.
     */
    public function index(Request $request)
    {
        // Menghapus eager loading 'bankUser' karena sudah tidak relevan
        $query = Porter::with('department');

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('porter_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('porter_nrp', 'like', '%' . $searchTerm . '%');
            });
        }

        if ($request->filled('status')) {
            $status = $request->status;
            $query->where(function ($q) use ($status) {
                if ($status == 'online') {
                    $q->where('porter_isOnline', true)->where('isWorking', false)->where(fn($sub) => $sub->whereNull('timeout_until')->orWhere('timeout_until', '<', now()));
                } elseif ($status == 'offline') {
                    $q->where('porter_isOnline', false)->where('isWorking', false)->where(fn($sub) => $sub->whereNull('timeout_until')->orWhere('timeout_until', '<', now()));
                } elseif ($status == 'working') {
                    $q->where('isWorking', true);
                } elseif ($status == 'timeout') {
                    $q->where('timeout_until', '>', now());
                }
            });
        }

        $porters = $query->latest()->get();
        // Mengirim data department untuk filter di view
        $departments = Department::all();

        return view('dashboard.porter.index', compact('porters', 'departments'));
    }

    /**
     * Menampilkan form untuk membuat porter baru.
     */
    public function create()
    {
        $departments = Department::all();
        // Menghapus $bankUsers karena tidak lagi digunakan
        return view('dashboard.porter.create', compact('departments'));
    }

    /**
     * Menyimpan porter baru ke database.
     */
    public function store(Request $request)
    {
        // Menyesuaikan validasi dengan kolom baru
        $validatedData = $request->validate([
            'porter_name'     => ['required', 'string', 'max:255'],
            'porter_nrp'      => ['required', 'string', 'max:255', 'unique:porters,porter_nrp'],
            'department_id'   => ['nullable', 'exists:departments,id'],
            'account_numbers' => ['required', 'string', 'max:50', 'unique:porters,account_numbers'],
            'bank_name'       => ['required', 'string', 'max:50'],
            'username'        => ['required', 'string', 'max:255', 'same:porter_name'],
            'porter_isOnline' => ['required', 'boolean'],
        ], [
            'username.same'      => 'Nama Pemilik Rekening harus sama dengan Nama Porter.',
            'porter_nrp.unique'  => 'NRP ini sudah terdaftar.',
            'account_numbers.unique' => 'Nomor rekening ini sudah digunakan oleh Porter lain.',
        ]);

        Porter::create($validatedData);

        return redirect()->route('dashboard.porters.index')->with('success', 'Porter berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit data porter.
     */
    public function edit(Porter $porter)
    {
        $departments = Department::all();
        // Menghapus $bankUsers karena tidak lagi digunakan
        return view('dashboard.porter.edit', compact('porter', 'departments'));
    }

    /**
     * Memperbarui data porter di database.
     */
    public function update(Request $request, Porter $porter)
    {
        // Handle aksi timeout/cancel_timeout secara terpisah
        if ($request->has('action')) {
            if ($request->action === 'timeout') {
                $porter->update(['timeout_until' => now()->addDays(2), 'porter_isOnline' => false]);
                $message = 'Porter berhasil di-timeout.';
            } else { // 'cancel_timeout'
                $porter->update(['timeout_until' => null]);
                $message = 'Timeout untuk porter berhasil dicabut.';
            }
            return redirect()->route('dashboard.porters.index')->with('success', $message);
        }

        // Validasi untuk update data utama porter
        $validatedData = $request->validate([
            'porter_name'     => ['required', 'string', 'max:255'],
            // Validasi unik NRP, mengabaikan data porter saat ini
            'porter_nrp'      => ['required', 'string', 'max:255', 'unique:porters,porter_nrp,' . $porter->id],
            'department_id'   => ['nullable', 'exists:departments,id'],
            // Validasi unik nomor rekening, mengabaikan data porter saat ini
            'account_numbers' => ['required', 'string', 'max:50', 'unique:porters,account_numbers,' . $porter->id],
            'bank_name'       => ['required', 'string', 'max:50'],
            'username'        => ['required', 'string', 'max:255', 'same:porter_name'],
            'porter_isOnline' => ['required', 'boolean'],
        ], [
            'username.same'      => 'Nama Pemilik Rekening harus sama dengan Nama Porter.',
            'porter_nrp.unique'  => 'NRP ini sudah digunakan oleh Porter lain.',
            'account_numbers.unique' => 'Nomor rekening ini sudah digunakan oleh Porter lain.',
        ]);

        $porter->update($validatedData);

        return redirect()->route('dashboard.porters.index')->with('success', 'Data porter berhasil diperbarui.');
    }

    /**
     * Menghapus data porter dari database.
     */
    public function destroy(Porter $porter)
    {
        // if ($porter->orders()->whereNotIn('status_id', [4, 5])->exists()) {
        //     return back()->with('error', 'Porter tidak dapat dihapus karena masih memiliki order aktif.');
        // }

        $porter->delete();
        return redirect()->route('dashboard.porters.index')->with('success', 'Porter berhasil dihapus.');
    }

    /**
     * Menghitung jumlah porter yang online dan tidak sedang bekerja.
     */
    public static function countOnlinePorters()
    {
        return Porter::where('porter_isOnline', true)
            ->where('isWorking', false)
            ->where(function ($query) {
                $query->whereNull('timeout_until')
                    ->orWhere('timeout_until', '<', Carbon::now());
            })
            ->count();
    }
}
