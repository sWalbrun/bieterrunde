<?php

namespace App\Filament\Resources\BidderRoundResource\RelationManagers;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use App\Models\BidderRoundComment;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Read-only view of the feedback members left while making their offers
 * (github issue #12). Comments are exportable.
 */
class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $recordTitleAttribute = BidderRoundComment::COL_COMMENT;

    public static function getPluralModelLabel(): string
    {
        return trans('Comments');
    }

    public static function getModelLabel(): string
    {
        return trans('Comment');
    }

    public function getPageClass(): string
    {
        return RelationManager::class;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(trans('Name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label(trans('E-Mail'))
                    ->searchable(),
                Tables\Columns\TextColumn::make(BidderRoundComment::COL_COMMENT)
                    ->label(trans('Comment'))
                    ->wrap(),
                Tables\Columns\TextColumn::make(BidderRoundComment::COL_UPDATED_AT)
                    ->label(trans('Updated at'))
                    ->dateTime('d.m.Y H:i'),
            ])
            ->bulkActions([
                FilamentExportBulkAction::make('Export'),
            ]);
    }
}
