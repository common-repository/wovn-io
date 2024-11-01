<?php
namespace Wovnio\Wovnwp\WpClasses\RequestHandlers;

require_once('abstract_request_handler.php');

class FileGetContentsRequestHandler extends AbstractRequestHandler
{
    public static function available()
    {
        return ini_get('allow_url_fopen');
    }

    public function file_get_contents($url, $http_context)
    {
        $response = @file_get_contents($url, false, $http_context);
        $response_headers = $http_response_header;

        return array($response, $response_headers);
    }

    protected function post($url, $request_headers, $data, $timeout, $http_proxy = null)
    {
        array_push($request_headers, 'Accept-Encoding: gzip');

        $http_options = array(
            'header' => implode("\r\n", $request_headers),
            'method' => 'POST',
            'content' => $data,
            'timeout' => $timeout,
        );

        if ($http_proxy) {
            $http_options['proxy'] = $http_proxy;
        }

        $http_context = stream_context_create(
            array(
                'http' => $http_options
            )
        );
        list($response, $response_headers) = $this->file_get_contents($url, $http_context);

        if ($response === false) {
            preg_match('{HTTP\/\S*\s(\d{3})}', $response_headers[0], $match);

            $http_error_code = $match[1];

            return array(null, $response_headers, "[fgc] Request failed ($http_error_code)");
        }

        foreach ($response_headers as $c => $h) {
            if (stristr($h, 'content-encoding') && stristr($h, 'gzip')) {
                $response = gzinflate(substr($response, 10, -8));
            }
        }

        return array($response, $response_headers, null);
    }
}
