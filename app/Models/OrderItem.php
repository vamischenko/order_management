<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель позиции заказа (строка в таблице order_items).
 *
 * @property int    $id
 * @property int    $order_id
 * @property int    $product_id
 * @property int    $quantity
 * @property string $unit_price
 * @property string $total_price
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'quantity'    => 'integer',
        'unit_price'  => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Заказ, которому принадлежит данная позиция.
     *
     * @return BelongsTo<Order, OrderItem>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Товар в данной позиции заказа.
     *
     * @return BelongsTo<Product, OrderItem>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
