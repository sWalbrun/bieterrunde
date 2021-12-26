<?php

use App\Http\Controllers\OfferController;
use App\Http\Livewire\OfferForm;
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

Route::get('/', function() {
   return redirect('/dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');
Route::get('/vegetable-overview', fn() => view('livewire.vegetable-overview'));

Route::get('/bidderRounds/{bidderRound}/offers', OfferForm::class);

require __DIR__ . '/auth.php';
