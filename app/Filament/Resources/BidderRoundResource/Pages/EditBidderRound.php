<?php

namespace App\Filament\Resources\BidderRoundResource\Pages;

use App\Exceptions\OverlappingBidderRoundException;
use App\Filament\Resources\BidderRoundResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

use function trans;

class EditBidderRound extends EditRecord
{
    protected static string $resource = BidderRoundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @throws ValidationException
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            parent::handleRecordUpdate($record, $data);

            return $record;
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
