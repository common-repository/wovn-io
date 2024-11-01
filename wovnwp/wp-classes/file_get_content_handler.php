<?php
namespace Wovnio\Wovnwp\WpClasses;

class FileGetContentHandler
{
    function get($request_url, $options)
    {
        $context = stream_context_create($options);
        $response = file_get_contents($request_url, false, $context);

        return array($response, $http_response_header);
    }
}
