<?php
namespace Wovnio\Wovnwp\WpClasses\RequestHandlers;

use Wovnio\Utils\HTTPHeaderParser\HTTPHeaderParser;
use Wovnio\Wovnwp\WpClasses\Logger;

require_once('abstract_request_handler.php');
require_once DIRNAME(__FILE__) . '../../utils/HTTPHeaderParser.php';
require_once DIRNAME(__FILE__) . '../../logger.php';

function get_curl_protocols()
{
    $curl_version = curl_version();

    return $curl_version['protocols'];
}

class CurlRequestHandler extends AbstractRequestHandler
{
    public static function available()
    {
        $used_functions = array('curl_version', 'curl_init', 'curl_setopt_array', 'curl_exec', 'curl_getinfo', 'curl_close');
        $supported_protocols = array('http', 'https');

        return extension_loaded('curl')
               && count(array_intersect(get_extension_funcs('curl'), $used_functions)) === count($used_functions)
               && count(array_intersect(get_curl_protocols(), $supported_protocols)) === count($supported_protocols);
    }

    protected function post($url, $request_headers, $data, $timeout, $http_proxy = null)
    {
        // https://github.com/WOVNio/equalizer/issues/19751
        // If body size is over 1024 bytes, cURL will add 'Expect: 100-continue' header automatically.
        // And wait until the response from html-swapper is returned.
        // This takes always 1[s].
        // So, it is better tp disable 'Expect: 100-continue'.
        array_push($request_headers, 'Expect:');

        $curl_session = curl_init($url);
        $curl_options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            // adds header to accept GZIP encoding and handles decoding response
            CURLOPT_ENCODING => 'gzip',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => $request_headers
        );

        if ($http_proxy) {
            $curl_options[CURLOPT_PROXY] = $http_proxy;
        }

        curl_setopt_array($curl_session, $curl_options);

        $response = curl_exec($curl_session);
        $header_size = curl_getinfo($curl_session, CURLINFO_HEADER_SIZE);
        $headers = $response ? explode("\r\n", substr($response, 0, $header_size)) : array();
        $parsedHeaders = HTTPHeaderParser::parseRawResponse($response, $header_size);

        if ($parsedHeaders) {
            $requestUUID = array_key_exists('X-Request-Id', $parsedHeaders) ? $parsedHeaders['X-Request-Id'] : 'NO_UUID';
            $status = array_key_exists('status', $parsedHeaders) ? $parsedHeaders['status'] : 'STATUS_UNKNOWN';
            Logger::get()->info("[{$requestUUID}] API call to html-swapper finished: {$status}.");
        }

        if (curl_error($curl_session) !== '') {
            $curl_error_code = curl_errno($curl_session);
            $http_error_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE);

            curl_close($curl_session);

            return array(null, $headers, "[cURL] Request failed ($curl_error_code-$http_error_code).");
        }

        $response_body = substr($response, $header_size);

        curl_close($curl_session);

        return array($response_body, $headers, null);
    }
}
