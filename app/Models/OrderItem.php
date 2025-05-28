<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'tenant_id',
        'quantity',
        'price',
        'subtotal',
    ];

    // Relasi ke Product
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Relasi ke Tenant
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    // Relasi ke Order
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
