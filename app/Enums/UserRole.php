<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Member = 'member';
    case Viewer = 'viewer';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Member => 'Membro',
            self::Viewer => 'Visualizador',
        };
    }
}
