<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ресурс клиента для API-ответов.
 *
 * Преобразует модель Customer в массив с основными полями:
 * идентификатор, имя, email и номер телефона.
 *
 * @OA\Schema(
 *     schema="CustomerResource",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Иван Иванов"),
 *     @OA\Property(property="email", type="string", format="email", example="ivan@example.com"),
 *     @OA\Property(property="phone", type="string", example="+7 900 000-00-00"),
 * )
 */
class CustomerResource extends JsonResource
{
    /**
     * Преобразует ресурс в массив для JSON-ответа.
     *
     * @param  Request $request Входящий HTTP-запрос
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
        ];
    }
}
