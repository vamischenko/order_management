<?php

namespace App\Data;

/**
 * DTO позиции заказа.
 *
 * Передаёт данные об одном товаре и его количестве
 * при создании заказа через сервисный слой.
 */
readonly class OrderItemData
{
    /**
     * @param int $productId Идентификатор товара
     * @param int $quantity  Количество единиц товара
     */
    public function __construct(
        public int $productId,
        public int $quantity,
    ) {}
}
