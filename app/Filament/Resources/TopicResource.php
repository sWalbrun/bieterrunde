<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TopicResource\Pages;
use App\Filament\Resources\TopicResource\RelationManagers\TopicReportRelationManager;
use App\Filament\Resources\TopicResource\RelationManagers\UsersRelationManager;
use App\Models\Topic;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;

class TopicResource extends Resource
{
    protected static ?string $model = Topic::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static bool $shouldRegisterNavigation = false;

    public static function getPluralModelLabel(): string
    {
        return trans('Topics');
    }

    public static function getModelLabel(): string
    {
        return trans('Topic');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(static::formSchema());
    }

    public static function getRelations(): array
    {
        return [
            UsersRelationManager::class,
            TopicReportRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTopics::route('/'),
            'create' => Pages\CreateTopic::route('/create'),
            'edit' => Pages\EditTopic::route('/{record}/edit'),
        ];
    }

    public static function formSchema(): array
    {
        return [
            TextInput::make(Topic::COL_NAME)->translateLabel(),
            TextInput::make(Topic::COL_ROUNDS)->numeric()->required()->minValue(1)->translateLabel(),
            TextInput::make(Topic::COL_TARGET_AMOUNT)
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
            TextInput::make('offersGiven')
                ->label(trans('Offers given'))
                ->disabled()
                ->hidden()
                ->afterStateHydrated(
                    function (TextInput $component, ?Topic $record) {
                        if (! isset($record)) {
                            return;
                        }
                        $component->hidden(false);
                        $component->state(
                            $record->countOffersGivenPerRound().'/'.$record->countTotalOffersPerRound()
                        );
                    }
                )
                ->reactive(),
        ];
    }
}
