<?php

use App\Tenancy\InitializeTenancyByCookie;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SolaWiUsersManagementController;

Route::group(['middleware' => ['web', 'auth', 'admin', InitializeTenancyByCookie::class]], function () {
    Route::resource('users', SolaWiUsersManagementController::class, [
        'names' => [
            'index' => 'users',
            'destroy' => 'user.destroy',
        ],
    ]);
});

Route::middleware(['web', 'auth', 'admin', InitializeTenancyByCookie::class])->group(function () {
    Route::post('search-users', SolaWiUsersManagementController::class . '@search')->name('search-users');
});
