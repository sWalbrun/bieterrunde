<?php

namespace App\Filament\Resources\TopicResource\RelationManagers;

use App\Models\TopicReport;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TopicReportRelationManager extends RelationManager
{
    public const INFORM_PARTICIPANTS = 'informParticipants';

    protected static string $relationship = 'topicReport';

    protected static ?string $recordTitleAttribute = 'roundWon';

    protected static function getPluralModelLabel(): string
    {
        return trans('Report');
    }

    public function getPageClass(): string
    {
        return RelationManager::class;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make(TopicReport::COL_ROUND_WON)
                    ->label(trans('Round with enough turnover'))
                    ->disabled(),
                Tables\Columns\BadgeColumn::make(TopicReport::COL_COUNT_PARTICIPANTS)
                    ->label(trans('Count shares'))
                    ->disabled(),
                Tables\Columns\BadgeColumn::make(TopicReport::COL_COUNT_ROUNDS)
                    ->label(trans('Number of configured rounds'))
                    ->disabled(),
                Tables\Columns\BadgeColumn::make(TopicReport::COL_SUM_AMOUNT)
                    ->label(trans('Sum'))
                    ->formatStateUsing(fn (float $state) => number_format($state, 2, ',', '.'))
                    ->suffix('€')
                    ->disabled(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make(self::INFORM_PARTICIPANTS)
                    ->label(trans('Inform participants'))
                    ->action(fn (TopicReport $record) => $record->notifyUsers())
                    ->icon('codicon-broadcast')
                    ->requiresConfirmation(),
            ]);
    }
}
