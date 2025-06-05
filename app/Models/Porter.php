<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BankUser;

class Porter extends Model
{
    protected $table = "porters";
    // app/Models/Porter.php

    protected $fillable = [
        'porter_name',
        'porter_nrp',
        'department_id',       // wajib ada
        'bank_user_id',
        'porter_isOnline',
    ];


    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function bankUser()
    {
        return $this->belongsTo(BankUser::class, 'bank_user_id');
    }

    public function orderHistory()
    {
        return $this->belongsTo(OrderHistory::class);
    }

    public function ratings()
    {
        return $this->hasMany(PorterRating::class);
    }
}
