<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantLocation extends Model
{
    protected $table = "tenant_locations";
    protected $fillable = ['location_name'];

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }
}
