<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция: создание таблиц кеша.
 *
 * Создаёт таблицы cache и cache_locks, необходимые для работы
 * драйвера кеша database в Laravel.
 */
return new class extends Migration
{
    /**
     * Применяет миграцию: создаёт таблицы cache и cache_locks.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration')->index();
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration')->index();
        });
    }

    /**
     * Откатывает миграцию: удаляет таблицы cache и cache_locks.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
