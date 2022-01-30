<?php

use App\Http\Livewire\BidderRoundForm;
use App\Http\Livewire\OfferForm;
use App\Http\Middleware\CanManipulateBidderRound;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth', 'verified'])->group(function () {
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

require __DIR__.'/auth.php';
