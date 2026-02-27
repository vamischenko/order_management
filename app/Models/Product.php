<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Модель товара каталога.
 *
 * @property int    $id
 * @property string $name
 * @property string $sku            Уникальный артикул товара
 * @property string $price
 * @property int    $stock_quantity Текущий остаток на складе
 * @property string $category
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'price',
        'stock_quantity',
        'category',
    ];

    protected $casts = [
        'price'          => 'decimal:2',
        'stock_quantity' => 'integer',
    ];

    /**
     * Позиции заказов, в которых фигурирует данный товар.
     *
     * @return HasMany<OrderItem>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Скоуп: фильтрация товаров по категории.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string                                $category Название категории
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Скоуп: полнотекстовый поиск по названию и артикулу (SKU).
     *
     * Использует ILIKE для PostgreSQL и LIKE для остальных драйверов (SQLite в тестах).
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string                                $search Строка поиска
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, string $search)
    {
        $operator = config('database.default') === 'pgsql' ? 'ILIKE' : 'LIKE';

        return $query->where(function ($q) use ($search, $operator) {
            $q->where('name', $operator, "%{$search}%")
              ->orWhere('sku', $operator, "%{$search}%");
        });
    }

    /**
     * Скоуп: только товары с ненулевым остатком на складе.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }
}
