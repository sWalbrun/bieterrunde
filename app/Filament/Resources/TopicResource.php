<?php

namespace App\Filament\Resources;

use App\BidderRound\OfferService;
use App\Filament\Resources\TopicResource\Pages;
use App\Filament\Resources\TopicResource\RelationManagers\TopicReportRelationManager;
use App\Filament\Resources\TopicResource\RelationManagers\UsersRelationManager;
use App\Models\Topic;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;

class TopicResource extends Resource
{
    protected static ?string $model = Topic::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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

    /**
     * The fields needed to create a topic (also used by the bidder round wizard).
     */
    public static function createSchema(): array
    {
        return [
            TextInput::make(Topic::COL_NAME)->translateLabel(),
            TextInput::make(Topic::COL_ROUNDS)->numeric()->required()->minValue(1)->translateLabel(),
            TextInput::make(Topic::COL_TARGET_AMOUNT)
                ->required()
                ->mask(RawJs::make(
                    <<<'JS'
                    $money($input, ',', '.', 2);
                    JS
                ))
                // German notation with optional thousand separators, or a plain number
                ->regex('/^(\d{1,3}(\.\d{3})*|\d+)(,\d{1,2})?$|^\d+\.\d{1,2}$/')
                // The money mask emits the formatted german string ("68.000,00") — persist the float
                ->dehydrateStateUsing(fn ($state) => OfferService::parseGermanAmount((string) $state))
                ->label(trans('Target amount')),
        ];
    }

    public static function formSchema(): array
    {
        return [
            ...static::createSchema(),
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
