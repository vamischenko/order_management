<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция: создание таблицы заказов (orders).
 *
 * Поля: id, customer_id (FK → customers, cascade delete), status (default 'new'),
 * total_amount (decimal 10,2, default 0), confirmed_at (nullable),
 * shipped_at (nullable), timestamps.
 * Индексы по status, customer_id и created_at для фильтрации.
 */
return new class extends Migration
{
    /**
     * Применяет миграцию: создаёт таблицу orders.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('new');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('customer_id');
            $table->index('created_at');
        });
    }

    /**
     * Откатывает миграцию: удаляет таблицу orders.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
