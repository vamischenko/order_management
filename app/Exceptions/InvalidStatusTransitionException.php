<?php

namespace App\Exceptions;

use App\Enums\OrderStatus;
use RuntimeException;

/**
 * Исключение о недопустимом переходе статуса заказа.
 *
 * Выбрасывается в OrderService::changeStatus(), когда запрошенный
 * переход не разрешён правилами OrderStatus::allowedTransitions().
 */
class InvalidStatusTransitionException extends RuntimeException
{
    /**
     * @param OrderStatus $from Текущий статус заказа
     * @param OrderStatus $to   Запрошенный целевой статус
     */
    public function __construct(OrderStatus $from, OrderStatus $to)
    {
        parent::__construct(
            "Невозможно изменить статус заказа с \"{$from->label()}\" на \"{$to->label()}\"."
        );
    }
}
