<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tenant_location_id',
        'isOpen',
    ];

    protected $casts = [
        'isOpen' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function tenantLocation()
    {
        return $this->belongsTo(TenantLocation::class, 'tenant_location_id');
    }
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
}
