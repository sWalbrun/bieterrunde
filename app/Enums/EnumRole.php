<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EnumRole: string implements HasColor, HasLabel
{
    case MEMBER = 'member';
    case ADMIN = 'admin';
    case SUPER_ADMIN = 'super_admin';

    public function isAdmin(): bool
    {
        return $this !== self::MEMBER;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::MEMBER => 'gray',
            self::ADMIN => 'warning',
            self::SUPER_ADMIN => 'danger',
        };
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
