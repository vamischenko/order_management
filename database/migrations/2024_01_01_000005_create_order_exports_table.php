<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция: создание таблицы экспорта заказов (order_exports).
 *
 * Отслеживает статус отправки каждого заказа во внешнюю систему.
 * Поля: id, order_id (FK → orders, уникальный, cascade delete),
 * status (pending | success | failed, default 'pending'),
 * attempts (счётчик попыток, default 0), last_error (текст ошибки, nullable),
 * exported_at (дата успешного экспорта, nullable), timestamps.
 * Индекс по status для выборки заданий в очереди.
 */
return new class extends Migration
{
    /**
     * Применяет миграцию: создаёт таблицу order_exports.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('order_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, success, failed
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('exported_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Откатывает миграцию: удаляет таблицу order_exports.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('order_exports');
    }
};
