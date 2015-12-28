<?php

$middleware = ['auth'];
if (method_exists(app(), 'bindShared') === false) {
    $middleware = array_merge(['web'], $middleware);
}

return [
    'middleware' => $middleware,
    'options' => [
        'locale' => 'en_US.UTF-8',
        'debug' => false,
        'roots' => [[
            'driver' => 'LocalFileSystem',
            'alias' => 'Home',
            'rootCssClass' => 'elfinder-button-icon-home',
            'path' => function () {
                return public_path('media/elfinder/user/'.auth()->id());
            },
            'URL' => function () {
                return url('media/elfinder/user/'.auth()->id());
            },
        ],[
            'driver' => 'LocalFileSystem',
            'alias' => 'Shared',
            'path' => public_path('media/elfinder/shared'),
            'URL' => url('media/elfinder/shared'),
        ]],
    ],
];
