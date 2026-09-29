<?php

namespace App\Enums;

enum DebtStatus: string
{
    case Active = 'active';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Activa',
            self::Paid => 'Quitada',
            self::Overdue => 'Em Atraso',
            self::Cancelled => 'Cancelada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'blue',
            self::Paid => 'green',
            self::Overdue => 'red',
            self::Cancelled => 'gray',
        };
    }
}
