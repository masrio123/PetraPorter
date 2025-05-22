<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $table = "banks";

    protected $guarded = [];

    public function bankUsers()
    {
        return $this->hasMany(BankUser::class, 'bank_name'); // kolom foreign key di account_numbers
    }
}
