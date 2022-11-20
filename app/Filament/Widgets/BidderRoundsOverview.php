<?php

namespace App\Filament\Widgets;

use App\Models\BidderRound;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class BidderRoundsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make(trans('Rounds carried out'), BidderRound::query()->count()),
        ];
    }
}
