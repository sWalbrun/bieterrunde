<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\MagicLinkController;
use App\Livewire\Auth\Login;
use App\Livewire\RequestTestAccount;
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

// The panel moved from /main to /admin — keep old bookmarks working
Route::redirect('/main', '/admin', 301);
Route::get('/main/{any}', fn (string $any) => redirect("/admin/$any", 301))->where('any', '.*');

Route::get('/login', Login::class)->name('login');

Route::get('/request-account', RequestTestAccount::class)->name('request-account');

Route::view('/impressum', 'legal.imprint')->name('imprint');
Route::view('/datenschutz', 'legal.privacy')->name('privacy');

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
