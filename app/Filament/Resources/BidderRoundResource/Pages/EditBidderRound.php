<?php

namespace App\Filament\Resources\BidderRoundResource\Pages;

use App\Filament\Resources\BidderRoundResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBidderRound extends EditRecord
{
    protected static string $resource = BidderRoundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
