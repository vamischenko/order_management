<?php

namespace App\Listeners;

use App\Events\OrderConfirmed;
use App\Jobs\ExportOrderJob;
use App\Models\OrderExport;

/**
 * Листенер события подтверждения заказа.
 *
 * Реагирует на OrderConfirmed: создаёт или сбрасывает запись экспорта
 * в таблице order_exports, затем помещает ExportOrderJob в очередь.
 */
class DispatchOrderExport
{
    /**
     * Обрабатывает событие подтверждения заказа.
     *
     * Создаёт запись OrderExport со статусом pending и диспатчит
     * ExportOrderJob в очередь exports.
     *
     * @param  OrderConfirmed $event Событие с подтверждённым заказом
     * @return void
     */
    public function handle(OrderConfirmed $event): void
    {
        OrderExport::updateOrCreate(
            ['order_id' => $event->order->id],
            ['status' => 'pending', 'attempts' => 0, 'last_error' => null, 'exported_at' => null],
        );

        ExportOrderJob::dispatch($event->order);
    }
}
