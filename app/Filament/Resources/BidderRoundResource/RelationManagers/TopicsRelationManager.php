<?php

namespace App\Filament\Resources\BidderRoundResource\RelationManagers;

use App\BidderRound\TopicService;
use App\Filament\Resources\TopicResource;
use App\Models\Topic;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TopicsRelationManager extends RelationManager
{
    protected static string $relationship = 'topics';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getPluralModelLabel(): string
    {
        return trans('Topics');
    }

    public static function getModelLabel(): string
    {
        return trans('Topic');
    }

    public function getPageClass(): string
    {
        return RelationManager::class;
    }

    public function form(Form $form): Form
    {
        return $form->schema(TopicResource::formSchema());
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make(Topic::COL_NAME)->translateLabel(),
                Tables\Columns\TextColumn::make('Current status')
                    ->translateLabel()
                    ->formatStateUsing(
                        fn (Topic $record) => $record->countOffersGivenPerRound().'/'.$record->countTotalOffersPerRound()
                    ),
                Tables\Columns\TextColumn::make(Topic::COL_TARGET_AMOUNT)
                    ->translateLabel()
                    ->formatStateUsing(
                        fn ($state) => number_format($state, 2, ',', '.').' €'
                    ),
                Tables\Columns\TextColumn::make(Topic::COL_ROUNDS)->translateLabel(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('Add all current members')
                    ->translateLabel()
                    ->icon('iconpark-treediagram-o')
                    ->tooltip(trans('If members have been added or removed, they are also linked or unlinked to this bidding round.'))
                    ->action(function (Topic $record) {
                        TopicService::syncTopicParticipants($record);

                        Notification::make('syncMembers')
                            ->title(trans('Synced successfully'))
                            ->success()
                            ->send();
                    }),
                Tables\Actions\ViewAction::make()->url(fn (Topic $record) => TopicResource::getUrl('edit', ['record' => $record])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
            ]);
    }
}
