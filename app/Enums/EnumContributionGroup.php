<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static FULL_MEMBER()
 * @method static static SUSTAINING_MEMBER()
 */
final class EnumContributionGroup extends Enum
{
    /**
     * This member is getting vegetables since he is paying the full amount.
     */
    public const FULL_MEMBER = 'FULL_MEMBER';

    /**
     * This member is simply supporting the foundation.
     */
    public const SUSTAINING_MEMBER = 'SUSTAINING_MEMBER';
}
