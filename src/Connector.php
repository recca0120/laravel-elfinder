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

        $temp = isset($data['header']) ? (array) $data['header'] : (array) $this->header;
        unset($data['header']);

        $headers = [];
        foreach ($temp as $header) {
            if (strpos($header, ':') !== false) {
                list($key, $value) = explode(':', $header, 2);
                $headers[$key] = $value;
            }
        }

        $headers['Access-Control-Allow-Origin'] = '*';

        if (isset($data['pointer']) === true) {
            return $this->response = new StreamedResponse(function () use ($data) {
                rewind($data['pointer']);
                fpassthru($data['pointer']);
                if (empty($data['volume']) === false) {
                    $data['volume']->close($data['pointer'], $data['info']['hash']);
                }
            }, 200, $headers);
        }

        if (empty($data['raw']) === false && empty($data['error']) === false) {
            return $this->response = new JsonResponse($data['error'], 500);
        }

        return $this->response = new JsonResponse($data, 200, $headers);
    }

    public function run()
    {
        parent::run();

        return $this->response;
    }

    protected function input_filter($args)
    {
        // if (empty($args['FILES']) === false &&
        //     empty($args['FILES']['upload']) === false &&
        //     empty($args['FILES']['upload']['name']) === false
        // ) {
        //     $args['FILES']['upload']['name'][0] = utf8_encode($args['FILES']['upload']['name'][0]);
        // }

        return parent::input_filter($args);
    }
}
