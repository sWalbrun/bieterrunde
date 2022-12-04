<?php

namespace App\Providers;

use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class SolarWirServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(125);
        DatePicker::configureUsing(fn (DatePicker $picker) => $picker->displayFormat(config('app.date_format')));
    }
}
