<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web', 'auth', 'admin'], 'namespace' => 'jeremykenedy\laravelusers\app\Http\Controllers'], function () {
    Route::resource('users', 'UsersManagementController', [
        'names' => [
            'index'   => 'users',
            'destroy' => 'user.destroy',
        ],
    ]);
});

Route::middleware(['web', 'auth', 'admin'])->group(function () {
    Route::post('search-users', 'jeremykenedy\laravelusers\app\Http\Controllers\UsersManagementController@search')->name('search-users');
});
