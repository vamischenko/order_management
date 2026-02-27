<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция: создание таблицы позиций заказа (order_items).
 *
 * Поля: id, order_id (FK → orders, cascade delete),
 * product_id (FK → products, restrict delete — товар нельзя удалить,
 * пока он есть в заказах), quantity, unit_price (decimal 10,2),
 * total_price (decimal 10,2), timestamps.
 * Индексы по order_id и product_id.
 */
return new class extends Migration
{
    /**
     * Применяет миграцию: создаёт таблицу order_items.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();

            $table->index('order_id');
            $table->index('product_id');
        });
    }

    /**
     * Откатывает миграцию: удаляет таблицу order_items.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
