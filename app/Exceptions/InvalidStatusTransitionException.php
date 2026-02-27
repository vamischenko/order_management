<?php

namespace App\Exceptions;

use App\Enums\OrderStatus;
use RuntimeException;

class InvalidStatusTransitionException extends RuntimeException
{
    public function __construct(OrderStatus $from, OrderStatus $to)
    {
        parent::__construct(
            "Невозможно изменить статус заказа с \"{$from->label()}\" на \"{$to->label()}\"."
        );
    }
}
