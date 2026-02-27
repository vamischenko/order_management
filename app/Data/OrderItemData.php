<?php

namespace App\Data;

readonly class OrderItemData
{
    public function __construct(
        public int $productId,
        public int $quantity,
    ) {}
}
