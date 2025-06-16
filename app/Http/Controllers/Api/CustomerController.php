<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    // Tampilkan semua customer
    public function index()
    {
        $customers = Customer::with(['department', 'bankUser'])->get();

        return response()->json($customers);
    }

    // Simpan customer baru
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'customer_name' => 'required|string|max:255',
                'identity_number'=>'required|string|max:255',
                'department_id' => 'required|exists:departments,id',
                'bank_user_id' => 'required|exists:bank_users,id',
            ]);

            $customer = Customer::create($validated);

            return response()->json([
                'message' => 'Customer berhasil ditambahkan.',
                'data' => $customer
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Kalau validasi gagal, kirim response dengan error detail validasi
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Kalau error lain (misal DB error), kasih info error supaya bisa debugging
            return response()->json([
                'message' => 'Terjadi kesalahan pada server',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // Tampilkan detail customer
    public function show($id)
    {
        $customer = Customer::with(['department', 'bankUser'])->where('user_id', $id)->first();

        if (!$customer) {
            return response()->json(['message' => 'Customer tidak ditemukan'], 404);
        }

        return response()->json($customer);
    }

    // Update customer
    public function update(Request $request, $id)
    {
        try {
            $customer = Customer::find($id);

            if (!$customer) {
                return response()->json(['message' => 'Customer tidak ditemukan'], 404);
            }

            $validated = $request->validate([
                'customer_name' => 'required|string|max:255',
                'identity_number'=>'required|string|max:255',
                'department_id' => 'required|exists:departments,id',
                'bank_user_id' => 'required|exists:bank_users,id',
            ]);

            $customer->update($validated);

            return response()->json([
                'message' => 'Customer berhasil diperbarui.',
                'data' => $customer
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // Hapus customer
    public function destroy($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['message' => 'Customer tidak ditemukan'], 404);
        }

        $customer->delete();

        return response()->json(['message' => 'Customer berhasil dihapus.']);
    }
}
