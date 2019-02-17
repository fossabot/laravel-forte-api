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

Route::prefix('v1/')->middleware(['api.headers'])->group(function () {
    Route::resource('users', 'UserController');

    Route::post('users/{user_id}/discord', 'DiscordController@store');
    Route::put('users/{user_id}/discord', 'DiscordController@update');
    Route::get('discords', 'DiscordController@index');
    Route::get('discords/{discord_id}', 'DiscordController@show');

    Route::get('users/{user_id}/items', 'ItemController@index');
    Route::get('users/{user_id}/items/{item_id}', 'ItemController@show');
    Route::post('users/{user_id}/items', 'ItemController@store');
    Route::put('users/{user_id}/items/{item_id}', 'ItemController@update');
    Route::delete('users/{user_id}/items/{item_id}', 'ItemController@destroy');
    Route::post('users/{user_id}/points', 'PointController@store');
    Route::get('users/{user_id}/receipts', 'ReceiptController@index');
    Route::get('users/{user_id}/receipts/{receipt_id}', 'ReceiptController@show');
});
