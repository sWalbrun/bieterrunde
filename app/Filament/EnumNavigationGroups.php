<?php

namespace App\Filament;

use BenSampo\Enum\Enum;

/**
 * These groups represent the existing groups within the navigation bar.
 */
class EnumNavigationGroups extends Enum
{
    public const ADMINISTRATION = 'Administration';
    public const YOUR_OFFERS = 'Your offers';
}
