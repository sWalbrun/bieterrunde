<?php

namespace App\Filament\Resources\TopicResource\Pages;

use App\BidderRound\TopicService;
use App\Enums\EnumTargetAmountReachedStatus;
use App\Exceptions\NoRoundFoundException;
use App\Filament\Resources\TopicResource;
use App\Models\Topic;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTopic extends EditRecord
{
    public const CALCULATE_BIDDER_ROUND_ACTION = 'calculateBidderRound';

    protected static string $resource = TopicResource::class;

    private TopicService $bidderRoundService;

    /** @var Topic */
    public $record;

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->bidderRoundService = resolve(TopicService::class);
    }

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make(self::CALCULATE_BIDDER_ROUND_ACTION)
                ->label(trans('Calculate bidder round'))
                ->action(fn () => $this->calculateReport($this->record))
                ->icon('heroicon-o-calculator')
                ->disabled(fn () => $this->record->topicReport()->exists()),
        ];
    }

    /**
     * @throws NoRoundFoundException
     */
    private function calculateReport(Topic $topic)
    {
        $report = $this->bidderRoundService->calculateReportForTopic($topic);

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
                        ->title(trans('Leider konnte mit keiner einzigen Runde der Richtwert ermittelt werden.'))
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
