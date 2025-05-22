<?php

namespace App\Filament\Resources\BidderRoundResource\Pages;

use App\Filament\Resources\BidderRoundResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBidderRound extends CreateRecord
{
    protected static string $resource = BidderRoundResource::class;

    public static function canCreateAnother(): bool
    {
        return false;
    }
}
