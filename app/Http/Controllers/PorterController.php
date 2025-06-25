<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Porter;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
        // 1. Validasi input dari form
        $validatedData = $request->validate([
            'porter_name'     => ['required', 'string', 'max:255'],
            // Pastikan NRP unik di tabel porters dan juga akan menjadi basis email yang unik di tabel users
            'porter_nrp'      => ['required', 'string', 'max:255', 'unique:porters,porter_nrp'],
            'department_id'   => ['nullable', 'exists:departments,id'],
            'account_numbers' => ['required', 'string', 'max:50', 'unique:porters,account_numbers'],
            'bank_name'       => ['required', 'string', 'max:50'],
        ], [
            'porter_nrp.unique'      => 'NRP ini sudah terdaftar.',
            'account_numbers.unique' => 'Nomor rekening ini sudah digunakan oleh Porter lain.',
        ]);

        // Memulai transaksi database untuk memastikan konsistensi data
        DB::beginTransaction();

        try {
            // 2. Buat User baru untuk porter
            // Email dibuat secara otomatis dari NRP untuk login. Anda bisa menyesuaikan domainnya.
            // Pastikan tidak ada user lain yang menggunakan email ini (NRP yang sama).
            $user = User::create([
                'name'     => $validatedData['porter_name'],
                'email'    => $validatedData['porter_nrp'] . '@porter.petra.ac.id', // Contoh email, bisa disesuaikan
                'password' => Hash::make('porter123'), // Atur password default untuk porter
            ]);

            // 3. Berikan role 'porter' kepada user baru
            // Pastikan Anda sudah memiliki role 'porter' di database.
            // Jika Anda menggunakan package Spatie/laravel-permission.
            $user->assignRole('porter');

            // 4. Buat Porter baru dan hubungkan dengan user_id yang baru dibuat
            $porter = new Porter();
            $porter->porter_name     = $validatedData['porter_name'];
            $porter->porter_nrp      = $validatedData['porter_nrp'];
            $porter->department_id   = $validatedData['department_id'];
            $porter->account_numbers = $validatedData['account_numbers'];
            $porter->bank_name       = $validatedData['bank_name'];
            // Username diisi sama dengan porter_name, sesuai validasi awal Anda
            $porter->username        = $validatedData['porter_name'];
            // Hubungkan porter dengan user
            $porter->user_id         = $user->id;
            $porter->save();

            // Commit transaksi jika semua proses berhasil
            DB::commit();

            return redirect()->route('dashboard.porters.index')->with('success', 'Porter dan Akun User berhasil ditambahkan.');
        } catch (Exception $e) {
            // Batalkan semua operasi jika terjadi error
            DB::rollBack();

            // Laporkan error untuk debugging (opsional, tapi sangat direkomendasikan)
            report($e);

            // Kembalikan ke halaman sebelumnya dengan pesan error dan input yang sudah diisi
            return back()->with('error', 'Gagal menambahkan porter. Terjadi kesalahan pada server.')->withInput();
        }
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
        ], [
            'porter_nrp.unique'  => 'NRP ini sudah digunakan oleh Porter lain.',
            'account_numbers.unique' => 'Nomor rekening ini sudah digunakan oleh Porter lain.',
        ]);

        $porter->update($validatedData);

        return redirect()->route('dashboard.porters.index')->with('success', 'Data porter berhasil diperbarui.');
    }

    /**
     * Menghapus data porter dari database.
     */
    public function destroy(Request $request, Porter $porter)
    {
        $request->validate(['deletion_reason' => 'required|string|min:10'], [
            'deletion_reason.required' => 'Alasan penonaktifan wajib diisi.',
            'deletion_reason.min' => 'Alasan harus minimal 10 karakter.',
        ]);

        $porter->deletion_reason = $request->input('deletion_reason');
        $porter->save();
        $porter->delete();

        return redirect()->route('dashboard.porters.index')->with('success', 'Porter berhasil dinonaktifkan.');
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
    public function trashed()
    {
        $trashedPorters = Porter::onlyTrashed()->latest('deleted_at')->paginate(10);
        return view('dashboard.porter.trashed', ['trashedPorters' => $trashedPorters]);
    }

    public function restore($id)
    {
        $porter = Porter::withTrashed()->findOrFail($id);

        $porter->deletion_reason = null;
        $porter->save();
        $porter->restore();

        return redirect()->route('dashboard.porters.trashed')->with('success', 'Porter berhasil dipulihkan.');
    }

    /**
     * Menampilkan semua review & rating untuk porter tertentu (beserta nama customer)
     */
    public function reviews($id)
    {
        // Ambil porter beserta relasi ratings → order → customer
        $porter = Porter::with(['ratings.order.customer'])->findOrFail($id);

        // Ambil semua review, urutkan terbaru
        $reviews = $porter->ratings->sortByDesc('id');

        // Kirim ke view, atau bisa juga return json jika mau dipakai AJAX
        return view('dashboard.porter.index', compact('porter', 'reviews'));
    }
}
