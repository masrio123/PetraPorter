<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PorterRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'porter_id',
        'order_id',
        'rating',
    ];

    // Relasi ke porter
    public function porter()
    {
        return $this->belongsTo(Porter::class);
    }

    // Relasi ke order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
