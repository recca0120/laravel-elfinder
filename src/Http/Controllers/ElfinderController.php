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

        $root = config('filesystems.elfinder.root');
        if (empty($root) === true) {
            $root = config('elfinder.root', public_path('media/elfinder'));
        }

        $path = substr($root, strlen(public_path()) + 1);

        if (auth()->check() === true) {
            $dirs->push([
                'alias' => 'Home',
                'icon' => null,
                'path' => $path.'/user/'.auth()->id(),
            ]);

            $dirs->push([
                'alias' => 'Shared',
                'path' => $path.'/shared',
            ]);
        }

        $dirs = $dirs->map(function ($item) use ($root, $path) {
            $driver = array_get($item, 'driver', 'LocalFileSystem');
            $path = array_get($item, 'path', $path.'/shared', '/');

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
        $file = __DIR__.'/../../../resources/assets/sounds/'.$file;
        $mimeType = File::mimeType($file);

        return response(file_get_contents($file), 200, [
            'content-type' => $mimeType,
        ]);
    }
}
