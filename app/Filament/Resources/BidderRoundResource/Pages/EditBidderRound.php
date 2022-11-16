<?php

namespace App\Filament\Resources\BidderRoundResource\Pages;

use App\Filament\Resources\BidderRoundResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBidderRound extends EditRecord
{
    protected static string $resource = BidderRoundResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
