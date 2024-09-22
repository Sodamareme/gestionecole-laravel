<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'Admin';
    case COACH = 'Coach';
    case CM = 'CM';
    case APPRENANT = 'Apprenant';
    case MANAGER = 'Manager';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}