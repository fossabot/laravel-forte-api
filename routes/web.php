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

Route::post('withdraw/{itemId}', 'UserController@withdraw')->middleware('auth.forte');

Route::get('panel', function () {
    return \Socialite::with('discord')->redirect();
});

Route::get('panel/{token}', 'UserController@panel')->name('user.panel')->middleware('auth.forte');
//Route::post('withdraw', 'UserItemController@withdraw');

Route::get('login/discord', function () {
    return \Socialite::with('discord')->redirect();
})->name('login');
Route::get('login/discord/callback', 'UserController@login');
