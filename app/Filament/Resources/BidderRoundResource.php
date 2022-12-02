<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BidderRoundResource\Pages;
use App\Filament\Resources\BidderRoundResource\RelationManagers\BidderRoundReportRelationManager;
use App\Filament\Resources\BidderRoundResource\RelationManagers\UsersRelationManager;
use App\Models\BidderRound;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class BidderRoundResource extends Resource
{
    protected static ?string $model = BidderRound::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function getPluralLabel(): ?string
    {
        return trans('Bidder Rounds');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make(BidderRound::COL_VALID_FROM)->required()->label(trans('Valid from')),
                DatePicker::make(BidderRound::COL_VALID_TO)->required()->label(trans('Valid to')),
                DatePicker::make(BidderRound::COL_START_OF_SUBMISSION)->required()->label(trans('Start of submission')),
                DatePicker::make(BidderRound::COL_END_OF_SUBMISSION)->required()->label(trans('End of submission')),
                TextInput::make(BidderRound::COL_COUNT_OFFERS)->required()->label(trans('Count offers')),
                TextInput::make(BidderRound::COL_TARGET_AMOUNT)
                    ->numeric()
                    ->required()
                    ->mask(
                        fn (TextInput\Mask $mask) => $mask
                            ->numeric()
                            ->decimalPlaces(2)
                            ->decimalSeparator(',')
                            ->minValue(1)
                            ->maxValue(250_000)
                            ->normalizeZeros()
                            ->padFractionalZeros()
                            ->thousandsSeparator('.')
                    )->suffix('€')
                    ->label(trans('Target amount')),
                Card::make()->schema([
                    TextInput::make('offersGiven')
                        ->label(trans('Offers given'))
                        ->disabled()
                        ->afterStateHydrated(
                            function (TextInput $component, BidderRound|null $record) {
                                if (!isset($record) || $record->groupedByRound()->isEmpty()) {
                                    return;
                                }
                                $component->state(
                                    $record->groupedByRound()->first()->count() . '/' . $record->users()->count()
                                );
                            }
                        )
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make(BidderRound::COL_TARGET_AMOUNT)
                    ->formatStateUsing(
                        fn ($state) => number_format($state, 2, ',', '.') . ' €'
                    ),
                Tables\Columns\TextColumn::make(BidderRound::COL_START_OF_SUBMISSION)
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make(BidderRound::COL_END_OF_SUBMISSION)
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make(BidderRound::COL_VALID_FROM)
                    ->date('d.m.Y'),
                Tables\Columns\TextColumn::make(BidderRound::COL_VALID_TO)
                    ->date('d.m.Y'),
                Tables\Columns\TextColumn::make(BidderRound::COL_COUNT_OFFERS),
                Tables\Columns\TextColumn::make(BidderRound::COL_NOTE),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            UsersRelationManager::class,
            BidderRoundReportRelationManager::class,
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
