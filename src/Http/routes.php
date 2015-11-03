<?php

Route::get('/', [
    'as' => 'elfinder',
    'uses' => 'ElfinderController@elfinder',
]);

Route::any('connector', [
    'as' => 'connector',
    'uses' => 'ElfinderController@connector',
]);

Route::get('/sounds/{file}', [
    'as' => 'sound',
    'uses' => 'ElfinderController@sound',
]);
