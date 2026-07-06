<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\MagicLinkController;
use App\Livewire\Auth\Login;
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

Route::get('/', fn () => redirect('main'));

Route::get('/login', Login::class)->name('login');

// Not /login/magic-link/… — that path is (still) taken by the filament-passwordless package routes.
Route::get('/login/link/{user}', MagicLinkController::class)
    ->middleware(['signed', 'throttle:10,1'])
    ->name('login.magic-link');

Route::post('/logout', LogoutController::class)
    ->middleware('auth')
    ->name('logout');

Route::get('/assets/{path?}', 'Stancl\Tenancy\Controllers\TenantAssetsController@asset')
    ->where('path', '(.*)')
    ->name('stancl.tenancy.asset');
