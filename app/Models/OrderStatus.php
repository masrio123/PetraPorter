<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    // Nama tabel jika beda dari 'order_statuses' (default plural dari model)
    // protected $table = 'order_statuses';

    // Kolom yang boleh diisi mass assignment
    protected $fillable = ['order_status'];

    // Kalau kamu nggak pakai timestamps created_at dan updated_at di tabel,
    // bisa nonaktifin dengan:
    // public $timestamps = false;

    // Relasi ke Order (jika kamu mau)
    public function orders()
    {
        return $this->hasMany(Order::class, 'order_status_id');
    }

    public function orderHistory()
    {
        return $this->belongsTo(OrderHistory::class);
    }
}
