<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель записи экспорта заказа во внешнюю систему.
 *
 * Создаётся при переходе заказа в статус confirmed.
 * Отражает текущее состояние отправки данных: pending → success / failed.
 *
 * @property int                             $id
 * @property int                             $order_id
 * @property string                          $status      Статус экспорта: pending, success, failed
 * @property int                             $attempts    Количество выполненных попыток
 * @property string|null                     $last_error  Текст последней ошибки
 * @property \Illuminate\Support\Carbon|null $exported_at Дата и время успешного экспорта
 * @property \Illuminate\Support\Carbon      $created_at
 * @property \Illuminate\Support\Carbon      $updated_at
 */
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

    /**
     * Заказ, к которому относится данная запись экспорта.
     *
     * @return BelongsTo<Order, OrderExport>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
