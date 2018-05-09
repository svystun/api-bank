<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('user/register', 'ApiAuthController@register');
Route::post('user/login', 'ApiAuthController@login');
Route::get('user/activate', 'ApiAuthController@activate')->name('activate');

Route::group(['middleware' => 'jwt.auth'], function () {
    Route::post('user/logout', 'ApiAuthController@logout');

    Route::get('user/{user}', function (App\User $user) {
        return $user->email;
    });

    Route::post('card/add', 'CardController@add');
    Route::post('card/charge', 'CardController@charge');

});