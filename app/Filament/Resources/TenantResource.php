<?php

namespace App\Filament\Resources;

use App\Enums\EnumRole;
use App\Filament\EnumNavigationGroups;
use App\Filament\Resources\TenantResource\Pages;
use App\Jobs\SetTenantCookie;
use App\Models\Tenant;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cookie;

use function cookie;

/**
 * Management of the tenants (= the Solawis sharing this instance).
 * Only visible to super admins.
 */
class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    public static function getPluralModelLabel(): string
    {
        return trans('Tenants');
    }

    public static function getModelLabel(): string
    {
        return trans('Tenant');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans(EnumNavigationGroups::ADMINISTRATION);
    }

    public static function isSuperAdmin(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        return $user?->role === EnumRole::SUPER_ADMIN;
    }

    public static function canViewAny(): bool
    {
        return self::isSuperAdmin();
    }

    public static function canCreate(): bool
    {
        return self::isSuperAdmin();
    }

    public static function canDelete(Model $record): bool
    {
        return self::isSuperAdmin();
    }

    public static function canDeleteAny(): bool
    {
        return self::isSuperAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make(Tenant::COL_ID)
                    ->label(trans('Identifier'))
                    ->helperText(trans('Lowercase letters, digits and dashes only — cannot be changed later.'))
                    ->regex('/^[a-z0-9-]+$/')
                    ->unique(table: Tenant::TABLE, column: Tenant::COL_ID)
                    ->required(),
                Forms\Components\TextInput::make('admin_name')
                    ->label(trans('Name of the admin'))
                    ->required(),
                Forms\Components\TextInput::make('admin_email')
                    ->label(trans('E-Mail of the admin'))
                    ->email()
                    ->helperText(trans('This user gets created as admin of the new tenant and receives a welcome mail with a login link.'))
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make(Tenant::COL_ID)
                    ->label(trans('Identifier'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('users_count')
                    ->label(trans('Users'))
                    ->getStateUsing(
                        fn (Tenant $record) => User::query()
                            ->withoutGlobalScopes()
                            ->where(User::COL_FK_TENANT, '=', $record->id)
                            ->count()
                    ),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(trans('Created at'))
                    ->dateTime('d.m.Y'),
            ])
            ->actions([
                Tables\Actions\Action::make('switch')
                    ->label(trans('Switch to tenant'))
                    ->icon('heroicon-o-arrows-right-left')
                    ->action(function (Tenant $record) {
                        Cookie::queue(cookie(SetTenantCookie::TENANT_ID, $record->id));

                        return redirect('/admin');
                    }),
                Tables\Actions\DeleteAction::make()
                    ->modalDescription(trans('All users, bidder rounds and offers of this tenant will be deleted permanently!')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
        ];
    }
}
