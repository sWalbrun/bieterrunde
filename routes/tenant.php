<?php

declare(strict_types=1);

use App\Livewire\Dashboard;
use App\Livewire\OfferForm;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'verified',
    'web',
])->group(function () {
    Route::get('/', Dashboard::class)->name('home');
    Route::get('/gebote', OfferForm::class)->name('offers');
});
