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
        Route::prefix('{user_id}')->group(function () {
            Route::get('', 'UserController@show');
            Route::patch('', 'UserController@update');
            Route::delete('', 'UserController@destroy');
            Route::prefix('items')->group(function () {
                Route::get('', 'UserItemController@index');
                Route::post('', 'UserItemController@store');
                Route::get('{user_item_id}', 'UserItemController@show');
                Route::prefix('{item_id}')->group(function () {
                    Route::put('', 'UserItemController@update');
                    Route::delete('', 'UserItemController@destroy');
                });
            });
            Route::prefix('receipts')->group(function () {
                Route::get('', 'ReceiptController@index');
                Route::get('{receipt_id}', 'ReceiptController@show');
            });
            Route::post('points', 'PointController@store');
            Route::get('xsolla/token', 'UserController@xsollaToken');
        });
    });

    Route::prefix('discords')->group(function () {
        Route::prefix('{discord_id}')->group(function () {
            Route::get('', 'UserController@discord');
            Route::post('attendances', 'UserController@attendance');
        });
        Route::prefix('attendances')->group(function () {
            Route::get('', 'AttendanceController@show');
            Route::post('', 'AttendanceController@store');
            Route::post('unpack', 'AttendanceController@unpack');
        });
    });

    Route::prefix('items')->group(function () {
        Route::get('', 'ItemController@index');
        Route::get('{item_id}', 'ItemController@show');
    });

    Route::get('clients/token', 'ClientController@issue');
});

Route::post('v2/xsolla', 'XsollaWebhookController@index')->middleware('api.xsolla');
