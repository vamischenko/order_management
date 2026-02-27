<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Модель заказа.
 *
 * @property int                             $id
 * @property int                             $customer_id
 * @property OrderStatus                     $status
 * @property string                          $total_amount
 * @property \Illuminate\Support\Carbon|null $confirmed_at
 * @property \Illuminate\Support\Carbon|null $shipped_at
 * @property \Illuminate\Support\Carbon      $created_at
 * @property \Illuminate\Support\Carbon      $updated_at
 */
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'status',
        'total_amount',
        'confirmed_at',
        'shipped_at',
    ];

    protected $casts = [
        'status'       => OrderStatus::class,
        'total_amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'shipped_at'   => 'datetime',
    ];

    /**
     * Клиент, оформивший заказ.
     *
     * @return BelongsTo<Customer, Order>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Позиции (товары) данного заказа.
     *
     * @return HasMany<OrderItem>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Запись экспорта заказа во внешнюю систему.
     *
     * @return HasOne<OrderExport>
     */
    public function export(): HasOne
    {
        return $this->hasOne(OrderExport::class);
    }

    /**
     * Скоуп: фильтрация заказов по статусу.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  OrderStatus|string                    $status Статус (enum или строковое значение)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, OrderStatus|string $status)
    {
        $value = $status instanceof OrderStatus ? $status->value : $status;
        return $query->where('status', $value);
    }

    /**
     * Скоуп: фильтрация заказов по клиенту.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  int                                   $customerId Идентификатор клиента
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Скоуп: фильтрация заказов по диапазону дат создания.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string|null                           $from  Начало диапазона (YYYY-MM-DD), null — без ограничения
     * @param  string|null                           $to    Конец диапазона (YYYY-MM-DD), null — без ограничения
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDateRange($query, ?string $from, ?string $to)
    {
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }
        return $query;
    }
}
