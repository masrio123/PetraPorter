<?php

namespace App\Http\Controllers;

use App\Models\Porter;
use App\Models\Department;
use App\Models\BankUser;
use Illuminate\Http\Request;

class PorterController extends Controller
{
    // Tampilkan daftar porter
    public function index()
    {
        // Pastikan timeout_until di-cast ke Carbon di model Porter untuk format isFuture() (opsional)
        $porters = Porter::with(['department', 'bankUser.bank'])->get();

        return view('dashboard.porter.index', compact('porters'));
    }

    // Form tambah porter
    public function create()
    {
        $departments = Department::all();
        $bankUsers = BankUser::with('bank')->get();

        return view('dashboard.porter.create', compact('departments', 'bankUsers'));
    }

    // Simpan porter baru
    public function store(Request $request)
    {
        $request->validate([
            'porter_name' => 'required|string|max:255',
            'porter_nrp' => 'required|string|max:50|unique:porters,porter_nrp',
            'department_id' => 'required|integer|exists:departments,id',
            'bank_user_id' => 'required|integer|exists:bank_users,id',
            'porter_isOnline' => 'required|boolean',
        ]);

        $bankUser = BankUser::findOrFail($request->bank_user_id);

        // Validasi: nama harus sama dengan username rekening
        if ($request->porter_name !== $bankUser->username) {
            return back()
                ->withErrors(['porter_name' => 'Nama porter harus sama dengan username rekening yang dipilih.'])
                ->withInput();
        }

        // Validasi: bank_user_id tidak boleh digunakan oleh porter lain
        if (Porter::where('bank_user_id', $request->bank_user_id)->exists()) {
            return back()
                ->withErrors(['bank_user_id' => 'Rekening ini sudah digunakan oleh porter lain.'])
                ->withInput();
        }

        Porter::create([
            'porter_name' => $request->porter_name,
            'porter_nrp' => $request->porter_nrp,
            'department_id' => $request->department_id,
            'bank_user_id' => $request->bank_user_id,
            'porter_account_number' => $bankUser->account_number,
            'porter_isOnline' => $request->porter_isOnline,
        ]);

        return redirect()->route('dashboard.porters.index')->with('success', 'Porter berhasil ditambahkan.');
    }

    // Fungsi timeout (blacklist porter selama 2 hari)
    public function timeout(Porter $porter)
    {
        $porter->timeout_until = now()->addDays(2);
        $porter->save();

        return redirect()->route('dashboard.porters.index')->with('success', 'Porter telah di-timeout selama 2 hari.');
    }

    // Hapus porter
    public function destroy(Porter $porter)
    {
        $porter->delete();

        return redirect()->route('dashboard.porters.index')->with('success', 'Porter berhasil dihapus.');
    }
}
