<?php

return [
    'locale' => 'en_US.UTF-8',
    'debug' => true,
    'roots' => [[
        'driver' => 'LocalFileSystem',
        'alias' => 'Home',
        // 'icon' => null,
        'path' => function () {
            return public_path('media/elfinder/user/'.auth()->id());
        },
        'URL' => function () {
            return url('media/elfinder/user/'.auth()->id());
        },
    ],[
        'driver' => 'LocalFileSystem',
        'alias' => 'Shared',
        // 'icon' => 'local',
        'path' => public_path('media/elfinder/shared'),
        'URL' => url('media/elfinder/shared'),
    ]],
];
