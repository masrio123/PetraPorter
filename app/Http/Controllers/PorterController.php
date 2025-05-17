<?php

namespace App\Http\Controllers;

use App\Models\Porter;
use Illuminate\Http\Request;
use App\Models\Department;

class PorterController extends Controller
{
    public function index()
    {
        $porters = Porter::all();
        return view('dashboard.porter.index', compact('porters'));
    }

    public function create()
    {
        $departments = Department::all();
        return view('dashboard.porter.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'porter_name' => 'required|string|max:255',
            'porter_nrp' => 'required|string|max:50|unique:porters,porter_nrp',
            'department_id' => 'required|integer',
            'porter_account_number' => 'required|string|max:100',
            'porter_rating' => 'nullable|numeric|min:0|max:5',
            'porter_isOnline' => 'required|boolean',
        ]);

        Porter::create($request->all());

        return redirect()->route('dashboard.porters.index')->with('success', 'Porter berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        // Ambil porter beserta field department_id
        $porter = Porter::select(
            'id',
            'porter_name',
            'porter_nrp',
            'department_id',
            'porter_account_number',
            'porter_rating',
            'porter_isOnline'
        )->findOrFail($id);

        $departments = Department::all();

        return view('dashboard.porter.edit', compact('porter', 'departments'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'porter_name' => 'required|string|max:255',
            'porter_nrp' => 'required|string|max:50|unique:porters,porter_nrp,' . $id,
            'department_id' => 'required|integer',
            'porter_account_number' => 'required|string|max:100',
            'porter_rating' => 'nullable|numeric|min:0|max:5',
            'porter_isOnline' => 'required|boolean',
        ]);

        $porter = Porter::findOrFail($id);
        $porter->update($request->all());

        return redirect()->route('dashboard.porters.index')->with('success', 'Porter berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $porter = Porter::findOrFail($id);
        $porter->delete();

        return redirect()->route('dashboard.porters.index')->with('success', 'Porter berhasil dihapus.');
    }
}
