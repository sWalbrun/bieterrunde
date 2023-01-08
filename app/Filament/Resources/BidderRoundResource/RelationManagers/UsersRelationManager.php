<?php

namespace App\Filament\Resources\BidderRoundResource\RelationManagers;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use App\BidderRound\BidderRoundService;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\User;
use App\Notifications\ReminderOfBidderRound;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UsersRelationManager extends RelationManager
{
    /**
     * @var BidderRound|null
     */
    public Model $ownerRecord;

    protected static string $relationship = 'users';

    protected static ?string $recordTitleAttribute = 'email';

    public static function getPluralRecordLabel(): string
    {
        return trans('Participants');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make(User::COL_EMAIL)
                    ->required()
                    ->maxLength(255)
                    ->disabled(),
                Forms\Components\TextInput::make(User::COL_NAME)
                    ->required()
                    ->maxLength(255)
                    ->disabled(),
                Forms\Components\KeyValue::make('offers')
                    ->keyLabel(trans('Round'))
                    ->valueLabel(trans('Offer'))
                    ->afterStateHydrated(
                        fn (
                            self                      $livewire,
                            Forms\Components\KeyValue $component,
                            User                      $record,
                        ) => $component->state(
                            BidderRoundService::getOffers($livewire->ownerRecord, $record)
                                ->map(fn (Offer|null $offer) => $offer?->amount)
                        )
                    )
                    ->afterStateUpdated(fn (
                        self                      $livewire,
                        Forms\Components\KeyValue $component,
                        User                      $record,
                        array                     $state,
                    ) => $livewire->updateOffers($state, $livewire, $record))
                    ->disableAddingRows()
                    ->disableDeletingRows(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make(User::COL_NAME)->searchable(),
                Tables\Columns\TextColumn::make(User::COL_EMAIL)->searchable(),
                Tables\Columns\BadgeColumn::make('Offers given')
                    ->translateLabel()
                    ->getStateUsing(
                        fn (User $record, self $livewire) => $record->offersForRound($livewire->ownerRecord)->count()
                    )
                    ->color(
                        fn (int $state, self $livewire) => $state === $livewire->ownerRecord->countOffers
                            ? 'success'
                            : 'secondary'
                    ),
                Tables\Columns\TextColumn::make('Round=Amount')
                    ->getStateUsing(
                        fn (User $record, self $livewire) => $record->offersAsStringFor($livewire->ownerRecord)
                    )
                    ->label(trans('Round=Amount'))
            ])
            ->filters([
                Filter::make('Offers given')
                    ->translateLabel()
                    ->form(
                        [
                            Forms\Components\Checkbox::make('onlyWithoutOffersGiven')
                                ->label(trans('Only without all offers given'))
                        ]
                    )->query(fn (array $data, Builder $query, self $livewire) => $query->when(
                        $data['onlyWithoutOffersGiven'],
                        fn (Builder $query) => $query->where(Offer::query()
                            ->where(Offer::COL_FK_BIDDER_ROUND, '=', $livewire->ownerRecord->id)
                            ->where(Offer::COL_FK_USER, '=', DB::raw('user.id'))
                            ->selectRaw('COUNT(*)'), '<', $livewire->ownerRecord->countOffers)
                    ))
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                FilamentExportBulkAction::make('Export'),
                Tables\Actions\BulkAction::make('RemindParticipants')
                    ->label(trans('Remind participants'))
                    ->icon('iconpark-remind-o')
                    ->action(
                        fn (Collection $records, self $livewire) => $records->each(
                            function (User $participant) use ($livewire) {
                                Log::info("Remind user ({$participant->email()}) about bidder round");
                                $participant->notify(new ReminderOfBidderRound($livewire->ownerRecord, $participant));
                                Log::info('User has been reminded');
                            }
                        )
                    ),
                Tables\Actions\DetachBulkAction::make(),
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    private function updateOffers(array $state, UsersRelationManager $livewire, User $record): void
    {
        collect($state)
            ->filter(fn (mixed $state) => isset($state))
            ->map(fn (string $amount) => floatval($amount))
            // Re-adjust potentially wrong order of offers
            ->sort()
            // Make sure the index will further match with the round
            ->values()
            ->mapWithKeys(fn (float $amount, int $round) => [$round + 1 => $amount])
            ->each(function (float $amount, int $round) use ($livewire, $record) {
                if ($amount === 0.0) {
                    // 0.0 gets treated as a special case -> Remove the offer to stay upward compatible
                    Offer::query()
                        ->where(Offer::COL_FK_BIDDER_ROUND, '=', $livewire->ownerRecord->id)
                        ->where(Offer::COL_FK_USER, '=', $record->id)
                        ->where(Offer::COL_ROUND, '=', $round)
                        ->delete();
                    return;
                }
                Offer::query()->updateOrCreate(
                    [
                        Offer::COL_FK_BIDDER_ROUND => $livewire->ownerRecord->id,
                        Offer::COL_FK_USER => $record->id,
                        Offer::COL_ROUND => $round,
                    ],
                    [
                        Offer::COL_AMOUNT => $amount,
                        Offer::COL_ROUND => $round,
                        Offer::COL_FK_BIDDER_ROUND => $livewire->ownerRecord->id,
                        Offer::COL_FK_USER => $record->id,
                    ]
                );
            });
    }
}
