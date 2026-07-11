<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * The admin-triggered actions on a bidder round that are worth remembering
 * (who did it, when) so several admins do not step on each other.
 */
enum EnumBidderRoundAction: string implements HasLabel
{
    case ANNOUNCED = 'announced';
    case REMINDED = 'reminded';

    public function getLabel(): string
    {
        return match ($this) {
            self::ANNOUNCED => trans('Start announced'),
            self::REMINDED => trans('Reminder sent'),
        };
    }
}
