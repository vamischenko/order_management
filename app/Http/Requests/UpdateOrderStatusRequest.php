<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Запрос на смену статуса заказа.
 *
 * Валидирует, что переданный статус является допустимым значением
 * перечисления OrderStatus.
 */
class UpdateOrderStatusRequest extends FormRequest
{
    /**
     * Определяет, авторизован ли пользователь для выполнения данного запроса.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Возвращает правила валидации входных данных.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(OrderStatus::class)],
        ];
    }

    /**
     * Возвращает кастомные сообщения об ошибках валидации на русском языке.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Поле status обязательно.',
            'status.*'        => 'Недопустимое значение статуса.',
        ];
    }
}
