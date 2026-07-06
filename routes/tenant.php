<?php

declare(strict_types=1);

use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'verified',
    'web',
])->group(function () {
    Route::get('/', Dashboard::class)->name('home');
});
