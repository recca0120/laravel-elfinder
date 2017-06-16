<?php

namespace Recca0120\Elfinder;

use elFinderConnector;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Connector extends elFinderConnector
{
    /**
     * response.
     *
     * @var mixed
     */
    protected $response;

    /**
     * run.
     *
     * @return mixed
     */
    public function run()
    {
        parent::run();

        return $this->response;
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

    /**
     * output.
     *
     * @param array $data
     *
     * @return mixed
     */
    protected function output(array $data)
    {
        return $this->response = new StreamedResponse(function () use ($data) {
            header('Access-Control-Allow-Origin: *');
            parent::output($data);
        });
    }
}
