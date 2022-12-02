<?php

namespace App\Filament\Resources\BidderRoundResource\RelationManagers;

use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    private function getOffers(User $record): array
    {
        // First we have to check for all offers, which have already been given
        $offers = $record->offersForRound(
            $this->ownerRecord
        )->get()->mapWithKeys(fn (Offer $offer) => [$offer->round => $offer->amountFormatted]);

        // Now we have to fill up the missing ones with null values, to disallow the admin to create offers which
        // are not matching with config of the bidder round created beforehand
        $startIndexOfMissingOffers = $offers->keys()->max() + 1 ?? 1;
        for ($i = $startIndexOfMissingOffers; $i <= $this->ownerRecord->countOffers; $i++) {
            $offers->put($i, null);
        }

        return $offers->sortKeys()->toArray();
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
                        ) => $component->state($livewire->getOffers($record))
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
                Tables\Columns\BadgeColumn::make('offersGiven')
                    ->getStateUsing(
                        fn (User $record, self $livewire) => $record->offersForRound($livewire->ownerRecord)->count()
                    )
                    ->color(
                        fn (int $state, self $livewire) => $state === $livewire->ownerRecord->countOffers
                            ? 'success'
                            : 'secondary'
                    )
            ])
            ->filters([
                Filter::make('offersGiven')
                    ->form(
                        [
                            Forms\Components\Checkbox::make('onlyWithoutOffersGiven')->label(trans('Only without all offers given'))
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
