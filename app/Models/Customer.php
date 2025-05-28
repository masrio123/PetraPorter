<?php

namespace App\Models;

use App\Models\Order;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $guarded = [];

    // Relasi ke Cart: 1 customer bisa punya banyak cart
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function bankUser()
    {
        return $this->belongsTo(BankUser::class);
    }
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
