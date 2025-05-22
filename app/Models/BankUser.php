<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankUser extends Model
{
    protected $fillable = ['username', 'account_number_id'];

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }
}
