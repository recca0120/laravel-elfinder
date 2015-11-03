<?php

namespace Recca0120\Elfinder;

use elFinderConnector;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Connector extends elFinderConnector
{
    protected $response;

    protected function output(array $data)
    {
        // clear output buffer
        while (@ob_get_level()) {
            @ob_end_clean();
        }

        $header = isset($data['header']) ? $data['header'] : $this->header;
        unset($data['header']);

        $headers = [];
        if ($header) {
            foreach ((array) $header as $headerString) {
                if (strpos($headerString, ':') !== false) {
                    list($key, $value) = explode(':', $headerString, 2);
                    $headers[$key] = $value;
                }
            }
        }

        $headers['Access-Control-Allow-Origin'] = '*';

        if (isset($data['pointer']) === true) {
            $this->response = new StreamedResponse(function () use ($data) {
                rewind($data['pointer']);
                fpassthru($data['pointer']);
                if (empty($data['volume']) === false) {
                    $data['volume']->close($data['pointer'], $data['info']['hash']);
                }
            }, 200, $headers);
        } else {
            if (empty($data['raw']) === false && empty($data['error']) === false) {
                $this->response = new JsonResponse($data['error'], 500);
            } else {
                $this->response = new JsonResponse($data, 200, $headers);
            }
        }
    }

    public function run()
    {
        parent::run();

        return $this->response;
    }
}
