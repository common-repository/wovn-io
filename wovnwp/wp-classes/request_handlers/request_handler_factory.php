<?php
namespace Wovnio\Wovnwp\WpClasses\RequestHandlers;

require_once('curl_request_handler.php');
require_once('file_get_contents_request_handler.php');
require_once(dirname(__FILE__) . '/../wovn_options/request_handler_option_wrapper.php');

use \Wovnio\Wovnwp\WpClasses\WovnOptions\RequestHandlerOptionWrapper;

class RequestHandlerFactory
{
    public static function get_best_available_request_handler($wovn_options)
    {
        $request_handler = null;
        $request_handler_option_wrapper = new RequestHandlerOptionWrapper($wovn_options['wovn_request_handler']);

        if ($request_handler_option_wrapper->use_curl() && CurlRequestHandler::available()) {
            $request_handler = new CurlRequestHandler();
        } elseif ($request_handler_option_wrapper->use_file_get_contents() && FileGetContentsRequestHandler::available()) {
            $request_handler = new FileGetContentsRequestHandler();
        }

        return $request_handler;
    }
}
