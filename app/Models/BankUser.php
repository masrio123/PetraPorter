<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankUser extends Model
{
    protected $fillable = ['username', 'account_number', 'bank_id'];

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }
    // app/Models/BankUser.php

    public function porter()
    {
        return $this->hasOne(Porter::class); // sesuaikan namespace dan model Porter kamu
    }
}
