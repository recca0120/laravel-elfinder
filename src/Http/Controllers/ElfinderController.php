<?php

namespace Recca0120\Elfinder\Http\Controllers;

use Closure;
use elFinder;
use Illuminate\Contracts\Auth\Guard as GuardContract;
use Illuminate\Contracts\Config\Repository as ConfigRepositoryContract;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Contracts\Routing\ResponseFactory as ResponseFactoryContract;
use Illuminate\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Recca0120\Elfinder\Connector;

class ElfinderController extends Controller
{
    /**
     * elfinder.
     *
     * @param \Illuminate\Contracts\Routing\ResponseFactory $responseFactory
     *
     * @return mixed
     */
    public function elfinder(ResponseFactoryContract $responseFactory)
    {
        return $responseFactory->view('elfinder::elfinder');
    }

    /**
     * connector.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param \Illuminate\Filesystem\Filesystem            $filesystem
     * @param \Illuminate\Contracts\Routing\UrlGenerator   $urlGenerator
     * @param \Illuminate\Contracts\Config\Repository      $config
     * @param \Illuminate\Contracts\Auth\Guard             $guard
     *
     * @return mixed
     */
    public function connector(
        ApplicationContract $app,
        Filesystem $filesystem,
        UrlGeneratorContract $urlGenerator,
        ConfigRepositoryContract $config,
        GuardContract $guard
    ) {
        $config = $config->get('elfinder');
        $options = Arr::get($config, 'options', []);

        $roots = Arr::get($options, 'roots', []);
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
                    $root['URL'] = $urlGenerator->to(str_replace('{user_id}', $userId, $root['URL']));

                    if ($filesystem->exists($root['path']) === false) {
                        $filesystem->makeDirectory($root['path'], 0755, true);
                    }

                    $root = array_merge([
                        'mimeDetect'    => 'internal',
                        'tmpPath'       => '.tmb',
                        'utf8fix'       => true,
                        'tmbCrop'       => false,
                        'tmbBgColor'    => 'transparent',
                        'accessControl' => Arr::get($config, 'accessControl'),
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
     * @param \Illuminate\Contracts\Routing\ResponseFactory $responseFactory
     * @param \Illuminate\Filesystem\Filesystem             $filesystem
     * @param \Illuminate\Http\Request                      $request
     * @param string                                        $file
     *
     * @return \Illuminate\Http\Response
     */
    public function sound(ResponseFactoryContract $responseFactory, Filesystem $filesystem, Request $request, $file)
    {
        $filename = __DIR__.'/../../../resources/elfinder/sounds/'.$file;
        $mimeType = $filesystem->mimeType($filename);
        $lastModified = $filesystem->lastModified($filename);
        $eTag = sha1_file($filename);
        $headers = [
            'content-type'  => $mimeType,
            'last-modified' => date('D, d M Y H:i:s ', $lastModified).'GMT',
        ];

        if (@strtotime($request->server('HTTP_IF_MODIFIED_SINCE')) === $lastModified ||
            trim($request->server('HTTP_IF_NONE_MATCH'), '"') === $eTag
        ) {
            $response = $responseFactory->make(null, 304, $headers);
        } else {
            $response = $responseFactory->stream(function () use ($filename) {
                $out = fopen('php://output', 'wb');
                $file = fopen($filename, 'rb');
                stream_copy_to_stream($file, $out, filesize($filename));
                fclose($out);
                fclose($file);
            }, 200, $headers);
        }

        return $response->setEtag($eTag);
    }
}
