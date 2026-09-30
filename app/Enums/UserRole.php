<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Analyst = 'analyst';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Analyst => 'Analista',
        };
    }
}