<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SolaWiUsersManagementController;

Route::group(['middleware' => ['web', 'auth', 'admin']], function () {
    Route::resource('users', SolaWiUsersManagementController::class, [
        'names' => [
            'index' => 'users',
            'destroy' => 'user.destroy',
        ],
    ]);
});

Route::middleware(['web', 'auth', 'admin'])->group(function () {
    Route::post('search-users', SolaWiUsersManagementController::class . '@search')->name('search-users');
});
