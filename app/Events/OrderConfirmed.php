<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие подтверждения заказа.
 *
 * Диспатчится в OrderService::changeStatus() при переходе заказа
 * в статус confirmed. Обрабатывается листенером DispatchOrderExport,
 * который инициирует экспорт данных во внешнюю систему.
 */
class OrderConfirmed
{
    use Dispatchable, SerializesModels;

    /**
     * @param Order $order Подтверждённый заказ
     */
    public function __construct(
        public readonly Order $order,
    ) {}
}
