<?php

namespace App\Data;

use App\Http\Requests\CreateOrderRequest;

/**
 * DTO для создания заказа.
 *
 * Инкапсулирует входные данные (клиент + список позиций),
 * необходимые для передачи в OrderService::createOrder().
 */
readonly class CreateOrderData
{
    /**
     * @param int             $customerId Идентификатор клиента
     * @param OrderItemData[] $items      Список позиций заказа
     */
    public function __construct(
        public int $customerId,
        public array $items,
    ) {}

    /**
     * Создаёт DTO из валидированного HTTP-запроса.
     *
     * @param  CreateOrderRequest $request Валидированный запрос на создание заказа
     * @return self
     */
    public static function fromRequest(CreateOrderRequest $request): self
    {
        return new self(
            customerId: $request->integer('customer_id'),
            items: array_map(
                fn(array $item) => new OrderItemData(
                    productId: (int) $item['product_id'],
                    quantity: (int) $item['quantity'],
                ),
                $request->input('items'),
            ),
        );
    }
}
