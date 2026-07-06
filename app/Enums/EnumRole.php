<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EnumRole: string implements HasLabel
{
    case MEMBER = 'member';
    case ADMIN = 'admin';
    case SUPER_ADMIN = 'super_admin';

    public function isAdmin(): bool
    {
        return $this !== self::MEMBER;
    }

    public function getLabel(): string
    {
        return trans(
            match ($this) {
                self::MEMBER => 'Member',
                self::ADMIN => 'Admin',
                self::SUPER_ADMIN => 'Super admin',
            }
        );
    }
}
