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

class OrderService
{
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
