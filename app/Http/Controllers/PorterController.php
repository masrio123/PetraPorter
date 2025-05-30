<?php

namespace App\Http\Controllers;

use App\Models\Porter;
use App\Models\Department;
use App\Models\BankUser;
use Illuminate\Http\Request;

class PorterController extends Controller
{
    public function index()
    {
        $porters = Porter::with(['department', 'bankUser'])->get();
        return view('dashboard.porter.index', compact('porters'));
    }

    public function create()
    {
        $departments = Department::all();
        $bankUsers = BankUser::with('bank')->get(); // pastikan model dan relasi sudah benar

        return view('dashboard.porter.create', compact('departments', 'bankUsers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'porter_name' => 'required|string',
            'porter_nrp' => 'required|string',
            'department_id' => 'nullable|exists:departments,id',
            'bank_user_id' => 'required|exists:bank_users,id',
            'porter_isOnline' => 'required|boolean',
        ]);

        // Cek nama & NRP gaboleh duplikat
        $exists = Porter::where('porter_name', $request->porter_name)
            ->where('porter_nrp', $request->porter_nrp)
            ->exists();
        if ($exists) {
            return back()->withErrors(['porter_name' => 'Nama dan NRP sudah digunakan.'])
                ->withInput();
        }

        // Cek nama harus sama dengan username rekening bank yang dipilih
        $bankUser = BankUser::findOrFail($request->bank_user_id);
        if ($bankUser->username !== $request->porter_name) {
            return back()->withErrors(['porter_name' => 'Nama Porter harus sama dengan username pada rekening bank yang dipilih.'])
                ->withInput();
        }

        // Cek nomor rekening hanya boleh dipakai sekali
        $accountNumberInUse = Porter::where('bank_user_id', $request->bank_user_id)->exists();
        if ($accountNumberInUse) {
            return back()->withErrors(['bank_user_id' => 'Nomor rekening ini sudah digunakan oleh Porter lain.'])
                ->withInput();
        }

        Porter::create($request->all());

        return redirect()->route('dashboard.porters.index')->with('success', 'Porter berhasil ditambahkan.');
    }

    public function edit(Porter $porter)
    {
        $departments = Department::all();
        $bankUsers = BankUser::with('bank')->get(); // kirim juga data bankUsers ke view edit

        return view('dashboard.porter.edit', compact('porter', 'departments', 'bankUsers'));
    }

    public function update(Request $request, string $id)
    {
        $porter = Porter::findOrFail($id);

        if ($request->has('action')) {
            // Handle aksi timeout/cancel_timeout
                if ($request->action === 'timeout') {
                    $porter->timeout_until = now()->addDays(2);
                } elseif ($request->action === 'cancel_timeout') {
                    $porter->timeout_until = null;
                }
                $porter->save();

                return redirect()->route('dashboard.porters.index')->with('success', 'Status timeout berhasil diperbarui.');
        } else {
            // Validasi dasar
            $request->validate([
                'porter_name' => 'required|string',
                'porter_nrp' => 'required|string',
                'department_id' => 'nullable|exists:departments,id',
                'bank_user_id' => 'required|exists:bank_users,id',
                'porter_isOnline' => 'required|boolean',
            ]);

            // Validasi unik kombinasi nama + NRP kecuali data saat ini
            $exists = Porter::where('porter_name', $request->porter_name)
                ->where('porter_nrp', $request->porter_nrp)
                ->where('id', '!=', $porter->id)
                ->exists();
            if ($exists) {
                return back()->withErrors(['porter_name' => 'Nama dan NRP sudah digunakan oleh Porter lain.'])
                    ->withInput();
            }

            // Validasi nama harus sama dengan username rekening bank
            $bankUser = BankUser::findOrFail($request->bank_user_id);
            if ($bankUser->username !== $request->porter_name) {
                return back()->withErrors(['porter_name' => 'Nama Porter harus sama dengan username pada rekening bank yang dipilih.'])
                    ->withInput();
            }

            // Validasi nomor rekening hanya boleh dipakai 1x kecuali data ini sendiri
            $accountNumberInUse = Porter::where('bank_user_id', $request->bank_user_id)
                ->where('id', '!=', $porter->id)
                ->exists();
            if ($accountNumberInUse) {
                return back()->withErrors(['bank_user_id' => 'Nomor rekening ini sudah digunakan oleh Porter lain.'])
                    ->withInput();
            }

            $porter->update($request->only([
                'porter_name',
                'porter_nrp',
                'department_id',
                'bank_user_id',
                'porter_isOnline',
            ]));

            return redirect()->route('dashboard.porters.index')->with('success', 'Porter berhasil diperbarui.');
        }
    }


    public function destroy(Porter $porter)
    {
        $porter->delete();
        return redirect()->route('dashboard.porters.index')->with('success', 'Porter berhasil dihapus.');
    }
}
