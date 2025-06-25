<?php

namespace App\Models;

use App\Models\User;
use App\Models\Product;
use App\Models\CartItem;
use App\Models\TenantLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- 1. IMPORT SoftDeletes
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tenant extends Model
{
    use HasFactory, SoftDeletes; // <-- 2. GUNAKAN TRAIT SoftDeletes

    protected $fillable = [
        'name',
        'tenant_location_id',
        'isOpen',
    ];

    protected $casts = [
        'isOpen' => 'boolean',
    ];

    /**
     * Definisikan relasi Tenant ke Product.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Definisikan relasi Tenant ke TenantLocation.
     */
    public function tenantLocation()
    {
        return $this->belongsTo(TenantLocation::class, 'tenant_location_id');
    }

    /**
     * Definisikan relasi Tenant ke CartItem.
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Definisikan relasi Tenant ke User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 3. (PENTING) Menambahkan boot method untuk menangani event.
     * Ini akan memastikan produk ikut terhapus (soft delete) saat tenant dihapus,
     * dan ikut pulih saat tenant dipulihkan.
     */
    protected static function booted()
    {
        parent::booted();

        // Event yang berjalan SEBELUM tenant di-soft-delete
        static::deleting(function ($tenant) {
            // Kita loop setiap produk milik tenant ini dan menghapusnya juga (soft delete)
            $tenant->products()->each(function ($product) {
                $product->delete();
            });
        });

        // Event yang berjalan SEBELUM tenant di-restore
        static::restoring(function ($tenant) {
            // Kita cari produk yang sudah terhapus milik tenant ini dan memulihkannya
            $tenant->products()->withTrashed()->restore();
        });
    }
}
