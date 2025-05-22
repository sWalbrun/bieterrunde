<?php

namespace App\Filament\Resources\BidderRoundResource\Pages;

use App\Filament\Resources\BidderRoundResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBidderRounds extends ListRecords
{
    protected static string $resource = BidderRoundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
