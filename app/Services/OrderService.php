<?php

namespace App\Services;

use App\Data\CreateOrderData;
use App\Enums\OrderStatus;
use App\Events\OrderConfirmed;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidStatusTransitionException;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Сервис управления заказами.
 *
 * Содержит всю бизнес-логику: создание заказа (с проверкой остатков,
 * атомарным списанием stock и подсчётом суммы) и смену статуса
 * (с валидацией допустимых переходов и диспатчем событий).
 */
class OrderService
{
    /**
     * Создаёт новый заказ в рамках транзакции БД.
     *
     * Для каждой позиции: блокирует строку товара (lockForUpdate),
     * проверяет достаточность остатка, рассчитывает цены, декрементирует
     * stock_quantity. После успешного создания инвалидирует кеш товаров.
     *
     * @param  CreateOrderData            $data Данные для создания заказа
     * @return Order                            Созданный заказ с загруженными customer и items.product
     * @throws InsufficientStockException       Если остатка товара недостаточно
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Если клиент или товар не найдены
     */
    public function createOrder(CreateOrderData $data): Order
    {
        /** @var Customer $customer */
        $customer = Customer::findOrFail($data->customerId);

        return DB::transaction(function () use ($data, $customer) {
            $totalAmount = 0;
            $orderItemsData = [];

            foreach ($data->items as $itemData) {
                /** @var Product $product */
                $product = Product::lockForUpdate()->findOrFail($itemData->productId);

                if ($product->stock_quantity < $itemData->quantity) {
                    throw new InsufficientStockException(
                        $product->name,
                        $itemData->quantity,
                        $product->stock_quantity,
                    );
                }

                $unitPrice = $product->price;
                $totalPrice = bcmul((string) $unitPrice, (string) $itemData->quantity, 2);
                $totalAmount = bcadd($totalAmount, $totalPrice, 2);

                $product->decrement('stock_quantity', $itemData->quantity);

                $orderItemsData[] = [
                    'product_id'  => $product->id,
                    'quantity'    => $itemData->quantity,
                    'unit_price'  => $unitPrice,
                    'total_price' => $totalPrice,
                ];
            }

            $order = Order::create([
                'customer_id'  => $customer->id,
                'status'       => OrderStatus::New,
                'total_amount' => $totalAmount,
            ]);

            foreach ($orderItemsData as &$itemRow) {
                $itemRow['order_id'] = $order->id;
                $itemRow['created_at'] = now();
                $itemRow['updated_at'] = now();
            }

            $order->items()->insert($orderItemsData);

            $store = Cache::getStore();
            if ($store instanceof \Illuminate\Cache\TaggableStore) {
                Cache::tags(['products'])->flush();
            }

            return $order->load(['customer', 'items.product']);
        });
    }

    /**
     * Изменяет статус заказа с проверкой допустимости перехода.
     *
     * При переходе в confirmed устанавливает confirmed_at и диспатчит
     * событие OrderConfirmed. При переходе в shipped устанавливает shipped_at.
     *
     * @param  Order                             $order     Заказ, статус которого нужно изменить
     * @param  OrderStatus                       $newStatus Целевой статус
     * @return Order                                        Обновлённый заказ с загруженными связями
     * @throws InvalidStatusTransitionException            Если переход из текущего статуса в новый недопустим
     */
    public function changeStatus(Order $order, OrderStatus $newStatus): Order
    {
        $currentStatus = $order->status;

        if (!$currentStatus->canTransitionTo($newStatus)) {
            throw new InvalidStatusTransitionException($currentStatus, $newStatus);
        }

        $attributes = ['status' => $newStatus];

        if ($newStatus === OrderStatus::Confirmed) {
            $attributes['confirmed_at'] = now();
        } elseif ($newStatus === OrderStatus::Shipped) {
            $attributes['shipped_at'] = now();
        }

        $order->update($attributes);

        if ($newStatus === OrderStatus::Confirmed) {
            event(new OrderConfirmed($order));
        }

        return $order->load(['customer', 'items.product']);
    }
}
