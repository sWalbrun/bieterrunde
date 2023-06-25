<?php

namespace App\Filament\Resources\BidderRoundResource\Pages;

use App\Filament\Resources\BidderRoundResource;
use Filament\Resources\Pages\EditRecord;

class EditBidderRound extends EditRecord
{
    protected static string $resource = BidderRoundResource::class;

    protected function getActions(): array
    {
        return [
            // TODO delete bidder round and offers, share and topics cascading
        ];
    }
}
