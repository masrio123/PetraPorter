<?php

namespace App\Models;

use App\Models\Tenant;
use App\Models\Category;
use App\Models\CartItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- 1. IMPORT SoftDeletes
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, SoftDeletes; // <-- 2. GUNAKAN TRAIT SoftDeletes

    protected $fillable = [
        'name', 
        'price', 
        'category_id', 
        'tenant_id', 
        'isAvailable'
    ];
    
    /**
     * Definisikan relasi Product ke Category.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Definisikan relasi Product ke Tenant.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Definisikan relasi Product ke CartItem.
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
}
