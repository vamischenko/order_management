<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ресурс заказа для API-ответов.
 *
 * Преобразует модель Order в массив: идентификатор, статус (строковое значение
 * enum), итоговая сумма, временные метки (ISO 8601), вложенные ресурсы клиента
 * и позиций заказа (подгружаются через eager loading).
 *
 * @OA\Schema(
 *     schema="OrderResource",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="status", type="string", example="new", enum={"new","confirmed","processing","shipped","completed","cancelled"}),
 *     @OA\Property(property="total_amount", type="number", format="float", example=3600.00),
 *     @OA\Property(property="confirmed_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="shipped_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="customer", ref="#/components/schemas/CustomerResource"),
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/OrderItemResource")),
 * )
 */
class OrderResource extends JsonResource
{
    /**
     * Преобразует ресурс в массив для JSON-ответа.
     *
     * Статус возвращается как строковое значение enum (->value).
     * Даты confirmed_at и shipped_at могут быть null.
     * Связи customer и items включаются только при наличии eager loading.
     *
     * @param  Request $request Входящий HTTP-запрос
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'status'       => $this->status->value,
            'total_amount' => (float) $this->total_amount,
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'shipped_at'   => $this->shipped_at?->toIso8601String(),
            'created_at'   => $this->created_at->toIso8601String(),
            'customer'     => new CustomerResource($this->whenLoaded('customer')),
            'items'        => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
