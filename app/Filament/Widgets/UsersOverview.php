<?php

namespace App\Filament\Widgets;

use App\Enums\EnumContributionGroup;
use App\Models\BidderRound;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class UsersOverview extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make(
                trans('Full users'),
                User::query()
                    ->where(User::COL_CONTRIBUTION_GROUP, '=', EnumContributionGroup::FULL_MEMBER)
                    ->count()),
            Card::make(
                trans('Sustaining users'),
                User::query()
                    ->where(User::COL_CONTRIBUTION_GROUP, '=', EnumContributionGroup::SUSTAINING_MEMBER)
                    ->count()),
            Card::make(trans('Rounds carried out'), BidderRound::query()->count()),
        ];
    }
}
