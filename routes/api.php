<?php

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

Route::prefix('v2/')->middleware(['api.trust.ip', 'api.headers'])->group(function () {
    Route::prefix('users')->group(function () {
        Route::resource('', 'UserController');
        Route::get('{user_id}/items', 'UserItemController@index');
        Route::get('{user_id}/items/{user_item_id}', 'UserItemController@show');
        Route::post('{user_id}/items', 'UserItemController@store');
        Route::put('{user_id}/items/{item_id}', 'UserItemController@update');
        Route::delete('{user_id}/items/{item_id}', 'UserItemController@destroy');
        Route::post('{user_id}/points', 'PointController@store');
        Route::get('{user_id}/receipts', 'ReceiptController@index');
        Route::get('{user_id}/receipts/{receipt_id}', 'ReceiptController@show');
        Route::get('{user_id}/xsolla/token', 'UserController@xsollaToken');
    });

    Route::prefix('discords')->group(function () {
        Route::get('attendances', 'UserController@attendances');
        Route::get('{discord_id}', 'UserController@discord');
        Route::post('{discord_id}/attendances', 'UserController@attendance');
    });

    Route::prefix('items')->group(function () {
        Route::get('', 'ItemController@index');
        Route::get('{item_id}', 'ItemController@show');
    });

    Route::get('clients/token', 'ClientController@issue');
});

Route::post('v2/xsolla', 'XsollaWebhookController@index')->middleware('api.xsolla');
