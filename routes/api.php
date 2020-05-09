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
        Route::prefix('{user_id:[0-9+}')->group(function () {
            Route::prefix('items')->group(function () {
                Route::get('', 'UserItemController@index');
                Route::post('', 'UserItemController@store');
                Route::get('{user_item_id:[0-9+}', 'UserItemController@show');
                Route::prefix('{item_id:[0-9+}')->group(function () {
                    Route::put('', 'UserItemController@update');
                    Route::delete('', 'UserItemController@destroy');
                });
            });
            Route::prefix('receipts')->group(function () {
                Route::get('', 'ReceiptController@index');
                Route::get('{receipt_id:[0-9]+}', 'ReceiptController@show');
            });
            Route::post('points', 'PointController@store');
            Route::get('xsolla/token', 'UserController@xsollaToken');
        });
    });

    Route::prefix('discords')->group(function () {
        Route::get('attendances', 'UserController@attendances');
        Route::prefix('{discord_id:[0-9+}')->group(function () {
            Route::get('', 'UserController@discord');
            Route::post('attendances', 'UserController@attendance');
        });
    });

    Route::prefix('items')->group(function () {
        Route::get('', 'ItemController@index');
        Route::get('{item_id:[0-9]+}', 'ItemController@show');
    });

    Route::get('clients/token', 'ClientController@issue');
});

Route::post('v2/xsolla', 'XsollaWebhookController@index')->middleware('api.xsolla');
