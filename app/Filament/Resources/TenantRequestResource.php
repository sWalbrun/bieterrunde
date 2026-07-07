<?php

namespace App\Filament\Resources;

use App\Enums\EnumRole;
use App\Enums\EnumTenantRequestStatus;
use App\Filament\EnumNavigationGroups;
use App\Filament\Resources\TenantRequestResource\Pages;
use App\Models\Tenant;
use App\Models\TenantRequest;
use App\Models\User;
use App\Notifications\TenantRequestRejected;
use App\Tenancy\TenantProvisioner;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Review of the test account requests submitted via the public form.
 * Only visible to super admins.
 */
class TenantRequestResource extends Resource
{
    protected static ?string $model = TenantRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope-open';

    public static function getPluralModelLabel(): string
    {
        return trans('Account requests');
    }

    public static function getModelLabel(): string
    {
        return trans('Account request');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans(EnumNavigationGroups::ADMINISTRATION);
    }

    public static function canViewAny(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        return $user?->role === EnumRole::SUPER_ADMIN;
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = TenantRequest::query()
            ->where(TenantRequest::COL_STATUS, '=', EnumTenantRequestStatus::PENDING)
            ->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make(TenantRequest::COL_SOLAWI_NAME)
                    ->label(trans('Name of your Solawi'))
                    ->searchable(),
                Tables\Columns\TextColumn::make(TenantRequest::COL_NAME)
                    ->label(trans('Name')),
                Tables\Columns\TextColumn::make(TenantRequest::COL_EMAIL)
                    ->label(trans('E-Mail'))
                    ->copyable(),
                Tables\Columns\TextColumn::make(TenantRequest::COL_WEBSITE_URL)
                    ->label(trans('Website'))
                    ->url(fn (TenantRequest $record) => $record->websiteUrl, shouldOpenInNewTab: true),
                Tables\Columns\TextColumn::make(TenantRequest::COL_STATUS)
                    ->label(trans('Status'))
                    ->badge(),
                Tables\Columns\TextColumn::make(TenantRequest::COL_CREATED_AT)
                    ->label(trans('Created at'))
                    ->dateTime('d.m.Y'),
            ])
            ->defaultSort(TenantRequest::COL_CREATED_AT, 'desc')
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label(trans('Approve'))
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (TenantRequest $record) => $record->isPending())
                    ->form(fn (TenantRequest $record) => [
                        TextInput::make('tenantId')
                            ->label(trans('Identifier'))
                            ->helperText(trans('Lowercase letters, digits and dashes only — cannot be changed later.'))
                            ->default(Str::slug($record->solawiName))
                            ->regex('/^[a-z0-9-]+$/')
                            ->unique(table: Tenant::TABLE, column: Tenant::COL_ID)
                            ->required(),
                    ])
                    ->modalDescription(trans('A new tenant gets created and the requester receives a welcome mail with a login link.'))
                    ->action(fn (TenantRequest $record, array $data) => self::approve($record, $data['tenantId'])),
                Tables\Actions\Action::make('reject')
                    ->label(trans('Reject'))
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (TenantRequest $record) => $record->isPending())
                    ->requiresConfirmation()
                    ->action(fn (TenantRequest $record) => self::reject($record)),
            ]);
    }

    public static function approve(TenantRequest $record, string $tenantId): void
    {
        $emailExists = User::query()
            ->withoutGlobalScopes()
            ->where(User::COL_EMAIL, '=', $record->email)
            ->exists();
        if ($emailExists) {
            Notification::make()
                ->title(trans('A user with this e-mail address already exists — the request cannot be approved.'))
                ->danger()
                ->send();

            return;
        }

        DB::transaction(function () use ($record, $tenantId) {
            // Re-check inside the transaction to prevent a double approve
            $record->refresh();
            if (! $record->isPending()) {
                return;
            }

            $tenant = app(TenantProvisioner::class)->provision(
                $tenantId,
                $record->name,
                $record->email,
            );

            $record->status = EnumTenantRequestStatus::APPROVED;
            $record->tenant_id = $tenant->id;
            $record->save();
        });

        Notification::make()
            ->title(trans('The tenant has been created and the requester received a welcome mail.'))
            ->success()
            ->send();
    }

    public static function reject(TenantRequest $record): void
    {
        $record->status = EnumTenantRequestStatus::REJECTED;
        $record->save();

        \Illuminate\Support\Facades\Notification::route('mail', $record->email)
            ->notify(new TenantRequestRejected($record));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenantRequests::route('/'),
        ];
    }
}
