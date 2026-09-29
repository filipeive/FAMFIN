<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Scheduled = 'scheduled';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::Completed => 'Concluída',
            self::Scheduled => 'Agendada',
            self::Cancelled => 'Cancelada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Completed => 'green',
            self::Scheduled => 'blue',
            self::Cancelled => 'gray',
        };
    }
}
