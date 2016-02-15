<?php

namespace Recca0120\Elfinder\Http\Controllers;

use Closure;
use elFinder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Controller;
use Recca0120\Elfinder\Connector;
use Illuminate\Contracts\Auth\Guard as GuardContract;

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
     * @param \Illuminate\Filesystem\Filesystem $filesystem
     *
     * @return mixed
     */
    public function connector(Filesystem $filesystem, GuardContract $guard)
    {
        $config = config('elfinder');
        $options = array_get($config, 'options', []);

        $roots = array_get($options, 'roots', []);
        foreach ($roots as $key => $root) {
            $root['driver'] = (empty($root['driver']) === true) ? 'LocalFileSystem' : $root['driver'];
            $root['autoload'] = true;

            if (empty($root['path']) === false && ($root['path'] instanceof Closure) === true) {
                $root['path'] = call_user_func($root['path']);
            }

            switch ($root['driver']) {
                case 'LocalFileSystem':
                    if (strpos($root['path'], '{user_id}') !== -1 && $guard->check() === false) {
                        continue;
                    }
                    $user = $guard->user();
                    $userId = $user->id;
                    $root['path'] = str_replace('{user_id}', $userId, $root['path']);
                    $root['URL'] = url(str_replace('{user_id}', $userId, $root['URL']));

                    if ($filesystem->exists($root['path']) === false) {
                        $filesystem->makeDirectory($root['path'], 0755, true);
                    }

                    $root = array_merge([
                        'mimeDetect'    => 'internal',
                        'tmpPath'       => '.tmb',
                        'utf8fix'       => true,
                        'tmbCrop'       => false,
                        'tmbBgColor'    => 'transparent',
                        'accessControl' => array_get($config, 'accessControl'),
                    ], $root);
                    break;
            }
            $roots[$key] = $root;
        }
        $options['roots'] = $roots;
        $connector = new Connector(new elFinder($options));

        return $connector->run();
    }

    /**
     * sound.
     *
     * @param Filesystem $filesystem
     * @param string     $file
     *
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
