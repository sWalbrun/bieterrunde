<?php

namespace App\Providers\Filament;

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\FilamentAuthenticate;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Support\Facades\Blade;
use SWalbrun\FilamentModelImport\FilamentRegexImportPlugin;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('admin')
            ->brandLogo('/logo-solawi.svg')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label(trans('Member area'))
                    ->url('/')
                    ->icon('heroicon-o-home'),
            ])
            ->middleware(config('filament.middleware.base'))
            ->authMiddleware([
                FilamentAuthenticate::class,
                EnsureUserIsAdmin::class,
            ])
            // Shows the active tenant so (switching) super admins never act on the wrong one
            ->renderHook(
                PanelsRenderHook::TOPBAR_START,
                fn (): string => tenant() !== null
                    ? Blade::render('<x-filament::badge color="warning">'.e(tenant('id')).'</x-filament::badge>')
                    : ''
            )
            ->plugin(FilamentRegexImportPlugin::make());
    }
}
