<?php

namespace App\Listeners;

use App\Events\OrderConfirmed;
use App\Jobs\ExportOrderJob;
use App\Models\OrderExport;

class DispatchOrderExport
{
    public function handle(OrderConfirmed $event): void
    {
        OrderExport::updateOrCreate(
            ['order_id' => $event->order->id],
            ['status' => 'pending', 'attempts' => 0, 'last_error' => null, 'exported_at' => null],
        );

        ExportOrderJob::dispatch($event->order);
    }
}
