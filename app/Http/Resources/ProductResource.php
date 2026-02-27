<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ресурс товара для API-ответов.
 *
 * Преобразует модель Product в массив: идентификатор, название,
 * артикул (SKU), цена (float), остаток на складе и категория.
 *
 * @OA\Schema(
 *     schema="ProductResource",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Масляный фильтр"),
 *     @OA\Property(property="sku", type="string", example="AB-1234-CD"),
 *     @OA\Property(property="price", type="number", format="float", example=450.00),
 *     @OA\Property(property="stock_quantity", type="integer", example=25),
 *     @OA\Property(property="category", type="string", example="Двигатель"),
 * )
 */
class ProductResource extends JsonResource
{
    /**
     * Преобразует ресурс в массив для JSON-ответа.
     *
     * Цена приводится к float, чтобы JSON-сериализатор не оборачивал
     * её в строку при использовании decimal cast.
     *
     * @param  Request $request Входящий HTTP-запрос
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'sku'            => $this->sku,
            'price'          => (float) $this->price,
            'stock_quantity' => $this->stock_quantity,
            'category'       => $this->category,
        ];
    }
}
