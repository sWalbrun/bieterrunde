<?php

use App\Http\Livewire\BidderRoundForm;
use App\Http\Livewire\OfferForm;
use App\Http\Middleware\CanManipulateBidderRound;
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

Route::get('/', fn() => redirect('main'));

Route::get('/assets/{path?}', 'Stancl\Tenancy\Controllers\TenantAssetsController@asset')
    ->where('path', '(.*)')
    ->name('stancl.tenancy.asset');
