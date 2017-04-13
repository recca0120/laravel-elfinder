<?php

namespace Recca0120\Elfinder;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use elFinder as BaseElfinder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Routing\UrlGenerator;

class Elfinder
{
    /**
     * $session.
     *
     * @var \Recca0120\Elfinder\Session
     */
    protected $session;

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
     * $urlGenerator.
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
     * connector.
     *
     * @param \Recca0120\Elfinder\Session $session
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Filesystem\Filesystem $files
     * @param \Illuminate\Contracts\Routing\UrlGenerator $urlGenerator
     * @param array $config
     */
    public function __construct(
        Session $session,
        Request $request,
        Filesystem $files,
        UrlGenerator $urlGenerator,
        $config = []
    ) {
        $this->files = $files;
        $this->urlGenerator = $urlGenerator;
        $this->session = $session;
        $this->request = $request;
        $this->config = $config;
    }

    /**
     * getConnector.
     *
     * @return Connector
     */
    public function getConnector()
    {
        $options = Arr::get($this->config, 'options', []);
        $user = $this->request->user();
        $accessControl = Arr::get($this->config, 'accessControl');

        $roots = Arr::get($options, 'roots', []);
        foreach ($roots as $key => $disk) {
            $disk['driver'] = (empty($disk['driver']) === true) ? 'LocalFileSystem' : $disk['driver'];
            $disk['autoload'] = true;

            if (empty($disk['path']) === false && ($disk['path'] instanceof Closure) === true) {
                $disk['path'] = call_user_func($disk['path']);
            }

            switch ($disk['driver']) {
                case 'LocalFileSystem':
                    if (strpos($disk['path'], '{user_id}') !== -1 && is_null($user) === true) {
                        continue;
                    }
                    $userId = $user->id;
                    $disk['path'] = str_replace('{user_id}', $userId, $disk['path']);
                    $disk['URL'] = $this->urlGenerator->to(str_replace('{user_id}', $userId, $disk['URL']));

                    if ($this->files->exists($disk['path']) === false) {
                        $this->files->makeDirectory($disk['path'], 0755, true);
                    }

                    $disk = array_merge([
                        'mimeDetect' => 'internal',
                        'utf8fix' => true,
                        'tmbCrop' => false,
                        'tmbBgColor' => 'transparent',
                        'accessControl' => $accessControl,
                    ], $disk);
                    break;
            }
            $roots[$key] = $disk;
        }
        $options = array_merge($options, [
            'roots' => $roots,
            'session' => $this->session,
        ]);

        return new Connector(new BaseElfinder($options));
    }

    /**
     * access.
     *
     * @param array  $attr
     * @param string  $path
     * @param array $data
     * @param string $volume
     * @param bool $isDir
     * @return bool|null
     */
    public static function access($attr, $path, $data, $volume, $isDir)
    {
        return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
            ? ! ($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
            : null;                                    // else elFinder decide it itself
    }
}
