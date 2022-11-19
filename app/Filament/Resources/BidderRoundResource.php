<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BidderRoundResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers\UsersRelationManager;
use App\Models\BidderRound;
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make(BidderRound::COL_VALID_FROM)->required(),
                DatePicker::make(BidderRound::COL_VALID_TO)->required(),
                DatePicker::make(BidderRound::COL_START_OF_SUBMISSION)->required(),
                DatePicker::make(BidderRound::COL_END_OF_SUBMISSION)->required(),
                TextInput::make(BidderRound::COL_TARGET_AMOUNT)
                    ->numeric()
                    ->required()
                    ->mask(
                        fn(TextInput\Mask $mask) => $mask
                            ->numeric()
                            ->decimalPlaces(2)
                            ->decimalSeparator(',')
                            ->minValue(1)
                            ->maxValue(150_000)
                            ->normalizeZeros()
                            ->padFractionalZeros()
                            ->thousandsSeparator('.')
                    )->suffix('€'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make(BidderRound::COL_TARGET_AMOUNT)
                    ->formatStateUsing(
                        fn($state) => number_format($state, 2, ',', '.') . ' €'
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
            ->filters([])->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('calculateBidderRound')
                    ->action(fn(BidderRound $bidderRound) => $bidderRound->calculateBidderRound())
                    ->icon('heroicon-o-calculator'),
            ])->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            UsersRelationManager::class,
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
