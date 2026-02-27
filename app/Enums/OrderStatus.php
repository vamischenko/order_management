<?php

namespace App\Enums;

/**
 * Статусы заказа.
 *
 * Описывает жизненный цикл заказа и допустимые переходы:
 * new → confirmed → processing → shipped → completed
 * new → cancelled, confirmed → cancelled
 */
enum OrderStatus: string
{
    /** Новый заказ — только что создан */
    case New = 'new';

    /** Заказ подтверждён менеджером */
    case Confirmed = 'confirmed';

    /** Заказ передан в обработку (сборка, упаковка) */
    case Processing = 'processing';

    /** Заказ передан в доставку */
    case Shipped = 'shipped';

    /** Заказ успешно доставлен и закрыт */
    case Completed = 'completed';

    /** Заказ отменён */
    case Cancelled = 'cancelled';

    /**
     * Возвращает список допустимых статусов, в которые можно перейти из текущего.
     *
     * @return OrderStatus[]
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::New       => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::Processing, self::Cancelled],
            self::Processing => [self::Shipped],
            self::Shipped   => [self::Completed],
            default         => [],
        };
    }

    /**
     * Проверяет, допустим ли переход в указанный статус из текущего.
     *
     * @param  self $status Целевой статус
     * @return bool
     */
    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }

    /**
     * Возвращает человекочитаемое название статуса на русском языке.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::New        => 'Новый',
            self::Confirmed  => 'Подтверждён',
            self::Processing => 'В обработке',
            self::Shipped    => 'Отправлен',
            self::Completed  => 'Выполнен',
            self::Cancelled  => 'Отменён',
        };
    }
}
