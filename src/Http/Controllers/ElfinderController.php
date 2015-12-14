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
        $config = config('elfinder');

        $roots = array_get($config, 'roots', []);

        foreach ($roots as $key => $root) {
            $root['driver'] = (empty($root['driver']) === true) ? 'LocalFileSystem' : $root['driver'];

            if (empty($root['path']) === false && ($root['path'] instanceof Closure) === true) {
                $root['path'] = call_user_func($root['path']);
            }

            switch ($root['driver']) {
                case 'LocalFileSystem':
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

            class_exists('elFinderVolume'.$root['driver']);
            $roots[$key] = $root;
        }
        $config['roots'] = $roots;
        $connector = new Connector(new elFinder($config));

        return $connector->run();

        // 'driver' => 'LocalFileSystem',
        // 'path' => public_path('media/elfinder/shared'),
        // 'URL' => url('media/elfinder/shared'),
        // // 'treeDeep' => 3,
        // // 'alias' => 'File system',
        // 'mimeDetect' => 'internal',
        // 'tmpPath' => '.tmb',
        // 'utf8fix' => true,
        // 'tmbCrop' => false,
        // 'tmbBgColor' => 'transparent',
        // 'accessControl' => 'access',
        // 'acceptedName' => '/^[^\.].*$/',
        // // 'disabled' => array('extract', 'archive'),
        // // 'tmbSize' => 128,
        // 'attributes' => [[
        //     'pattern' => '/\.js$/',
        //     'read' => true,
        //     'write' => false,
        // ],[
        //     'pattern' => '/^\/icons$/',
        //     'read' => true,
        //     'write' => false,
        // ]],
        // // All Mimetypes not allowed to upload
        // 'uploadDeny' => ['all'],
        // // Mimetype `image` and `text/plain` allowed to upload
        // 'uploadAllow' => ['image', 'text/plain'],
        // // allowed Mimetype `image` and `text/plain` only
        // 'uploadOrder' => ['deny', 'allow'],

        // $dirs = $dirs->map(function ($item) use ($root, $path) {
        //     $driver = array_get($item, 'driver', 'LocalFileSystem');
        //     $path = array_get($item, 'path', $path.'/shared', '/');

        //     $options = array_merge($item, [
        //         'driver' => $driver,
        //         'accessControl' => function ($attr, $path, $data, $volume, $isDir) {
        //             return strpos(basename($path), '.') === 0
        //                 ? ! ($attr == 'read' || $attr == 'write')
        //                 :  null;
        //         },
        //     ]);

        //     switch ($driver) {
        //         case 'LocalFileSystem':
        //             if (File::exists(public_path($path)) === false) {
        //                 File::makeDirectory(public_path($path), 0755, true);
        //             }

        //             if (empty($options['URL'])) {
        //                 $options['URL'] = url($path);
        //             }

        //             if (empty($options['tmpPath']) === true) {
        //                 $options['tmpPath'] = $path.'/.tmp';
        //             }

        //             break;
        //     }

        //     return $options;
        // });
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
