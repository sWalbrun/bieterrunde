<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

class ShareValue extends Enum
{
    public const HALF = 'HALF';

    public const ONE = 'ONE';

    public const ONE_AND_A_HALF = 'ONE_AND_A_HALF';

    public const TWO = 'TWO';

    public function calculable(): float
    {
        return match ($this->value) {
            self::HALF => 0.5,
            self::ONE => 1,
            self::ONE_AND_A_HALF => 1.5,
            self::TWO => 2,
        };
    }
}
