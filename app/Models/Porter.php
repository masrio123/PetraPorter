<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Porter extends Model
{
    protected $table = "porters";
    protected $fillable = [
    'porter_name',
    'porter_nrp',
    'department_id',
    'porter_account_number',
    'porter_isOnline',
];

    public function department()
{
    return $this->belongsTo(Department::class);
}
}
