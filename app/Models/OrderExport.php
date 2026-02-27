<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderExport extends Model
{
    protected $fillable = [
        'order_id',
        'status',
        'attempts',
        'last_error',
        'exported_at',
    ];

    protected $casts = [
        'attempts'    => 'integer',
        'exported_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
