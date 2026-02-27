<?php

namespace App\Enums;

enum OrderStatus: string
{
    case New = 'new';
    case Confirmed = 'confirmed';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /**
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

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }

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
