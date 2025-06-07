<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'cart_id',
        'customer_id',
        'tenant_location_id',
        'order_status_id',
        'total_price',
        'shipping_cost',
        'grand_total',
    ];

    // Relasi ke OrderItem (satu order punya banyak order item)
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Relasi ke Customer
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    // Relasi ke Cart (opsional, kalau kamu ingin connect ke cart)
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    // Relasi ke OrderStatus
    public function status(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_id');
    }

    // Relasi ke TenantLocation
    public function tenantLocation(): BelongsTo
    {
        return $this->belongsTo(TenantLocation::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function porter()
    {
        return $this->belongsTo(Porter::class);
    }
}
