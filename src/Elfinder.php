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
     * connector.
     *
     * @param \Recca0120\Elfinder\Session $session
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Filesystem\Filesystem $filesystem
     * @param \Illuminate\Contracts\Routing\UrlGenerator $urlGenerator
     * @param array $config
     *
     * @return mixed
     */
    public function __construct(
        Session $session,
        Request $request,
        Filesystem $filesystem,
        UrlGenerator $urlGenerator,
        $config = []
    ) {
        $this->filesystem = $filesystem;
        $this->urlGenerator = $urlGenerator;
        $this->session = $session;
        $this->request = $request;
        $this->config = $config;
    }

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

                    if ($this->filesystem->exists($disk['path']) === false) {
                        $this->filesystem->makeDirectory($disk['path'], 0755, true);
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

    public static function access($attr, $path, $data, $volume, $isDir)
    {
        return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
            ? ! ($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
            : null;                                    // else elFinder decide it itself
    }
}
