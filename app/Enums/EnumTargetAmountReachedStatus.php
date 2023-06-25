<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static SUCCESS()
 * @method static static ROUND_ALREADY_PROCESSED()
 * @method static static NOT_ALL_OFFERS_GIVEN()
 * @method static static NOT_ENOUGH_MONEY()
 */
class EnumTargetAmountReachedStatus extends Enum
{
    public const SUCCESS = 0;

    public const ROUND_ALREADY_PROCESSED = 2;

    public const NOT_ALL_OFFERS_GIVEN = 3;

    public const NOT_ENOUGH_MONEY = 4;

    public function isReportAvailable(): bool
    {
        return $this->is(self::SUCCESS())
            || $this->is(self::ROUND_ALREADY_PROCESSED());
    }
}
