<?php

namespace App\Models;

use App\Models\OrderHistoryItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'porter_id',
        'order_status_id',
        'shipping_cost',
        'grand_total',
    ];

    // Relasi ke order asli
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Relasi ke porter
    public function porter()
    {
        return $this->belongsTo(Porter::class);
    }

    // Relasi ke status
    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_id');
    }

    // Relasi ke item histori
    public function items()
    {
        return $this->hasMany(OrderHistoryItem::class);
    }
}
