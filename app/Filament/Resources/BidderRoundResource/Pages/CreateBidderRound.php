<?php

namespace App\Filament\Resources\BidderRoundResource\Pages;

use App\Exceptions\OverlappingBidderRoundException;
use App\Filament\Resources\BidderRoundResource;
use App\Models\BidderRound;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

use function trans;

class CreateBidderRound extends CreateRecord
{
    protected static string $resource = BidderRoundResource::class;

    public static function canCreateAnother(): bool
    {
        return false;
    }

    /**
     * @throws ValidationException
     */
    protected function handleRecordCreation(array $data): BidderRound
    {
        try {
            return static::getModel()::create($data);
        } catch (OverlappingBidderRoundException) {
            Notification::make()
                ->title(trans('Overlapping Bidder Round'))
                ->body(trans('This bidder round overlaps with an existing one.'))
                ->danger()
                ->persistent()
                ->send();

            throw ValidationException::withMessages([
            ]);
        }
    }
}
