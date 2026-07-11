<?php

namespace App\Filament\Resources\BidderRoundResource\Pages;

use App\Exceptions\OverlappingBidderRoundException;
use App\Filament\Resources\BidderRoundResource;
use App\Models\BidderRound;
use Filament\Actions\Action;
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
        /** @var BidderRound $record */
        $record = $this->record;

        return [
            Action::make('AnnounceStart')
                ->label(trans('Announce start'))
                ->icon('heroicon-o-megaphone')
                ->hidden(fn () => ! BidderRoundResource::canAnnounceStart($record))
                ->form(BidderRoundResource::announceStartForm())
                ->requiresConfirmation()
                ->modalSubheading(fn () => BidderRoundResource::announceModalSubheading($record))
                ->action(fn (array $data) => BidderRoundResource::announceStart($record, $data['message'] ?? null)),
            Action::make('RemindParticipants')
                ->label(trans('Remind participants'))
                ->icon('iconpark-remind-o')
                ->hidden(fn () => ! BidderRoundResource::canRemindParticipants($record))
                ->requiresConfirmation()
                ->modalSubheading(fn () => BidderRoundResource::remindModalSubheading($record))
                ->action(fn () => BidderRoundResource::remindParticipants($record)),
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
