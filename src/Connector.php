<?php

namespace Recca0120\Elfinder;

use elFinder;
use elFinderConnector;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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
     * output.
     *
     * @param array $data
     *
     * @return mixed
     */
    protected function output(array $data)
    {
        // clear output buffer
        while (@ob_get_level()) {
            @ob_end_clean();
        }

        $header = isset($data['header']) ? $data['header'] : $this->header;
        unset($data['header']);
        $headers = collect(is_array($header) ? $header : [$header])->map(function ($header) {
            return explode(':', $header, 2);
        })->pluck(1, 0)->toArray();

        $headers['Access-Control-Allow-Origin'] = '*';

        if (isset($data['pointer'])) {
            $toEnd = true;
            $status = 200;
            $fp = $data['pointer'];
            if (elFinder::isSeekableStream($fp)) {
                $headers['Accept-Ranges'] = 'bytes';
                $psize = null;
                if (!empty($_SERVER['HTTP_RANGE'])) {
                    $size = $data['info']['size'];
                    $start = 0;
                    $end = $size - 1;
                    if (preg_match('/bytes=(\d*)-(\d*)(,?)/i', $_SERVER['HTTP_RANGE'], $matches)) {
                        if (empty($matches[3])) {
                            if (empty($matches[1]) && $matches[1] !== '0') {
                                $start = $size - $matches[2];
                            } else {
                                $start = intval($matches[1]);
                                if (!empty($matches[2])) {
                                    $end = intval($matches[2]);
                                    if ($end >= $size) {
                                        $end = $size - 1;
                                    }
                                    $toEnd = ($end == ($size - 1));
                                }
                            }
                            $psize = $end - $start + 1;

                            // $headers['HTTP/1.1 206 Partial Content'];
                            $status = 206;
                            $headers['Content-Length'] = $psize;
                            $headers['Content-Range'] = 'bytes '.$start.'-'.$end.'/'.$size;

                            fseek($fp, $start);
                        }
                    }
                }
                if (is_null($psize)) {
                    rewind($fp);
                }
            } else {
                $headers['Accept-Ranges'] = 'none';
            }

            // unlock session data for multiple access
            session_id() && session_write_close();
            app('session')->close();
            // client disconnect should abort
            ignore_user_abort(false);

            return $this->response = new StreamedResponse(function () use ($toEnd, $fp, $psize, $data) {
                if ($toEnd) {
                    fpassthru($fp);
                } else {
                    $out = fopen('php://output', 'wb');
                    stream_copy_to_stream($fp, $out, $psize);
                    fclose($out);
                }

                if (!empty($data['volume'])) {
                    $data['volume']->close($data['pointer'], $data['info']['hash']);
                }

            }, $status, $headers);
        }

        if (!empty($data['raw']) && !empty($data['error'])) {
            return $this->response = new JsonResponse($data['error'], 500, $headers);
        }

        return $this->response = new JsonResponse($data, 200, $headers);
    }

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
}
