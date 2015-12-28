<?php

namespace Recca0120\Elfinder\Http\Controllers;

use Closure;
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
        $options = config('elfinder.options');

        $roots = array_get($options, 'roots', []);
        foreach ($roots as $key => $root) {
            $root['driver'] = (empty($root['driver']) === true) ? 'LocalFileSystem' : $root['driver'];
            $root['autoload'] = true;

            if (empty($root['path']) === false && ($root['path'] instanceof Closure) === true) {
                $root['path'] = call_user_func($root['path']);
            }

            switch ($root['driver']) {
                case 'LocalFileSystem':
                    if (File::exists($root['path']) === false) {
                        File::makeDirectory($root['path'], 0755, true);
                    }

                    if (empty($root['URL']) === true) {
                        $root['URL'] = url(substr($root['path'], strlen(public_path()) + 1));
                    } elseif (($root['URL'] instanceof Closure) === true) {
                        $root['URL'] = call_user_func($root['URL']);
                    }

                    $root = array_merge([
                        'mimeDetect' => 'internal',
                        'tmpPath' => '.tmb',
                        'utf8fix' => true,
                        'tmbCrop' => false,
                        'tmbBgColor' => 'transparent',
                        'accessControl' => function ($attr, $path, $data, $volume, $isDir) {
                            return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
                                ? ! ($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
                                :  null;                                    // else elFinder decide it itself
                        },
                    ], $root);
                    break;
            }
            $roots[$key] = $root;
        }
        $options['roots'] = $roots;

        return with(new Connector(new elFinder($options)))->run();
    }

    public function sound($file)
    {
        $file = __DIR__.'/../../../resources/elfinder/sounds/'.$file;
        $mimeType = File::mimeType($file);

        return response(file_get_contents($file), 200, [
            'content-type' => $mimeType,
        ]);
    }
}
