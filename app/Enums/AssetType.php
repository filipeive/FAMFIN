<?php

namespace App\Enums;

enum AssetType: string
{
    case Property = 'property';
    case Vehicle = 'vehicle';
    case Investment = 'investment';
    case Electronics = 'electronics';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Property => 'Imóvel',
            self::Vehicle => 'Veículo',
            self::Investment => 'Investimento',
            self::Electronics => 'Electrónicos',
            self::Other => 'Outro',
        };
    }
}
