<?php

namespace App\Enums;

enum FinancialRuleType: string
{
    case Inss = 'inss';
    case Tithe = 'tithe';
    case Yield = 'yield';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Inss => 'INSS',
            self::Tithe => 'Dízimo',
            self::Yield => 'Rendimento',
            self::Custom => 'Personalizada',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Inss => 'Contribuição obrigatória para a segurança social (3%)',
            self::Tithe => 'Contribuição religiosa sobre a receita',
            self::Yield => 'Rendimento sobre investimentos',
            self::Custom => 'Regra financeira personalizada',
        };
    }
}
