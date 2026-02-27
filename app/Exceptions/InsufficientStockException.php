<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Исключение о недостаточном остатке товара на складе.
 *
 * Выбрасывается в OrderService при создании заказа,
 * если запрошенное количество превышает доступный остаток.
 */
class InsufficientStockException extends RuntimeException
{
    /**
     * @param string $productName Название товара с нехваткой остатка
     * @param int    $requested   Запрошенное количество
     * @param int    $available   Доступный остаток на складе
     */
    public function __construct(string $productName, int $requested, int $available)
    {
        parent::__construct(
            "Недостаточно товара \"{$productName}\": запрошено {$requested}, доступно {$available}."
        );
    }
}
