<?php
namespace Wovnio\Wovnwp\WpClasses;

require_once('plugin_detector.php');
require_once('wovn_request_options.php');


class LocalizationDecider
{
    private $plugin_detector;
    private $wovn_request_options;

    public function __construct(&$plugin_detector, $wovn_request_options)
    {
        $this->plugin_detector = $plugin_detector;
        $this->wovn_request_options = $wovn_request_options;
    }

    /**
     * Tells if it is possible to intercept the output buffer. The conditions to
     * check if interception is possible care only about basic Wordpress
     * information, so this function can be called before every plugin is loaded.
     * Because some plugins add rules regarding swapping pages, `can_intercept`
     * will return `true` even if those plugins are present. Once every plugins are
     * loaded, `can_swap` should be called for final check.
     *
     * @return Boolean `true` if the output buffer can be intercepted, `false`
     *                 otherwise.
     */
    public function can_intercept()
    {
        if ($this->wovn_request_options->getDisableMode()) {
            return false;
        }

        $can_check_json_request = function_exists('wp_is_json_request') ||
                                  function_exists('Wovnio\Wovnwp\WpClasses\wp_is_json_request'); // test case

        return !is_admin() && !($can_check_json_request && wp_is_json_request());
    }

    /**
     * Tells if it is possible to swap the content of the output buffer. The
     * conditions to check if swapping is possible requires every plugin to be
     * loaded. This function should then be called once the output buffer as been
     * created and is ready to be served.
     *
     * @param   request The original request variables ($_SERVER) before WOVN
     *                information were stipped off.
     *
     * @return Boolean `true` if the content of the output buffer can be swapped,
     *                 `false` otherwise.
     */
    public function can_swap($request)
    {
        return !$this->is_amp($request) && !$this->is_all_in_one_calendar($request);
    }

    public function can_change_url($url)
    {
        return !$this->is_admin_url($url);
    }

    private function is_amp($request)
    {
        if ($this->plugin_detector->amp_plugin_detected()) {
            return preg_match('/\/amp\/?$/', $request['REQUEST_URI']) ||
                   preg_match('/(^|\?|&)amp=1(&|#|$)/', $request['QUERY_STRING']);
        }

        return false;
    }

    private function is_all_in_one_calendar($request)
    {
        if ($this->plugin_detector->all_in_one_calendar_plugin_detected()) {
            return preg_match('/ai1ec_render_js/', $request['QUERY_STRING']);
        }

        return false;
    }

    private function is_admin_url($url)
    {
        return preg_match('/\/wp-admin(\/|$)/i', $url);
    }
}
