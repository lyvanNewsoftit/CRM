<?php
namespace App\Enum;

enum UserRole: string
{
    case USER = 'ROLE_USER';
    case ADMIN = 'ROLE_ADMIN';
    case SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    public static function getAvailableRoles(): array
    {
        return array_column(self::cases(), 'value');
    }
}
