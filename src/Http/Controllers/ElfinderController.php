<?php

namespace Recca0120\Elfinder\Http\Controllers;

use Closure;
use elFinder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Controller;
use Recca0120\Elfinder\Connector;

class ElfinderController extends Controller
{
    /**
     * elfinder.
     *
     * @return mixed
     */
    public function elfinder()
    {
        return view('elfinder::elfinder');
    }

    /**
     * connector.
     *
     * @param  \Illuminate\Filesystem\Filesystem $filesystem
     * @return mixed
     */
    public function connector(Filesystem $filesystem)
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
                    if (strpos($root['path'], '{user_id}') != -1 && auth()->check() === false) {
                        continue;
                    } else {
                        $userId = auth()->user()->id;
                        $root['path'] = str_replace('{user_id}', $userId, $root['path']);
                        $root['URL'] = url(str_replace('{user_id}', $userId, $root['URL']));
                    }

                    if ($filesystem->exists($root['path']) === false) {
                        $filesystem->makeDirectory($root['path'], 0755, true);
                    }

                    $root = array_merge([
                        'mimeDetect'    => 'internal',
                        'tmpPath'       => '.tmb',
                        'utf8fix'       => true,
                        'tmbCrop'       => false,
                        'tmbBgColor'    => 'transparent',
                        'accessControl' => function ($attr, $path, $data, $volume, $isDir) {
                            return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
                                ? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
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

    /**
     * sound.
     *
     * @param  Filesystem $filesystem
     * @param  string $file
     * @return \Illuminate\Http\Response
     */
    public function sound(Filesystem $filesystem, $file)
    {
        $file = __DIR__.'/../../../resources/elfinder/sounds/'.$file;
        $mimeType = $filesystem->mimeType($file);

        return response(file_get_contents($file), 200, [
            'content-type' => $mimeType,
        ]);
    }
}
