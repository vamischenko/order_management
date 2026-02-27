<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Основной сидер базы данных.
 *
 * Заполняет таблицы тестовыми данными: 50 товаров в наличии
 * и 10 клиентов. Используется для первоначального наполнения БД
 * командой php artisan db:seed.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Запускает сидеры для заполнения базы данных.
     *
     * Создаёт 50 товаров (все в наличии, inStock) и 10 клиентов.
     *
     * @return void
     */
    public function run(): void
    {
        Product::factory(50)->inStock()->create();

        Customer::factory(10)->create();
    }
}
