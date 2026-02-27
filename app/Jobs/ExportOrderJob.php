<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\OrderExport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Джоб экспорта заказа во внешнюю систему.
 *
 * Помещается в очередь exports при подтверждении заказа.
 * Отправляет данные заказа POST-запросом на URL из конфига services.export.url.
 * При неудаче выполняется до 3 попыток с интервалом 10 секунд.
 */
class ExportOrderJob implements ShouldQueue
{
    use Queueable;

    /** Максимальное количество попыток выполнения джоба */
    public int $tries = 3;

    /** Задержка в секундах перед повторной попыткой */
    public int $backoff = 10;

    /**
     * @param Order $order Заказ для экспорта
     */
    public function __construct(
        public readonly Order $order,
    ) {
        $this->onQueue('exports');
    }

    /**
     * Выполняет экспорт заказа во внешнюю систему.
     *
     * Формирует payload с данными заказа, клиента и позиций,
     * отправляет HTTP POST-запрос. При успехе обновляет OrderExport
     * (status=success), при неудаче — бросает исключение для повторной попытки.
     *
     * @return void
     * @throws \RuntimeException Если внешняя система вернула неуспешный ответ
     */
    public function handle(): void
    {
        $export = OrderExport::firstOrCreate(
            ['order_id' => $this->order->id],
            ['status' => 'pending', 'attempts' => 0],
        );

        $export->increment('attempts');

        $payload = [
            'order_id'     => $this->order->id,
            'status'       => $this->order->status->value,
            'total_amount' => $this->order->total_amount,
            'confirmed_at' => $this->order->confirmed_at?->toIso8601String(),
            'customer'     => [
                'id'    => $this->order->customer->id,
                'name'  => $this->order->customer->name,
                'email' => $this->order->customer->email,
            ],
            'items' => $this->order->items->map(fn($item) => [
                'product_id'  => $item->product_id,
                'product_sku' => $item->product->sku,
                'quantity'    => $item->quantity,
                'unit_price'  => $item->unit_price,
                'total_price' => $item->total_price,
            ])->toArray(),
        ];

        $response = Http::timeout(30)
            ->post(config('services.export.url'), $payload);

        if ($response->successful()) {
            $export->update([
                'status'      => 'success',
                'last_error'  => null,
                'exported_at' => now(),
            ]);
        } else {
            throw new \RuntimeException(
                "Export failed with status {$response->status()}: {$response->body()}"
            );
        }
    }

    /**
     * Вызывается после исчерпания всех попыток.
     *
     * Обновляет запись OrderExport, устанавливая статус failed
     * и сохраняя сообщение последней ошибки.
     *
     * @param  Throwable $exception Исключение последней неудачной попытки
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        OrderExport::updateOrCreate(
            ['order_id' => $this->order->id],
            [
                'status'     => 'failed',
                'last_error' => $exception->getMessage(),
            ],
        );
    }
}
