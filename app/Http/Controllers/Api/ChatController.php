<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Order;
use App\Models\Porter;
use App\Models\Customer;
// Tidak perlu import Chat lagi
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function getMessages($orderId)
    {
        // Ambil semua pesan yang memiliki teks (bukan hanya record kosong)
        $messages = Message::where('order_id', $orderId)
                            ->whereNotNull('message') // Hanya ambil yang ada pesannya
                            ->with(['porter:id,porter_name', 'customer:id,customer_name'])
                            ->orderBy('created_at', 'asc')
                            ->get();
        
        return response()->json(['success' => true, 'data' => $messages]);
    }

    public function sendMessage(Request $request, $orderId)
    {
        $validated = $request->validate(['message' => 'required|string|max:1000']);

        $order = Order::with('status')->find($orderId);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order tidak ditemukan.'], 404);
        }

        $currentStatus = strtolower(optional($order->status)->order_status ?? '');

        if (!in_array($currentStatus, ['received', 'on-delivery'])) {
            return response()->json(['success' => false, 'message' => 'Chat tidak tersedia untuk order ini.'], 403);
        }
        
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Pengguna tidak terautentikasi.'], 401);
        }

        // --- INI SOLUSI UTAMA DENGAN MODEL YANG BENAR ---
        // Kita gunakan Model Message untuk membuat pesan baru yang sebenarnya.
        
        $message = new Message();
        $message->order_id = $orderId;
        $message->message  = $validated['message']; // Langsung masukkan isi pesan

        if ($user->hasRole('porter')) {
            $porterProfile = Porter::where('user_id', $user->id)->first();
            if (!$porterProfile) return response()->json(['success' => false, 'message' => 'Profil porter tidak ditemukan.'], 404);
            $message->porter_id = $porterProfile->id;

        } elseif ($user->hasRole('customer')) {
            $customerProfile = Customer::where('user_id', $user->id)->first();
            if (!$customerProfile) return response()->json(['success' => false, 'message' => 'Profil customer tidak ditemukan.'], 404);
            $message->customer_id = $customerProfile->id;
            
        } else {
            return response()->json(['success' => false, 'message' => 'Peran pengguna tidak diizinkan untuk chat.'], 403);
        }
        
        $message->save(); // Simpan pesan lengkap dengan isinya
        
        $message->load(['porter:id,porter_name', 'customer:id,customer_name']);

        return response()->json(['success' => true, 'message' => 'Pesan terkirim.', 'data' => $message], 201);
    }

    /**
     * FUNGSI BARU: Membersihkan semua pesan untuk suatu order.
     * Panggil fungsi ini dari controller lain (misal: PorterController) setelah order selesai.
     *
     * @param int $orderId ID dari order yang pesannya akan dihapus.
     * @return void
     */
    public static function cleanupMessages(int $orderId): void
    {
        try {
            // Hapus semua record di tabel 'messages' yang cocok dengan order_id
            Message::where('order_id', $orderId)->delete();
            
            // Opsional: bisa ditambahkan logging jika berhasil
            Log::info("Riwayat chat untuk order_id {$orderId} telah dibersihkan.");

        } catch (\Exception $e) {
            // Log error jika terjadi masalah saat menghapus
            Log::error("Gagal membersihkan riwayat chat untuk order_id {$orderId}: " . $e->getMessage());
        }
    }
}
