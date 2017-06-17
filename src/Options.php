<?php

namespace Recca0120\Elfinder;

use Closure;
use ArrayObject;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Routing\UrlGenerator;

class Options extends ArrayObject
{
    /**
     * $request.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * $files.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * $request.
     *
     * @var \Illuminate\Contracts\Routing\UrlGenerator
     */
    protected $urlGenerator;

    /**
     * $config.
     *
     * @var array
     */
    protected $config;

    /**
     * $options.
     *
     * @var array
     */
    protected $options;

    /**
     * __construct.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Filesystem\Filesystem $files
     * @param \Illuminate\Contracts\Routing\UrlGenerator $urlGenerator
     * @param array $config
     */
    public function __construct(Request $request, Filesystem $files, UrlGenerator $urlGenerator, $config = [])
    {
        $this->request = $request;
        $this->files = $files;
        $this->urlGenerator = $urlGenerator;
        $this->config = $config;
        $this->options = Arr::get($this->config, 'options', []);

        parent::__construct(array_merge($this->options, [
            'roots' => $this->getRoots(),
        ]));
    }

    /**
     * getRoots.
     *
     * @return array
     */
    protected function getRoots()
    {
        $accessControl = Arr::get($this->config, 'accessControl');
        $roots = Arr::get($this->options, 'roots', []);
        $user = $this->request->user();

        return array_values(array_filter(array_map(function ($disk) use ($user, $accessControl) {
            $disk['driver'] = empty($disk['driver']) === true ? 'LocalFileSystem' : $disk['driver'];
            $disk['autoload'] = true;

            if (empty($disk['path']) === false && ($disk['path'] instanceof Closure) === true) {
                $disk['path'] = call_user_func($disk['path']);
            }
            $method = 'create'.$disk['driver'].'Driver';
            $method = method_exists($this, $method) === true ? $method : 'createDefaultDriver';

            return call_user_func_array([$this, $method], [$disk, $user, $accessControl]);
        }, $roots)));
    }

    /**
     * createDefaultDriver.
     *
     * @param array $disk
     * @param mixed $user
     * @param mixed $accessControl
     * @param bool $makeDirectory
     * @return array
     */
    protected function createDefaultDriver($disk, $user, $accessControl = null, $makeDirectory = false)
    {
        if (strpos($disk['path'], '{user_id}') !== false) {
            if (is_null($user) === true) {
                return;
            }

            $userId = $user->id;
            $disk['path'] = str_replace('{user_id}', $userId, $disk['path']);
            $disk['URL'] = str_replace('{user_id}', $userId, $disk['URL']);
        }

        if ($makeDirectory === true && $this->files->exists($disk['path']) === false) {
            $this->files->makeDirectory($disk['path'], 0755, true);
        }

        $disk['URL'] = $this->urlGenerator->to($disk['URL']);

        return array_merge([
            'accessControl' => $accessControl,
            'autoload' => true,
            'mimeDetect' => 'internal',
            'tmbBgColor' => 'transparent',
            'tmbCrop' => false,
            'utf8fix' => true,
        ], $disk);
    }

    /**
     * createLocalFileSystemDriver.
     *
     * @param  array $disk
     * @param  mixed $user
     * @param  mixed $accessControl
     * @return array
     */
    protected function createLocalFileSystemDriver($disk, $user, $accessControl = null)
    {
        return $this->createDefaultDriver($disk, $user, $accessControl, true);
    }

    /**
     * createTrashDriver.
     *
     * @param  array $disk
     * @param  mixed $user
     * @param  mixed $accessControl
     * @return array
     */
    protected function createTrashDriver($disk, $user, $accessControl = null)
    {
        return $this->createDefaultDriver(array_merge([
            'id' => 1,
        ], $disk), $user, $accessControl, true);
    }
}
