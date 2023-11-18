<?php

namespace App\Filament\Resources;

use App\Filament\EnumNavigationGroups;
use App\Filament\Resources\BidderRoundResource\Pages;
use App\Filament\Resources\BidderRoundResource\RelationManagers\TopicsRelationManager;
use App\Models\BidderRound;
use App\Models\Topic;
use App\Models\User;
use App\Notifications\ReminderOfBidderRound;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
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

    protected static function getNavigationGroup(): ?string
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
                Card::make()->schema([
                    TextInput::make('Current status ')
                        ->translateLabel()
                        ->afterStateHydrated(
                            function (TextInput $component, ?BidderRound $record) {
                                $topics = $record?->topics()->with('topicReport');
                                $state = match (true) {
                                    $topics?->count() > $topics?->get()->map(fn (Topic $topic) => $topic->topicReport)->count() => trans('Die Bieterrunde wurde erfolgreich abgeschlossen'),
                                    $record?->bidderRoundBetweenNow() => trans('Die Bieterrunde läuft gerade'),
                                    $record?->startOfSubmission->gt(now()) => trans('Die Bieterrunde hat noch nicht begonnen'),
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
            ])
            ->actions([Tables\Actions\Action::make('RemindParticipants')
                ->label(trans('Remind participants'))
                ->icon('iconpark-remind-o')
                ->action(
                    fn (BidderRound $record) => $record->usersWithMissingOffers()->each(
                        function (User $participant) use ($record) {
                            Log::info("Remind user ({$participant->email()}) about bidder round");
                            $participant->notify(new ReminderOfBidderRound($record, $participant));
                            Log::info('User has been reminded');
                        }
                    )
                )
                ->requiresConfirmation()
                ->modalSubheading(fn () => trans('Remind all participants with missing offers')),
            ])
            ->filters([]);
    }

    public static function getRelations(): array
    {
        return [
            TopicsRelationManager::class,
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
