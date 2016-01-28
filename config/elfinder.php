<?php

return [
    'middleware' => ['auth'],
    'options'    => [
        'locale' => 'en_US.UTF-8',
        'debug'  => false,
        'roots'  => [[
            'driver'       => 'LocalFileSystem',
            'alias'        => 'Home',
            'rootCssClass' => 'elfinder-button-icon-home',
            'path'         => public_path('media/elfinder/user/{user_id}'),
            'URL'          => 'media/elfinder/user/{user_id}',

        ], [
            'driver' => 'LocalFileSystem',
            'alias'  => 'Shared',
            'path'   => public_path('media/elfinder/shared'),
            'URL'    => 'media/elfinder/shared',
        ]],
    ],
];
