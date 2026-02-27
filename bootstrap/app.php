<?php

use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidStatusTransitionException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->throttleWithRedis();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (InsufficientStockException $e, Request $request): JsonResponse {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        });

        $exceptions->render(function (InvalidStatusTransitionException $e, Request $request): JsonResponse {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request): JsonResponse {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Ресурс не найден.',
                ], 404);
            }

            return response()->json(['message' => 'Not Found.'], 404);
        });
    })->create();
