<?php

return [
    'route' => [
        'middleware'    => ['web', 'auth'],
        'prefix'        => 'elfinder',
    ],
    'accessControl' => [Recca0120\Elfinder\Elfinder::class, 'access'],
    'options'       => [
        'locale' => 'en_US.UTF-8',
        'debug'  => false,
        'roots'  => [[
            'driver'       => 'LocalFileSystem',
            'alias'        => 'Home',
            'rootCssClass' => 'elfinder-button-icon-home',
            'path'         => public_path('storage/media/user/{user_id}'),
            'URL'          => 'storage/media/user/{user_id}',
        ], [
            'driver' => 'LocalFileSystem',
            'alias'  => 'Shared',
            'path'   => public_path('storage/media/shared'),
            'URL'    => 'storage/media/shared',
        ]],
    ],
];
