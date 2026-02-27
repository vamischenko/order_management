<?php

namespace App\Data;

use App\Http\Requests\CreateOrderRequest;

readonly class CreateOrderData
{
    /**
     * @param OrderItemData[] $items
     */
    public function __construct(
        public int $customerId,
        public array $items,
    ) {}

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
