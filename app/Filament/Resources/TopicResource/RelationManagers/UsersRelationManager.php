<?php

namespace App\Filament\Resources\TopicResource\RelationManagers;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use App\BidderRound\TopicService;
use App\Enums\EnumContributionGroup;
use App\Enums\EnumPaymentInterval;
use App\Enums\ShareValue;
use App\Models\Offer;
use App\Models\Share;
use App\Models\Topic;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UsersRelationManager extends RelationManager
{
    /**
     * @var Topic|null
     */
    public Model $ownerRecord;

    protected static string $relationship = 'users';

    protected static ?string $recordTitleAttribute = 'email';

    public static function getPluralRecordLabel(): string
    {
        return trans('Participants');
    }

    public function getPageClass(): string
    {
        return RelationManager::class;
    }

    public function form(Form $form): Form
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
                Select::make(Share::COL_VALUE)
                    ->label(trans('Count shares'))
                    ->options(collect(ShareValue::getInstances())
                        ->mapWithKeys(
                            fn (ShareValue $value) => [$value->key => isset($value->value) ? trans($value->value) : null]
                        )
                    ),
                Forms\Components\Select::make(User::COL_CONTRIBUTION_GROUP)
                    ->label(trans('Contribution group'))
                    ->options(
                        collect(EnumContributionGroup::getInstances())
                            ->mapWithKeys(
                                fn (EnumContributionGroup $value) => [$value->key => isset($value->value) ? trans($value->value) : null]
                            )
                    )
                    ->disabled(),
                Forms\Components\Select::make(User::COL_PAYMENT_INTERVAL)
                    ->translateLabel()
                    ->options(
                        collect(EnumPaymentInterval::getInstances())
                            ->mapWithKeys(
                                fn (EnumPaymentInterval $value) => [$value->key => isset($value->value) ? trans($value->value) : null]
                            )
                    ),
                Forms\Components\KeyValue::make('offers')
                    ->keyLabel(trans('Round'))
                    ->valueLabel(trans('Offer'))
                    ->afterStateHydrated(
                        fn (
                            self $livewire,
                            Forms\Components\KeyValue $component,
                            User $record,
                        ) => $component->state(
                            TopicService::getOffers($livewire->ownerRecord, $record)
                                ->map(fn (?Offer $offer) => $offer?->amount)
                        )
                    )->columnSpan(2)
                    ->afterStateUpdated(fn (
                        self $livewire,
                        Forms\Components\KeyValue $component,
                        User $record,
                        array $state,
                    ) => $livewire->updateOffers($state, $livewire, $record))
                    ->disableAddingRows()
                    ->disableDeletingRows(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make(User::COL_NAME)->searchable(),
                Tables\Columns\TextColumn::make(User::COL_EMAIL)->searchable(),
                Tables\Columns\TextColumn::make(User::COL_CONTRIBUTION_GROUP)
                    ->translateLabel()
                    ->formatStateUsing(fn (?EnumContributionGroup $state) => isset($state) ? trans($state->value) : null),
                Tables\Columns\BadgeColumn::make(Share::COL_VALUE)->label(trans('Count shares'))->formatStateUsing(fn ($state) => isset($state) ? trans($state) : null),
                Tables\Columns\BadgeColumn::make('Offers given')
                    ->translateLabel()
                    ->getStateUsing(
                        fn (User $record, self $livewire) => $record->offersForTopic($livewire->ownerRecord)->count()
                    )
                    ->color(
                        fn (int $state, self $livewire) => $state === $livewire->ownerRecord->rounds
                            ? 'success'
                            : 'secondary'
                    ),
                Tables\Columns\TextColumn::make(User::COL_PAYMENT_INTERVAL)
                    ->translateLabel()
                    ->formatStateUsing(fn (?EnumPaymentInterval $state) => isset($state) ? trans($state->value) : null),
                Tables\Columns\TextColumn::make('Round=Amount')
                    ->getStateUsing(
                        fn (User $record, self $livewire) => $record->offersAsStringFor($livewire->ownerRecord)
                    )
                    ->label(trans('Round=Amount')),
            ])
            ->filters([
                Filter::make('Offers given')
                    ->translateLabel()
                    ->form(
                        [
                            Forms\Components\Checkbox::make('onlyWithoutOffersGiven')
                                ->label(trans('Only without all offers given')),
                        ]
                    )->query(fn (array $data, Builder $query, self $livewire) => $query->when(
                        $data['onlyWithoutOffersGiven'],
                        fn (Builder $query) => $query->where(
                            Offer::query()
                                ->where(Offer::COL_FK_TOPIC, '=', $livewire->ownerRecord->id)
                                ->where(Offer::COL_FK_USER, '=', DB::raw('user.id'))
                                ->selectRaw('COUNT(*)'),
                            '<',
                            $livewire->ownerRecord->rounds
                        )
                    )),
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
                        ->where(Offer::COL_FK_TOPIC, '=', $livewire->ownerRecord->id)
                        ->where(Offer::COL_FK_USER, '=', $record->id)
                        ->where(Offer::COL_ROUND, '=', $round)
                        ->delete();

                    return;
                }
                Offer::query()->updateOrCreate(
                    [
                        Offer::COL_FK_TOPIC => $livewire->ownerRecord->id,
                        Offer::COL_FK_USER => $record->id,
                        Offer::COL_ROUND => $round,
                    ],
                    [
                        Offer::COL_AMOUNT => $amount,
                        Offer::COL_ROUND => $round,
                        Offer::COL_FK_TOPIC => $livewire->ownerRecord->id,
                        Offer::COL_FK_USER => $record->id,
                    ]
                );
            });
    }
}
