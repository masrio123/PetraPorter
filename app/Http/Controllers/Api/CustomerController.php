<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use App\Models\Department; // Department masih diperlukan

class CustomerController extends Controller
{
    /**
     * Tampilkan semua customer.
     */
    public function index()
    {
        // PERBAIKAN: Menghapus relasi 'bankUser' yang sudah tidak ada
        $customers = Customer::with('department')->get();
        return response()->json($customers);
    }

    /**
     * Simpan customer baru.
     */
    public function store(Request $request)
    {
        try {
            // PERBAIKAN: Validasi disesuaikan dengan field baru
            $validated = $request->validate([
                'customer_name' => 'required|string|max:255',
                'identity_number' => 'required|string|max:255|unique:customers,identity_number',
                'department_id' => 'required|exists:departments,id',
                'account_number' => 'required|string|max:50|unique:customers,account_number',
                'bank' => 'required|string|max:50',
                'username' => 'required|string|max:255',
            ]);

            // Validasi tambahan: Nama customer harus sama dengan nama pemilik rekening
            if ($request->customer_name !== $request->username) {
                // Manually create a validation exception to match the format
                throw ValidationException::withMessages([
                    'username' => ['Nama Customer harus sama dengan Nama Pemilik Rekening.'],
                ]);
            }

            $customer = Customer::create($validated);

            return response()->json([
                'message' => 'Customer berhasil ditambahkan.',
                'data' => $customer
            ], 201);
        } catch (ValidationException $e) {
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

    /**
     * Tampilkan detail customer berdasarkan user_id.
     */
    public function show($id)
    {
        // PERBAIKAN: Menghapus relasi 'bankUser'
        $customer = Customer::with('department')->where('user_id', $id)->first();

        if (!$customer) {
            return response()->json(['message' => 'Customer tidak ditemukan'], 404);
        }

        return response()->json($customer);
    }

    /**
     * Update customer.
     */
    public function update(Request $request, $id)
    {
        try {
            $customer = Customer::find($id);

            if (!$customer) {
                return response()->json(['message' => 'Customer tidak ditemukan'], 404);
            }

            // PERBAIKAN: Validasi disesuaikan untuk update
            $validated = $request->validate([
                'customer_name' => 'required|string|max:255',
                'identity_number' => 'required|string|max:255|unique:customers,identity_number,' . $customer->id,
                'department_id' => 'required|exists:departments,id',
                'account_number' => 'required|string|max:50|unique:customers,account_number,' . $customer->id,
                'bank' => 'required|string|max:50',
                'username' => 'required|string|max:255',
            ]);

            // Validasi tambahan: Nama customer harus sama dengan nama pemilik rekening
            if ($request->customer_name !== $request->username) {
                throw ValidationException::withMessages([
                    'username' => ['Nama Customer harus sama dengan Nama Pemilik Rekening.'],
                ]);
            }

            $customer->update($validated);

            return response()->json([
                'message' => 'Customer berhasil diperbarui.',
                'data' => $customer
            ]);
        } catch (ValidationException $e) {
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

    /**
     * Hapus customer.
     */
    public function destroy($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['message' => 'Customer tidak ditemukan'], 404);
        }

        // Anda mungkin ingin menambahkan validasi di sini untuk mencegah penghapusan
        // jika customer memiliki order aktif, mirip seperti di PorterController.

        $customer->delete();

        return response()->json(['message' => 'Customer berhasil dihapus.']);
    }

    /**
     * Update hanya data bank customer (Nama Bank, Nomor Rekening, Atas Nama).
     * Endpoint: PUT /customers/{id}/update-bank
     */
    public function updateBank(Request $request, $id)
    {
        try {
            // 1. Validasi input yang masuk (disesuaikan dengan kolom di tabel customer)
            $validated = $request->validate([
                'bank_name'           => 'required|string|max:50',
                'account_numbers' => 'required|string|max:50',
                'username'       => 'required|string|max:255', // Nama pemilik rekening
            ]);

            // 2. Cari data customer berdasarkan ID
            $customer = Customer::where('user_id', $id)->firstOrFail();

            // 3. Update field di model Customer
            $customer->bank_name           = $validated['bank_name'];
            $customer->account_numbers = $validated['account_numbers'];
            $customer->username       = $validated['username'];
            $customer->save();

            // 4. Kembalikan response sukses
            return response()->json([
                'success' => true,
                'message' => 'Data bank customer berhasil diperbarui.',
                'data'    => $customer // Mengembalikan data customer yang sudah diupdate
            ]);
        } catch (ValidationException $e) {
            // Menangani error validasi secara spesifik
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Menangani jika customer tidak ditemukan
            return response()->json([
                'success' => false,
                'message' => 'Data customer tidak ditemukan.',
            ], 404);
        } catch (\Exception $e) {
            // Menangani error umum lainnya
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data bank. Terjadi kesalahan pada server.',
            ], 500);
        }
    }
}
