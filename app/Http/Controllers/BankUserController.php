<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\BankUser;
use Illuminate\Http\Request;

class BankUserController extends Controller
{
    public function index()
    {
        // Ambil data bank_users beserta relasi bank
        $bank_users = BankUser::with('bank')->get();
        return view('dashboard.bank-user.index', compact('bank_users'));
    }

    public function create()
    {
        $banks = Bank::all();
        return view('dashboard.bank-user.create', compact('banks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'account_number' => 'required|string|max:255|unique:bank_users,account_number',
            'bank_id' => 'required|exists:banks,id',
        ]);

        BankUser::create($request->only('username', 'account_number', 'bank_id'));

        return redirect()->route('dashboard.bank-users.index')->with('success', 'Data bank user berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $bank_user = BankUser::findOrFail($id);
        $banks = Bank::all();
        return view('dashboard.bank-user.edit', compact('bank_user', 'banks'));
    }

    public function update(Request $request, $id)
    {
        $bank_user = BankUser::findOrFail($id);

        $request->validate([
            'username' => 'required|string|max:255',
            'account_number' => 'required|string|max:255|unique:bank_users,account_number,' . $bank_user->id,
            'bank_id' => 'required|exists:banks,id',
        ]);

        $bank_user->update($request->only('username', 'account_number', 'bank_id'));

        return redirect()->route('dashboard.bank-users.index')->with('success', 'Data bank user berhasil diperbarui.');
    }

    public function destroy(BankUser $bankUser)
    {
        // Pastikan relasi 'porter' sudah ada di model BankUser
        if ($bankUser->porter()->exists()) {
            return redirect()->route('dashboard.bank-users.index')
                ->with('error', 'Bank user tidak dapat dihapus karena masih menjadi porter.');
        }

    $bankUser->delete();

        return redirect()->route('dashboard.bank-users.index')
            ->with('success', 'Bank user berhasil dihapus.');
    }
}
