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
use Recca0120\Elfinder\Session;
use Illuminate\Session\SessionManager;

class ElfinderController extends Controller
{
    /**
     * elfinder.
     *
     * @param \Illuminate\Contracts\Routing\ResponseFactory $responseFactory
     * @param \Illuminate\Session\SessionManager            $sessionManager
     *
     * @return mixed
     */
    public function elfinder(ResponseFactoryContract $responseFactory, SessionManager $sessionManager)
    {
        $token = $sessionManager->driver()->token();

        return $responseFactory->view('elfinder::elfinder', compact('token'));
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
        GuardContract $guard,
        Session $session
    ) {
        $config = $config->get('elfinder');
        $options = Arr::get($config, 'options', []);

        $roots = Arr::get($options, 'roots', []);
        foreach ($roots as $key => $disk) {
            $disk['driver'] = (empty($disk['driver']) === true) ? 'LocalFileSystem' : $disk['driver'];
            $disk['autoload'] = true;

            if (empty($disk['path']) === false && ($disk['path'] instanceof Closure) === true) {
                $disk['path'] = call_user_func($disk['path']);
            }

            switch ($disk['driver']) {
                case 'LocalFileSystem':
                    if (strpos($disk['path'], '{user_id}') !== -1 && $guard->check() === false) {
                        continue;
                    }
                    $user = $guard->user();
                    $userId = $user->id;
                    $disk['path'] = str_replace('{user_id}', $userId, $disk['path']);
                    $disk['URL'] = $urlGenerator->to(str_replace('{user_id}', $userId, $disk['URL']));

                    if ($filesystem->exists($disk['path']) === false) {
                        $filesystem->makeDirectory($disk['path'], 0755, true);
                    }

                    $disk = array_merge([
                        'mimeDetect'    => 'internal',
                        'utf8fix'       => true,
                        'tmbCrop'       => false,
                        'tmbBgColor'    => 'transparent',
                        'accessControl' => Arr::get($config, 'accessControl'),
                    ], $disk);
                    break;
            }
            $roots[$key] = $disk;
        }
        $options = array_merge($options, [
            'roots' => $roots,
            'session' => $session,
        ]);
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
