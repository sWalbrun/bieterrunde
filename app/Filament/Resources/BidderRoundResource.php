<?php

namespace App\Filament\Resources;

use App\BidderRound\TargetAmountReachedReport;
use App\BidderRound\TopicService;
use App\Enums\EnumBidderRoundAction;
use App\Enums\EnumTargetAmountReachedStatus;
use App\Filament\EnumNavigationGroups;
use App\Filament\Resources\BidderRoundResource\Pages;
use App\Filament\Resources\BidderRoundResource\RelationManagers\CommentsRelationManager;
use App\Filament\Resources\BidderRoundResource\RelationManagers\TopicsRelationManager;
use App\Models\BidderRound;
use App\Models\BidderRoundActionLog;
use App\Models\Topic;
use App\Models\User;
use App\Notifications\BidderRoundStarted;
use App\Notifications\ReminderOfBidderRound;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;

class BidderRoundResource extends Resource
{
    protected static ?string $model = BidderRound::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-euro';

    public static function getPluralLabel(): ?string
    {
        return trans('Bidder Rounds');
    }

    public static function getModelLabel(): string
    {
        return trans('Bidder round');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans(EnumNavigationGroups::ADMINISTRATION);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make(BidderRound::COL_START_OF_SUBMISSION)->required()->label(trans('Start of submission')),
                DatePicker::make(BidderRound::COL_END_OF_SUBMISSION)->required()->label(trans('End of submission')),
                Textarea::make(BidderRound::COL_NOTE)->translateLabel()->columnSpan(2),
                Placeholder::make('offerSources')
                    ->label(trans('Offers member / admin'))
                    ->helperText(trans('Submitted by members / entered by admins'))
                    ->columnSpan(2)
                    // Only meaningful for an existing round (not while creating)
                    ->visible(fn (?BidderRound $record) => $record !== null)
                    ->content(function (?BidderRound $record) {
                        $counts = $record->offerSourceCounts();

                        return $counts['member'].' / '.$counts['admin'];
                    }),
                Placeholder::make('lastAnnouncement')
                    ->label(trans('Start announced'))
                    ->columnSpan(2)
                    ->visible(fn (?BidderRound $record) => $record !== null)
                    ->content(fn (?BidderRound $record) => self::describeLastAction($record, EnumBidderRoundAction::ANNOUNCED)
                        ?: trans('Not announced yet')),
                Placeholder::make('lastReminder')
                    ->label(trans('Reminder sent'))
                    ->columnSpan(2)
                    ->visible(fn (?BidderRound $record) => $record !== null)
                    ->content(fn (?BidderRound $record) => self::describeLastAction($record, EnumBidderRoundAction::REMINDED)
                        ?: trans('No reminder sent yet')),
                Card::make()->schema([
                    TextInput::make('Current status ')
                        ->translateLabel()
                        ->afterStateHydrated(
                            function (TextInput $component, ?BidderRound $record) {
                                $topics = $record?->topics()->with('topicReport');
                                $state = match (true) {
                                    $topics?->count() > $topics?->get()->map(fn (Topic $topic) => $topic->topicReport)->count() => trans('Die Beitragsrunde wurde erfolgreich abgeschlossen'),
                                    $record?->bidderRoundBetweenNow() => trans('Die Beitragsrunde läuft gerade'),
                                    $record?->startOfSubmission->gt(now()) => trans('Die Beitragsrunde hat noch nicht begonnen'),
                                    default => null,
                                };
                                if (isset($state)) {
                                    $component->state($state);
                                    $component->hidden(false);
                                }
                            }
                        )
                        ->disabled()
                        ->hidden()
                        ->reactive(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make(BidderRound::COL_START_OF_SUBMISSION)
                    ->date('d.m.Y')
                    ->sortable()
                    ->translateLabel(),
                Tables\Columns\TextColumn::make(BidderRound::COL_END_OF_SUBMISSION)
                    ->date('d.m.Y')
                    ->sortable()
                    ->translateLabel(),
                Tables\Columns\TextColumn::make(BidderRound::COL_NOTE)
                    ->translateLabel(),
                Tables\Columns\TextColumn::make('offerSources')
                    ->label(trans('Offers member / admin'))
                    ->badge()
                    ->tooltip(trans('Submitted by members / entered by admins'))
                    ->getStateUsing(function (BidderRound $record) {
                        $counts = $record->offerSourceCounts();

                        return $counts['member'].' / '.$counts['admin'];
                    }),
                Tables\Columns\IconColumn::make('announced')
                    ->label(trans('Start announced'))
                    ->boolean()
                    ->getStateUsing(fn (BidderRound $record) => $record->lastAction(EnumBidderRoundAction::ANNOUNCED) !== null)
                    ->tooltip(fn (BidderRound $record) => self::describeLastAction($record, EnumBidderRoundAction::ANNOUNCED) ?: null),
                Tables\Columns\IconColumn::make('reminded')
                    ->label(trans('Reminder sent'))
                    ->boolean()
                    ->getStateUsing(fn (BidderRound $record) => $record->lastAction(EnumBidderRoundAction::REMINDED) !== null)
                    ->tooltip(fn (BidderRound $record) => self::describeLastAction($record, EnumBidderRoundAction::REMINDED) ?: null),
            ])
            ->actions([
                Tables\Actions\Action::make('AnnounceStart')
                    ->label(trans('Announce start'))
                    ->icon('heroicon-o-megaphone')
                    ->hidden(fn (BidderRound $record) => ! self::canAnnounceStart($record))
                    ->form(self::announceStartForm())
                    ->requiresConfirmation()
                    ->modalSubheading(fn (BidderRound $record) => self::announceModalSubheading($record))
                    ->action(fn (BidderRound $record, array $data) => self::announceStart($record, $data['message'] ?? null)),
                Tables\Actions\Action::make('RemindParticipants')
                    ->label(trans('Remind participants'))
                    ->icon('iconpark-remind-o')
                    ->hidden(fn (BidderRound $record) => ! self::canRemindParticipants($record))
                    ->action(fn (BidderRound $record) => self::remindParticipants($record))
                    ->requiresConfirmation()
                    ->modalSubheading(fn (BidderRound $record) => self::remindModalSubheading($record)),
                Tables\Actions\Action::make('CalculateResults')
                    ->label(trans('Calculate results'))
                    ->icon('heroicon-o-calculator')
                    ->requiresConfirmation()
                    ->modalSubheading(fn () => trans('Determines for every topic without a result the round with enough turnover.'))
                    ->action(fn (BidderRound $record) => self::calculateResults($record)),
            ])
            ->filters([]);
    }

    public static function calculateResults(BidderRound $bidderRound): void
    {
        $reports = resolve(TopicService::class)->calculateReportsForRound($bidderRound);

        if ($reports->isEmpty()) {
            Notification::make()
                ->title(trans('All topics of this round already have a result.'))
                ->info()
                ->send();

            return;
        }

        $successCount = $reports
            ->filter(fn (TargetAmountReachedReport $report) => $report->status->is(EnumTargetAmountReachedStatus::SUCCESS()))
            ->count();
        $body = $reports
            ->map(fn (TargetAmountReachedReport $report, string $topicName) => match ($report->status->value) {
                EnumTargetAmountReachedStatus::SUCCESS => trans(
                    ':topic: round :round with :amount € covers the costs',
                    ['topic' => $topicName, 'round' => $report->roundWon(), 'amount' => $report->sumAmountFormatted()]
                ),
                EnumTargetAmountReachedStatus::NOT_ALL_OFFERS_GIVEN => trans(':topic: offers are still missing', ['topic' => $topicName]),
                EnumTargetAmountReachedStatus::NOT_ENOUGH_MONEY => trans(':topic: no round reaches the target amount', ['topic' => $topicName]),
                default => trans(':topic: already calculated', ['topic' => $topicName]),
            })
            ->implode("\n");

        $notification = Notification::make()
            ->title(trans(':success of :total topics got a result', ['success' => $successCount, 'total' => $reports->count()]))
            ->body($body);
        $successCount === $reports->count() ? $notification->success() : $notification->warning();
        $notification->send();
    }

    /**
     * The optional personal-message form shown before announcing a round start.
     * Shared by the table action and the edit page header action.
     *
     * @return array<int, Component>
     */
    public static function announceStartForm(): array
    {
        return [
            Textarea::make('message')
                ->label(trans('Personal message (optional)'))
                ->helperText(trans('Gets included in the mail to all participants.')),
        ];
    }

    /** Announcing (or reminding about) an already ended round makes no sense. */
    public static function canAnnounceStart(BidderRound $record): bool
    {
        return ! $record->endOfSubmission->endOfDay()->isPast();
    }

    public static function canRemindParticipants(BidderRound $record): bool
    {
        return self::canAnnounceStart($record);
    }

    /**
     * Notifies every participant that the round has started, each with a
     * personal magic login link (github issue #16), and remembers who did it.
     */
    public static function announceStart(BidderRound $record, ?string $message): void
    {
        $participants = $record->participants();
        $participants->each(
            fn (User $participant) => $participant->notify(
                new BidderRoundStarted($record, $participant, $message)
            )
        );

        $record->recordAction(EnumBidderRoundAction::ANNOUNCED, auth()->id(), $participants->count());

        Notification::make()
            ->title(trans(':count participants have been informed.', ['count' => $participants->count()]))
            ->success()
            ->send();
    }

    /**
     * Reminds every participant with missing offers, each with a personal magic
     * login link, and remembers who did it.
     */
    public static function remindParticipants(BidderRound $record): void
    {
        $participants = $record->usersWithMissingOffers();
        $participants->each(function (User $participant) use ($record) {
            Log::info("Remind user ({$participant->email()}) about bidder round");
            $participant->notify(new ReminderOfBidderRound($record, $participant));
            Log::info('User has been reminded');
        });

        $record->recordAction(EnumBidderRoundAction::REMINDED, auth()->id(), $participants->count());

        Notification::make()
            ->title(trans(':count participants have been reminded.', ['count' => $participants->count()]))
            ->success()
            ->send();
    }

    public static function announceModalSubheading(BidderRound $record): string
    {
        return trim(
            trans('Informs all participants by mail that the bidder round has started.')
            .' '.self::describeLastAction($record, EnumBidderRoundAction::ANNOUNCED)
        );
    }

    public static function remindModalSubheading(BidderRound $record): string
    {
        return trim(
            trans('Remind all participants with missing offers')
            .' '.self::describeLastAction($record, EnumBidderRoundAction::REMINDED)
        );
    }

    /**
     * A human sentence describing when and by whom an action was last taken on
     * the round, so admins do not accidentally repeat each other. Empty when
     * the action has not happened yet.
     */
    public static function describeLastAction(BidderRound $record, EnumBidderRoundAction $action): string
    {
        $log = $record->lastAction($action);
        if (! $log instanceof BidderRoundActionLog) {
            return '';
        }

        $by = $log->user?->name ?? trans('the system');

        return $action === EnumBidderRoundAction::ANNOUNCED
            ? trans('Already announced on :date by :by.', ['date' => $log->createdAt->format('d.m.Y H:i'), 'by' => $by])
            : trans('Last reminder sent on :date by :by.', ['date' => $log->createdAt->format('d.m.Y H:i'), 'by' => $by]);
    }

    public static function getRelations(): array
    {
        return [
            TopicsRelationManager::class,
            CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBidderRounds::route('/'),
            'create' => Pages\CreateBidderRound::route('/create'),
            'edit' => Pages\EditBidderRound::route('/{record}/edit'),
        ];
    }
}
