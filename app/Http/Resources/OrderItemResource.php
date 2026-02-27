<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="OrderItemResource",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="product_id", type="integer", example=3),
 *     @OA\Property(property="product_name", type="string", example="Тормозные колодки передние"),
 *     @OA\Property(property="product_sku", type="string", example="TK-5678-AB"),
 *     @OA\Property(property="quantity", type="integer", example=2),
 *     @OA\Property(property="unit_price", type="number", format="float", example=1200.00),
 *     @OA\Property(property="total_price", type="number", format="float", example=2400.00),
 * )
 */
class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'product_id'   => $this->product_id,
            'product_name' => $this->whenLoaded('product', fn() => $this->product->name),
            'product_sku'  => $this->whenLoaded('product', fn() => $this->product->sku),
            'quantity'     => $this->quantity,
            'unit_price'   => (float) $this->unit_price,
            'total_price'  => (float) $this->total_price,
        ];
    }
}
