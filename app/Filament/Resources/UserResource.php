<?php

namespace App\Filament\Resources;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use App\Enums\EnumContributionGroup;
use App\Filament\EnumNavigationGroups;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function getPluralModelLabel(): string
    {
        return trans('Users');
    }

    public static function getModelLabel(): string
    {
        return trans('User');
    }

    protected static function getNavigationGroup(): ?string
    {
        return trans(EnumNavigationGroups::ADMINISTRATION);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make(User::COL_NAME)
                    ->label(trans('Name')),
                Forms\Components\TextInput::make(User::COL_EMAIL)
                    ->email()
                    ->label(trans('E-Mail')),
                Forms\Components\Select::make(User::COL_CONTRIBUTION_GROUP)
                    ->label(trans('Contribution group'))
                    ->options(collect(EnumContributionGroup::getInstances())->mapWithKeys(fn (EnumContributionGroup $value) => [$value->key => trans($value->value)])),
                Forms\Components\TextInput::make(User::COL_COUNT_SHARES)
                    ->label(trans('Count shares'))
                    ->integer()
                    ->gt(0),
                Forms\Components\DatePicker::make(User::COL_JOIN_DATE)
                    ->label(trans('Join date')),
                Forms\Components\DatePicker::make(User::COL_EXIT_DATE)
                    ->label(trans('Exit date')),
                Forms\Components\Select::make('roles')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->label(trans('Roles'))
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make(User::COL_NAME)
                    ->translateLabel()
                    ->copyable()
                    ->searchable(),
                Tables\Columns\TextColumn::make(User::COL_EMAIL)
                    ->translateLabel()
                    ->copyable()
                    ->searchable(),
                Tables\Columns\TextColumn::make(User::COL_CONTRIBUTION_GROUP)
                    ->translateLabel()
                    ->formatStateUsing(fn (EnumContributionGroup|null $state) => isset($state) ? trans($state->value) : null),
                Tables\Columns\BadgeColumn::make(User::COL_COUNT_SHARES)->label(trans('Count shares')),
                Tables\Columns\TextColumn::make(User::COL_JOIN_DATE)->label(trans('Join date'))
                    ->formatStateUsing(fn (Carbon|null $state) => $state?->format('d.m.Y')),
                Tables\Columns\TextColumn::make(User::COL_EXIT_DATE)->label(trans('Exit date'))
                    ->formatStateUsing(fn (Carbon|null $state) => $state?->format('d.m.Y')),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                FilamentExportBulkAction::make('Export'),
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
