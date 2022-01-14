<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static ANNUAL()
 * @method static static SEMIANNUAL()
 * @method static static QUARTERLY()
 */
final class EnumPaymentInterval extends Enum
{
    const ANNUAL = 'ANNUAL';
    const SEMIANNUAL = 'SEMIANNUAL';
    const QUARTERLY = 'QUARTERLY';
}
