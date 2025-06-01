<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderHistoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_history_id',
        'customer_id',
        'user_id',
        'tenant_location_name',
        'tenant_name',
        'product_name',
        'quantity',
        'price',
        'total_price',
        'shipping_cost',
        'grand_total',
    ];

    // Relasi ke histori utama
    public function orderHistory()
    {
        return $this->belongsTo(OrderHistory::class);
    }

    // Relasi ke customer
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Relasi ke user yang mungkin melakukan transaksi (jika berbeda dari customer)
    public function user()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }
}
