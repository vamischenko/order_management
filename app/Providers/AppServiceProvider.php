<?php

namespace App\Providers;

use App\Events\OrderConfirmed;
use App\Listeners\DispatchOrderExport;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

/**
 * Основной провайдер приложения.
 *
 * Отвечает за начальную настройку: отключение оборачивания JSON-ресурсов,
 * регистрацию rate limiter для создания заказов и привязку событий к листенерам.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Регистрация сервисов в контейнере.
     *
     * @return void
     */
    public function register(): void {}

    /**
     * Инициализация сервисов после загрузки всех провайдеров.
     *
     * - Отключает обёртку data у JSON-ресурсов (withoutWrapping)
     * - Настраивает rate limiter order-creation: 10 запросов в минуту по IP
     * - Привязывает OrderConfirmed → DispatchOrderExport
     *
     * @return void
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();

        RateLimiter::for('order-creation', function ($request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        Event::listen(OrderConfirmed::class, DispatchOrderExport::class);
    }
}
