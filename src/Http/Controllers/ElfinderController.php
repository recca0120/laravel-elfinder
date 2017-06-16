<?php

namespace Recca0120\Elfinder\Http\Controllers;

use Illuminate\Http\Request;
use Recca0120\Elfinder\Connector;
use Illuminate\Routing\Controller;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Routing\ResponseFactory;

class ElfinderController extends Controller
{
    /**
     * $responseFactory.
     *
     * @var \Illuminate\Contracts\Routing\ResponseFactory
     */
    protected $responseFactory;

    /**
     * __construct.
     *
     * @param \Illuminate\Contracts\Routing\ResponseFactory $responseFactory
     */
    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * elfinder.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function elfinder(Request $request)
    {
        $token = null;
        if ($request->hasSession() === true) {
            $token = $request->session()->token();
        }

        return $this->responseFactory->view('elfinder::elfinder', compact('token'));
    }

    /**
     * connector.
     *
     * @param \Recca0120\Elfinder\Connector $elfinder
     * @return mixed
     */
    public function connector(Connector $connector)
    {
        return $connector->run();
    }

    /**
     * sound.
     *
     * @param \Illuminate\Filesystem\Filesystem $files
     * @param \Illuminate\Http\Request $request
     * @param string $file
     * @return \Illuminate\Http\Response
     */
    public function sound(Filesystem $files, Request $request, $file)
    {
        $filename = __DIR__.'/../../../resources/elfinder/sounds/'.$file;
        $mimeType = $files->mimeType($filename);
        $lastModified = $files->lastModified($filename);
        $eTag = sha1_file($filename);
        $headers = [
            'content-type' => $mimeType,
            'last-modified' => date('D, d M Y H:i:s ', $lastModified).'GMT',
        ];

        if (@strtotime($request->server('HTTP_IF_MODIFIED_SINCE')) === $lastModified ||
            trim($request->server('HTTP_IF_NONE_MATCH'), '"') === $eTag
        ) {
            $response = $this->responseFactory->make(null, 304, $headers);
        } else {
            $response = $this->responseFactory->stream(function () use ($filename) {
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
