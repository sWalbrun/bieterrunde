<?php

declare(strict_types=1);

use App\Http\Livewire\BidderRoundForm;
use App\Http\Livewire\OfferForm;
use App\Http\Middleware\CanManipulateBidderRound;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'verified',
    'web',
])->group(function () {
    Route::get('/', function () {
        return redirect('/dashboard');
    });
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    Route::get('/vegetable-overview', fn () => view('livewire.vegetable-overview'));

    Route::get('/bidderRounds/{bidderRound}/offers', OfferForm::class);

    Route::middleware(CanManipulateBidderRound::class)->group(function () {
        Route::get('/bidderRounds/create', BidderRoundForm::class);
        Route::get('/bidderRounds/{bidderRound}', BidderRoundForm::class);
    });
});

require('admin.php');
