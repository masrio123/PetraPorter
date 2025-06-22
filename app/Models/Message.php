<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Ini adalah versi bersih dari Model Message.
 * Tidak ada fungsi tambahan, hanya yang esensial.
 */
class Message extends Model
{
    use HasFactory;

    /**
     * Properti $guarded dengan array kosong adalah cara "keras"
     * untuk mengizinkan SEMUA kolom diisi. Ini akan mengesampingkan
     * semua masalah terkait $fillable untuk sementara waktu.
     * Jika ini berhasil, berarti masalahnya memang di mass assignment.
     */
    protected $guarded = [];

    /**
     * Relasi ke model Order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Relasi ke model Porter.
     */
    public function porter()
    {
        return $this->belongsTo(Porter::class, 'porter_id');
    }

    /**
     * Relasi ke model Customer.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    
    // PASTIKAN TIDAK ADA FUNGSI LAIN DI BAWAH INI
    // TERUTAMA FUNGSI BERNAMA boot() ATAU setMessageAttribute()
}
