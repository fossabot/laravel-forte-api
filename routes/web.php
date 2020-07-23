<?php

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('error', function () {
    return view('xsolla.error');
});

// disable inventory and shops
//Route::get('inventory', 'UserItemController@inventory')->name('item.inventory')->middleware('auth.forte');
//Route::get('shops/{token}', 'UserController@shortXsollaURL')->name('xsolla.short')->middleware('auth.forte');
Route::get('panel', function () {
    return \Socialite::with('discord')->redirect();
});
Route::get('panel/{token}', 'UserController@panel')->name('user.panel')->middleware('auth.forte');
//Route::post('withdraw', 'UserItemController@withdraw');

/*
 * @deprecated admin dashboard
 */
Route::prefix('dashboard/')->group(function () {
    Route::any('/signin', 'DashboardController@signin');
    Route::middleware('auth')->group(function () {
        Route::get('/', 'DashboardController@index')->name('dashboard.index');
        Route::get('/users', 'DashboardController@users')->name('dashboard.users');
        Route::get('/errors', 'DashboardController@errors')->name('dashboard.errors');

        Route::post('logout', 'DashboardController@logout')->name('dashboard.logout');
    });
});

Route::get('login/discord', function () {
    return \Socialite::with('discord')->redirect();
})->name('login');
Route::get('login/discord/callback', 'UserController@login');
