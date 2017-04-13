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
