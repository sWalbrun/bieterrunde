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
    public const ANNUAL = 'ANNUAL';
    public const SEMIANNUAL = 'SEMIANNUAL';
    public const MONTHLY = 'MONTHLY';
}
