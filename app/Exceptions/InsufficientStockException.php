<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(string $productName, int $requested, int $available)
    {
        parent::__construct(
            "Недостаточно товара \"{$productName}\": запрошено {$requested}, доступно {$available}."
        );
    }
}
