<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Porter extends Model
{
    use HasFactory;

    protected $table = "porters";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'porter_name',
        'porter_nrp',
        'department_id',
        'bank_name',
        'username',
        'account_numbers', 
        'porter_isOnline',
        'isWorking',
        'timeout_until',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'porter_isOnline' => 'boolean',
        'isWorking' => 'boolean',
        'timeout_until' => 'datetime',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function ratings()
    {
        return $this->hasMany(PorterRating::class);
    }

    public function orders() {
        return $this->hasMany(Order::class);
    }
}