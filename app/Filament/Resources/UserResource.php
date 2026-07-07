<?php

namespace App\Filament\Resources;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use App\Enums\EnumContributionGroup;
use App\Enums\EnumPaymentInterval;
use App\Enums\EnumRole;
use App\Filament\EnumNavigationGroups;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Utils\ForFilamentTranslator;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    /**
     * Only super admins may hand out the super_admin role — otherwise an admin
     * could escalate their own (or someone else's) privileges.
     *
     * @return Collection<string, string> value => label
     */
    private static function assignableRoleOptions(): Collection
    {
        return collect(EnumRole::cases())
            ->reject(fn (EnumRole $role) => $role === EnumRole::SUPER_ADMIN && ! static::actingAsSuperAdmin())
            ->mapWithKeys(fn (EnumRole $role) => [$role->value => $role->getLabel()]);
    }

    private static function actingAsSuperAdmin(): bool
    {
        return auth()->user()?->role === EnumRole::SUPER_ADMIN;
    }

    /**
     * The role values the current user is allowed to assign.
     *
     * @return string[]
     */
    public static function getAssignableRoleValues(): array
    {
        return static::assignableRoleOptions()->keys()->all();
    }

    /**
     * Non super admins must not touch super admin accounts (neither to edit
     * their role down nor to delete them).
     */
    private static function canManage(Model $record): bool
    {
        return static::actingAsSuperAdmin() || $record->role !== EnumRole::SUPER_ADMIN;
    }

    public static function canEdit(Model $record): bool
    {
        return static::canManage($record);
    }

    public static function canDelete(Model $record): bool
    {
        return static::canManage($record);
    }

    public static function getPluralModelLabel(): string
    {
        return trans('Users');
    }

    public static function getModelLabel(): string
    {
        return trans('User');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans(EnumNavigationGroups::ADMINISTRATION);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make(User::COL_NAME)
                    ->label(trans('Name'))
                    ->required(),
                Forms\Components\TextInput::make(User::COL_EMAIL)
                    ->email()
                    ->required()
                    // Globally unique across all tenants — required by the magic-link login
                    ->unique(table: User::TABLE, ignoreRecord: true)
                    ->label(trans('E-Mail')),
                Forms\Components\Select::make(User::COL_CONTRIBUTION_GROUP)
                    ->label(trans('Contribution group'))
                    ->required()
                    ->options(ForFilamentTranslator::enum(EnumContributionGroup::getInstances())),
                Forms\Components\Select::make(User::COL_PAYMENT_INTERVAL)
                    ->translateLabel()
                    ->options(ForFilamentTranslator::enum(EnumPaymentInterval::getInstances())),
                Forms\Components\DatePicker::make(User::COL_JOIN_DATE)
                    ->label(trans('Join date')),
                Forms\Components\DatePicker::make(User::COL_EXIT_DATE)
                    ->label(trans('Exit date')),
                Forms\Components\Select::make(User::COL_ROLE)
                    ->label(trans('Role'))
                    ->options(fn () => static::assignableRoleOptions())
                    ->default(EnumRole::MEMBER->value)
                    ->required()
                    // Server side guard against a tampered request assigning super_admin
                    ->rule(fn () => Rule::in(static::assignableRoleOptions()->keys())),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make(User::COL_NAME)
                    ->translateLabel()
                    ->copyable()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make(User::COL_EMAIL)
                    ->translateLabel()
                    ->copyable()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make(User::COL_CONTRIBUTION_GROUP)
                    ->translateLabel()
                    ->formatStateUsing(fn (?EnumContributionGroup $state) => isset($state) ? trans($state->value) : null),
                Tables\Columns\TextColumn::make(User::COL_ROLE)
                    ->label(trans('Role'))
                    ->badge(),
                Tables\Columns\TextColumn::make(User::COL_JOIN_DATE)->label(trans('Join date'))
                    ->formatStateUsing(fn (?Carbon $state) => $state?->format('d.m.Y')),
                Tables\Columns\TextColumn::make(User::COL_EXIT_DATE)->label(trans('Exit date'))
                    ->formatStateUsing(fn (?Carbon $state) => $state?->format('d.m.Y')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make(User::COL_CONTRIBUTION_GROUP)->options(
                    ForFilamentTranslator::enum(EnumContributionGroup::getInstances())
                ),
            ])
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
