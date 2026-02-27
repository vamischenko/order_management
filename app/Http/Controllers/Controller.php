<?php

namespace App\Http\Controllers;

/**
 * Базовый абстрактный контроллер.
 *
 * Содержит глобальные OpenAPI-аннотации (Info, Server),
 * от которых наследуются все контроллеры API.
 *
 * @OA\Info(
 *     title="Order Management API",
 *     version="1.0.0",
 *     description="API сервиса управления заказами для интернет-магазина запчастей",
 *     @OA\Contact(email="admin@example.com")
 * )
 *
 * @OA\Server(
 *     url="/",
 *     description="API Server"
 * )
 */
abstract class Controller {}
