<?php

return [
    'middleware'    => ['web', 'auth'],
    'as'            => 'elfinder::',
    'prefix'        => 'elfinder',
    'accessControl' => function ($attr, $path, $data, $volume, $isDir) {
        return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
            ? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
            : null;                                    // else elFinder decide it itself
    },
    'options'    => [
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
