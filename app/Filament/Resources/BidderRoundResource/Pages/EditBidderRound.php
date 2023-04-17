<?php

namespace App\Filament\Resources\BidderRoundResource\Pages;

use App\BidderRound\BidderRoundService;
use App\BidderRound\EnumTargetAmountReachedStatus;
use App\Exceptions\NoRoundFoundException;
use App\Filament\Resources\BidderRoundResource;
use App\Models\BidderRound;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBidderRound extends EditRecord
{
    public const CALCULATE_BIDDER_ROUND_ACTION = 'calculateBidderRound';

    /**
     * @var BidderRound
     */
    public $record;

    protected static string $resource = BidderRoundResource::class;

    private BidderRoundService $bidderRoundService;

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->bidderRoundService = resolve(BidderRoundService::class);
    }

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make(self::CALCULATE_BIDDER_ROUND_ACTION)
                ->label(trans('Calculate bidder round'))
                ->action(fn () => $this->calculateBidderRound($this->record))
                ->icon('heroicon-o-calculator')
                ->disabled(fn () => $this->record->bidderRoundReport()->exists()),
        ];
    }

    /**
     * @throws NoRoundFoundException
     */
    private function calculateBidderRound(BidderRound $bidderRound)
    {
        $report = $this->bidderRoundService->calculateBidderRound($bidderRound);

        try {
            switch ($report->status->value) {
                case EnumTargetAmountReachedStatus::SUCCESS:
                    Notification::make()
                        ->title(trans('Es konnte eine Runde ermittelt werden!'))
                        ->body(trans("Bieterrunde {$report->roundWon()} mit dem Betrag {$report->sumAmountFormatted()}€ deckt die Kosten"))
                        ->success()
                        ->send();
                    break;

                case EnumTargetAmountReachedStatus::ROUND_ALREADY_PROCESSED:
                    Notification::make()
                        ->title(trans('Die Runde wurde bereits ermittelt!'))
                        ->body(trans("Bieterrunde {$report->roundWon()} mit dem Betrag {$report->sumAmountFormatted()}€ deckt die Kosten"))
                        ->success()
                        ->send();
                    break;

                case EnumTargetAmountReachedStatus::NOT_ALL_OFFERS_GIVEN:
                    Notification::make()
                        ->title(trans('Es wurden noch nicht alle Gebote abgegeben!'))
                        ->warning()
                        ->send();
                    break;

                case EnumTargetAmountReachedStatus::NOT_ENOUGH_MONEY:
                    Notification::make()
                        ->title(trans('Leider konnte mit keiner einzigen Runde der Zielbetrag ermittelt werden.'))
                        ->danger()
                        ->send();
                    break;
            }
        } catch (NoRoundFoundException) {
            Notification::make()
                ->title(trans('Es ist ein unerwarteter Fehler aufgetreten'))
                ->danger()
                ->send();
        }
    }
}
