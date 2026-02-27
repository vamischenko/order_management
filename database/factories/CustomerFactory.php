<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Фабрика для генерации тестовых данных клиентов.
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * Возвращает набор атрибутов по умолчанию для модели клиента.
     *
     * Генерирует случайное имя, уникальный email и номер телефона.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
        ];
    }
}
