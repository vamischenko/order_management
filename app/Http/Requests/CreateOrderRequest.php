<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Запрос на создание заказа.
 *
 * Валидирует наличие клиента (customer_id) и список позиций (items):
 * каждая позиция должна содержать существующий product_id и quantity >= 1.
 */
class CreateOrderRequest extends FormRequest
{
    /**
     * Определяет, авторизован ли пользователь для выполнения запроса.
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
            'customer_id'          => ['required', 'integer', 'exists:customers,id'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.product_id'   => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'     => ['required', 'integer', 'min:1'],
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
            'customer_id.required' => 'Поле customer_id обязательно.',
            'customer_id.exists'   => 'Клиент не найден.',
            'items.required'       => 'Список товаров обязателен.',
            'items.min'            => 'Заказ должен содержать хотя бы один товар.',
            'items.*.product_id.required' => 'ID товара обязателен.',
            'items.*.product_id.exists'   => 'Товар не найден.',
            'items.*.quantity.required'   => 'Количество обязательно.',
            'items.*.quantity.min'        => 'Количество должно быть не менее 1.',
        ];
    }
}
