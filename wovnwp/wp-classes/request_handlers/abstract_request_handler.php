<?php
namespace Wovnio\Wovnwp\WpClasses\RequestHandlers;

use Wovnio\Wovnwp\WpClasses\Logger;

abstract class AbstractRequestHandler
{
    abstract protected function post($url, $request_headers, $data, $timeout);

    public static function available()
    {
        return false;
    }

    public function send_request($method, $url, $raw_data, $timeout, $http_proxy = null)
    {
        $formatted_data = http_build_query($raw_data);
        $compressed_data = gzencode($formatted_data);
        $content_length = strlen($compressed_data);
        $uniqueId = Logger::get()->getUniqueId();
        $headers = array(
            'Content-Type: application/octet-stream',
            "Content-Length: $content_length",
            "X-Request-Id: $uniqueId"
        );

        switch ($method) {
            case 'POST':
                return $this->post($url, $headers, $compressed_data, $timeout, $http_proxy);
                break;
        }
    }
}
