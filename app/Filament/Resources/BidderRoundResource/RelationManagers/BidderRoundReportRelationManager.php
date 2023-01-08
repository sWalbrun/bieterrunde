<?php

namespace App\Filament\Resources\BidderRoundResource\RelationManagers;

use App\Models\BidderRoundReport;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

class BidderRoundReportRelationManager extends RelationManager
{
    protected static string $relationship = 'bidderRoundReport';

    protected static ?string $recordTitleAttribute = 'roundWon';

    protected static function getPluralModelLabel(): string
    {
        return trans('Bidder round report');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make(BidderRoundReport::COL_ROUND_WON)
                    ->label(trans('Round with enough turnover'))
                    ->disabled(),
                Tables\Columns\BadgeColumn::make(BidderRoundReport::COL_COUNT_PARTICIPANTS)
                    ->label(trans('Anzahl der Anteile'))
                    ->disabled(),
                Tables\Columns\BadgeColumn::make(BidderRoundReport::COL_COUNT_ROUNDS)
                    ->label(trans('Number of configured rounds'))
                    ->disabled(),
                Tables\Columns\BadgeColumn::make(BidderRoundReport::COL_SUM_AMOUNT)
                    ->label(trans('Sum'))
                    ->formatStateUsing(fn (float $state) => number_format($state, 2, ',', '.'))
                    ->suffix('€')
                    ->disabled(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('informParticipants')
                    ->label(trans('Inform participants'))
                    ->action(fn (BidderRoundReport $record) => $record->notifyUsers())
                    ->icon('codicon-broadcast'),
            ]);
    }
}
