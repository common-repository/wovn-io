<?php
namespace Wovnio\Wovnwp\WpClasses;

require_once('wovn_options/request_handler_option_wrapper.php');
require_once('lang.php');

use Wovnio\Wovnwp\Constants;
use \Wovnio\Wovnwp\WpClasses\WovnOptions\RequestHandlerOptionWrapper;

class WovnOptions
{
    private static $instance;
    private $options;

    private function __construct()
    {
        $this->options = get_option('wovn_options');
        $this->merge_default();
    }

    public static function get_instance()
    {
        if (empty(self::$instance)) {
            self::$instance = new WovnOptions();
        }
        return self::$instance;
    }

    public function merge_default()
    {
        if (empty($this->options['wovn_user_token'])) {
            $this->options['wovn_user_token'] = '';
        }

        if (empty($this->options['wovn_supported_langs'])) {
            $this->options['wovn_supported_langs'] = array();
        }

        if (empty($this->options['wovn_default_lang'])) {
            $this->options['wovn_default_lang'] = Constants::$langs['en']['code'];
        }

        if (empty($this->options['wovn_url_pattern_name'])) {
            $this->options['wovn_url_pattern_name'] = Constants::$url_pattern_names[0];
        }

        if (empty($this->options['wovn_lang_param_name'])) {
            $this->options['wovn_lang_param_name'] = Constants::$default_lang_param_name;
        }

        if (empty($this->options['wovn_translation_timeout'])) {
            $this->options['wovn_translation_timeout'] = Constants::$default_translation_timeout;
        } else {
            $this->options['wovn_translation_timeout'] = intval($this->options['wovn_translation_timeout']);
        }

        if (empty($this->options['wovn_api_timeout_search_engine_bots'])) {
            $this->options['wovn_api_timeout_search_engine_bots'] = Constants::$default_wovn_api_timeout_search_engine_bots;
        } else {
            $this->options['wovn_api_timeout_search_engine_bots'] = intval($this->options['wovn_api_timeout_search_engine_bots']);
        }

        if (empty($this->options['wovn_request_handler'])) {
            $this->options['wovn_request_handler'] = RequestHandlerOptionWrapper::RH_ALL;
        }

        if (empty($this->options['wovn_http_proxy'])) {
            $this->options['wovn_http_proxy'] = null;
        }

        if (empty($this->options['wovn_lang_code_aliases'])) {
            $this->options['wovn_lang_code_aliases'] = array();
        }

        if (empty($this->options['wovn_logging_enabled'])) {
            $this->options['wovn_logging_enabled'] = false;
        }

        if (empty($this->options['wovn_log_file_path'])) {
            $this->options['wovn_log_file_path'] = '';
        }

        if (empty($this->options['wovn_widget_url'])) {
            $this->options['wovn_widget_url'] = Constants::$widget_url;
        }

        if (empty($this->options['wovn_translate_canonical_tag'])) {
            $this->options['wovn_translate_canonical_tag'] = true;
        }

        if (empty($this->options['wovn_debug_mode'])) {
            $this->options['wovn_debug_mode'] = false;
        }

        $this->options['wovn_lang_identifiers'] = array();

        $lang_code_aliases = $this->options['wovn_lang_code_aliases'];
        foreach (array_keys(Constants::$langs) as $lang_code) {
            $this->options['wovn_lang_identifiers'][$lang_code] = array_key_exists($lang_code, $lang_code_aliases) ? Lang::convert_to_custom_alias($lang_code, $lang_code_aliases) : $lang_code;
        }
        Logger::set(new Logger($this->options['wovn_user_token']));
    }

    public function get_all()
    {
        return $this->options;
    }

    public function get($param_name)
    {
        return $this->options[$param_name];
    }

    public function destroy()
    {
        $this->options = null;
        self::$instance = null;
    }
}
