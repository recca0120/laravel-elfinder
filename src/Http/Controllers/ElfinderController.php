<?php

namespace Recca0120\Elfinder\Http\Controllers;

use elFinder;
use File;
use Illuminate\Routing\Controller;
use Recca0120\Elfinder\Connector;

class ElfinderController extends Controller
{
    public function elfinder()
    {
        return view('elfinder::elfinder');
    }

    public function connector()
    {
        $dirs = collect();

        if (auth()->check() === true) {
            $dirs->push([
                'alias' => 'Home',
                'icon' => null,
                'path' => 'upload/filemanager/user/'.auth()->id(),
            ]);

            $dirs->push([
                'alias' => 'Shared',
                'path' => 'upload/filemanager/shared',
            ]);
        }

        $dirs = $dirs->map(function ($item) {
            $driver = array_get($item, 'driver', 'LocalFileSystem');
            $path = trim(array_get($item, 'path', 'upload/filemanager/shared'), '/');
            $options = array_merge($item, [
                'driver' => $driver,
                'accessControl' => function ($attr, $path, $data, $volume, $isDir) {
                    return strpos(basename($path), '.') === 0
                        ? ! ($attr == 'read' || $attr == 'write')
                        :  null;
                },
            ]);

            switch ($driver) {
                case 'LocalFileSystem':
                    if (File::exists(public_path($path)) === false) {
                        File::makeDirectory(public_path($path), 0755, true);
                    }

                    if (empty($options['URL'])) {
                        $options['URL'] = url($path);
                    }

                    if (empty($options['tmpPath']) === true) {
                        $options['tmpPath'] = $path.'/.tmp';
                    }

                    break;
            }

            return $options;
        });

        $opts = [
            // 'debug' => true,
            'roots' => $dirs->toArray(),
        ];

        $connector = new Connector(new elFinder($opts));

        return $connector->run();
    }

    public function sound($file)
    {
        return response()->download(__DIR__.'/../../../resources/assets/sounds/rm.wav');
    }
}
