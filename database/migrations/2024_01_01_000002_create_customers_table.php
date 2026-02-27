<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция: создание таблицы клиентов (customers).
 *
 * Поля: id, name, email (уникальный), phone (необязательный), timestamps.
 */
return new class extends Migration
{
    /**
     * Применяет миграцию: создаёт таблицу customers.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Откатывает миграцию: удаляет таблицу customers.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
