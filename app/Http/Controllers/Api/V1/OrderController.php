<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\CreateOrderData;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @OA\Tag(name="Orders", description="Управление заказами")
 */
class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    /**
     * @OA\Post(
     *     path="/api/v1/orders",
     *     summary="Создать заказ",
     *     tags={"Orders"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"customer_id","items"},
     *             @OA\Property(property="customer_id", type="integer", example=1),
     *             @OA\Property(property="items", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="product_id", type="integer", example=1),
     *                     @OA\Property(property="quantity", type="integer", example=2)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Заказ создан", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/OrderResource"))),
     *     @OA\Response(response=422, description="Ошибка валидации"),
     *     @OA\Response(response=429, description="Слишком много запросов"),
     * )
     */
    public function store(CreateOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->createOrder(
            CreateOrderData::fromRequest($request)
        );

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders",
     *     summary="Список заказов",
     *     tags={"Orders"},
     *     @OA\Parameter(name="status", in="query", description="Фильтр по статусу", required=false, @OA\Schema(type="string", enum={"new","confirmed","processing","shipped","completed","cancelled"})),
     *     @OA\Parameter(name="customer_id", in="query", description="Фильтр по клиенту", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="date_from", in="query", description="Начало диапазона дат (YYYY-MM-DD)", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", description="Конец диапазона дат (YYYY-MM-DD)", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Список заказов")
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Order::with(['customer', 'items.product'])
            ->when($request->input('status'), fn($q, $status) => $q->byStatus($status))
            ->when($request->input('customer_id'), fn($q, $id) => $q->byCustomer((int) $id))
            ->byDateRange($request->input('date_from'), $request->input('date_to'))
            ->latest();

        $orders = $query->paginate($request->integer('per_page', 15));

        return OrderResource::collection($orders);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders/{id}",
     *     summary="Детали заказа",
     *     tags={"Orders"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Данные заказа", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/OrderResource"))),
     *     @OA\Response(response=404, description="Заказ не найден"),
     * )
     */
    public function show(Order $order): OrderResource
    {
        $order->load(['customer', 'items.product']);

        return new OrderResource($order);
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/orders/{id}/status",
     *     summary="Изменить статус заказа",
     *     tags={"Orders"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"new","confirmed","processing","shipped","completed","cancelled"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="Статус изменён", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/OrderResource"))),
     *     @OA\Response(response=422, description="Недопустимый переход статуса"),
     *     @OA\Response(response=404, description="Заказ не найден"),
     * )
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): OrderResource
    {
        $newStatus = OrderStatus::from($request->input('status'));

        $order = $this->orderService->changeStatus($order, $newStatus);

        return new OrderResource($order);
    }
}
