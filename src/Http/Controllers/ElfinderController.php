<?php

namespace Recca0120\Elfinder\Http\Controllers;

use elFinder;
use File;
use Recca0120\Elfinder\Connector;

class ElfinderController extends Controller
{
    public function elfinder()
    {
        return view('elfinder::elfinder');
    }

    public function connector()
    {
        $dirs = [[
            'alias' => 'Home',
            'icon' => null,
            'path' => 'upload/filemanager/user/'.auth()->id().'/',
        ], [
            'alias' => 'Shared',
            'path' => 'upload/filemanager/shared/',
        ]];

        $roots = [];
        foreach ($dirs as $dir) {
            if (File::exists(public_path($dir['path'])) === false) {
                File::makeDirectory(public_path($dir['path']), 0755, true);
            }
            $roots[] = array_merge([
                'driver' => 'LocalFileSystem',
                'URL' => url($dir['path']),
                'tmpPath' => $dir['path'].'.tmp',
                'accessControl' => function ($attr, $path, $data, $volume, $isDir) {
                    return strpos(basename($path), '.') === 0
                        ? ! ($attr == 'read' || $attr == 'write')
                        :  null;
                },
            ], $dir);
        }

        $opts = [
            // 'debug' => true,
            'roots' => $roots,
        ];

        $connector = new Connector(new elFinder($opts));

        return $connector->run();
    }
}
