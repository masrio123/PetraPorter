<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderHistoryItem extends Model
{
    protected $fillable = [
        'order_history_id',
        'product_id',
        'quantity',
        'price',
        'total_price',
    ];

    public function orderHistory(): BelongsTo
    {
        return $this->belongsTo(OrderHistory::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
