<?php

get('/', [
    'as' => 'elfinder',
    'uses' => 'ElfinderController@elfinder',
]);

Route::any('connector', [
    'as' => 'connector',
    'uses' => 'ElfinderController@connector',
]);
