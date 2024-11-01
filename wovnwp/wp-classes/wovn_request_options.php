<?php
namespace Wovnio\Wovnwp\WpClasses;

require_once('wovn_options/request_handler_option_wrapper.php');
require_once('lang.php');
require_once(__DIR__ . '/../constants.php');

use Wovnio\Wovnwp\Constants;

class WovnRequestOptions
{

    /*
     * wovn_disable:
     *      - do nothing to the request
     */
    private $wovn_disable;

    /*
     * cacheDisableMode:
     *      - bypass cache for request to translation API
     * Only available if WOVN DEBUG MODE is also turned on in admin settings.
     */
    private $wovn_cache_disable;

    /*
     * debugMode:
     *      - activate extra debugging information.
     *      - send "debugMode=true" to translation API
     *      - bypass cache for request to translation API
     * Only available if WOVN DEBUG MODE is also turned on in admin settings.
     */
    private $wovn_debug_request;

    public function __construct($query_string_array, $wovn_debug_mode)
    {
        $this->wovn_disable = false;
        $this->wovn_cache_disable = false;
        $this->wovn_debug_request = false;

        if ($query_string_array !== null) {
            $this->wovn_disable = array_key_exists(Constants::$wovn_disable_param, $query_string_array) && strcasecmp($query_string_array['wovnDisable'], 'false') !== 0;
            if ($wovn_debug_mode) {
                $this->wovn_cache_disable = array_key_exists('wovnCacheDisable', $query_string_array) && strcasecmp($query_string_array['wovnCacheDisable'], 'false') !== 0;
                $this->wovn_debug_request = array_key_exists('wovnDebugMode', $query_string_array) && strcasecmp($query_string_array['wovnDebugMode'], 'false') !== 0;
            }
        }
    }

    public function getDisableMode()
    {
        return $this->wovn_disable;
    }

    public function getCacheDisableMode()
    {
        return $this->wovn_cache_disable;
    }

    public function getWovnDebugMode()
    {
        return $this->wovn_debug_request;
    }
}
