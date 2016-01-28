<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group([], function () {
    Route::get('/', [
        'as'   => 'elfinder',
        'uses' => 'ElfinderController@elfinder',
    ]);

    Route::any('connector', [
        'as'   => 'connector',
        'uses' => 'ElfinderController@connector',
    ]);

    Route::get('sounds/{file}', [
        'as'   => 'sound',
        'uses' => 'ElfinderController@sound',
    ]);
});
