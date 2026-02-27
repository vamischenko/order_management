<?php

namespace App\Providers;

use App\Events\OrderConfirmed;
use App\Listeners\DispatchOrderExport;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        JsonResource::withoutWrapping();

        RateLimiter::for('order-creation', function ($request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        Event::listen(OrderConfirmed::class, DispatchOrderExport::class);
    }
}
