<?php

return [
    'route' => [
        'prefix' => 'elfinder',
        'as' => 'elfinder.',
        'middleware' => ['web', 'auth'],
    ],
    'accessControl' => [Recca0120\Elfinder\Connector::class, 'access'],
    'options' => [
        'locale' => 'en_US.UTF-8',
        'debug' => false,
        'roots' => [
            [
                'driver' => 'LocalFileSystem',
                'alias' => 'Home',
                'rootCssClass' => 'elfinder-button-icon-home',
                'path' => public_path('storage/media/user/{user_id}'),
                'URL' => 'storage/media/user/{user_id}',
            ], [
                'driver' => 'LocalFileSystem',
                'alias' => 'Shared',
                'path' => public_path('storage/media/shared'),
                'URL' => 'storage/media/shared',
            ],
        ],
    ],
];
