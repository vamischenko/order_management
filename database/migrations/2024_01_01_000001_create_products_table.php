<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция: создание таблицы товаров (products).
 *
 * Поля: id, name, sku (уникальный артикул), price (decimal 10,2),
 * stock_quantity (остаток, default 0), category, timestamps.
 * Индексы по category и name для фильтрации и поиска.
 */
return new class extends Migration
{
    /**
     * Применяет миграцию: создаёт таблицу products.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->string('category');
            $table->timestamps();

            $table->index('category');
            $table->index('name');
        });
    }

    /**
     * Откатывает миграцию: удаляет таблицу products.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
