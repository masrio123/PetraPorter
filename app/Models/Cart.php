<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'status',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class); // atau User::class kalau kamu pakai default Laravel auth
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    // Optional: hitung total harga dari semua item
    public function getTotalPriceAttribute()
    {
        return $this->items->sum('total_price');
    }
}
