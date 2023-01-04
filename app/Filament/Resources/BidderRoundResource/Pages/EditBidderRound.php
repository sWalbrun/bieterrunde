<?php

namespace App\Filament\Resources\BidderRoundResource\Pages;

use App\Filament\Resources\BidderRoundResource;
use App\Models\BidderRound;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBidderRound extends EditRecord
{
    /** @var BidderRound */
    public $record;

    protected static string $resource = BidderRoundResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('calculateBidderRound')
                ->label(trans('Calculate bidder round'))
                ->action(fn () => $this->record->calculateBidderRound())
                ->icon('heroicon-o-calculator')
                ->disabled(fn () => $this->record->bidderRoundReport()->exists())
        ];
    }
}
