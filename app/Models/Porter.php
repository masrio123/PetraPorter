<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Porter extends Model
{
    use HasFactory, SoftDeletes; // <-- 2. GUNAKAN TRAIT

    protected $fillable = [
        'porter_name',
        'porter_nrp',
        'department_id',
        'bank_name',
        'account_numbers',
        'username',
        'porter_isOnline',
        'isWorking',
        'timeout_until',
        'deletion_reason' // <-- 3. Tambahkan kolom baru ke fillable
    ];

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

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
