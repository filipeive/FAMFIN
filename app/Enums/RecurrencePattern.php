<?php

namespace App\Enums;

enum RecurrencePattern: string
{
    case Weekly = 'weekly';
    case Biweekly = 'biweekly';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case SemiAnnual = 'semi_annual';
    case Annual = 'annual';

    public function label(): string
    {
        return match ($this) {
            self::Weekly => 'Semanal',
            self::Biweekly => 'Quinzenal',
            self::Monthly => 'Mensal',
            self::Quarterly => 'Trimestral',
            self::SemiAnnual => 'Semestral',
            self::Annual => 'Anual',
        };
    }
}
