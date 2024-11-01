<?php
namespace Wovnio\Wovnwp\WpClasses;

require_once('lang.php');
require_once('url.php');
require_once('logger.php');

use Wovnio\Wovnwp\Constants;
use Wovnio\Wovnwp\WpClasses\Logger;

global $wp_version;
if (defined('ABSPATH') && isset($wp_version) && version_compare($wp_version, '2.5', '>=')) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

class Swapper
{
    private $accessed_url;
    private $options;
    private $request_variables;
    private $request_handler;
    private $target_lang;
    private $original_url;
    private $headers_transformer;
    private $wovn_request_options;
    private $localization_decider;

    public function __construct($accessed_url, $options, $wovn_request_options, $request_variables, $request_handler, &$localization_decider, $headers_transformer = null)
    {
        $this->accessed_url = $accessed_url;
        $this->options = $options;
        $this->request_variables = $request_variables;
        $this->request_handler = $request_handler;
        $this->localization_decider = $localization_decider;
        $this->headers_transformer = $headers_transformer;
        $this->wovn_request_options = $wovn_request_options;
    }

    public function set_lang_and_original_url()
    {
        $changed_url = null;
        switch ($this->options['wovn_url_pattern_name']) {
            case 'query':
                $changed_url = $this->set_lang_and_original_url_by_query();
                break;
            case 'path':
                $changed_url = $this->set_lang_and_original_url_by_path();
                break;
            case 'subdomain':
                $changed_url = $this->set_lang_and_original_url_by_subdomain();
                break;
        }
        // Make it available for user application
        $_ENV['WOVN_TARGET_LANG'] = $this->target_lang;
        if ($this->wovn_request_options->getWovnDebugMode()) {
            $this->original_url = preg_replace('~(\?|&)'. Constants::$wovn_debug_mode_query_param . '[^&]*~', '', $this->original_url);
        }
        return $changed_url;
    }

    function set_lang_and_original_url_by_query()
    {
        $wovn_query = $this->request_variables["WOVN_QUERY"];
        $lang_code = Lang::convert_to_original_code($wovn_query, $this->options['wovn_lang_code_aliases']);

        if (isset($lang_code) && in_array($lang_code, $this->options['wovn_supported_langs'])) {
            $this->target_lang = $lang_code;
            $this->original_url = $this->remove_query($this->accessed_url, $this->options['wovn_lang_param_name']);
        } else {
            $this->target_lang = $this->options['wovn_default_lang'];
            $this->original_url = $this->accessed_url;
        }

        return null;
    }

    function set_lang_and_original_url_by_path()
    {
        $uri = $this->request_variables['REQUEST_URI'];
        $this->target_lang = URL::path_lang_code($uri, $this->options);

        if ($this->target_lang === $this->options['wovn_default_lang']) {
            $this->original_url = $this->accessed_url;
        } else {
            $lang_identifier = Lang::convert_to_custom_alias($this->target_lang, $this->options['wovn_lang_code_aliases']);
            $lang_path_regexp = '/\/' . $lang_identifier . '(\/|$)/i';
            $this->original_url = preg_replace($lang_path_regexp, '/', $this->accessed_url, 1);
            return preg_replace($lang_path_regexp, '/', $uri, 1);
        }

        return null;
    }

    function set_lang_and_original_url_by_subdomain()
    {
        $uri = $this->request_variables['HTTP_HOST'];
        $lang_identifieres = $this->options['wovn_lang_identifiers'];
        $lang_path_regexp = '/(' . join('|', $lang_identifieres) .')(\.)/i';
        if (preg_match($lang_path_regexp, $uri, $matches)) {
            $lang_identifier = $matches[1];
            $this->target_lang = Lang::convert_to_original_code($lang_identifier, $this->options['wovn_lang_code_aliases']);
            $this->original_url = preg_replace($lang_path_regexp, '', $this->accessed_url, 1);
        } else {
            $this->target_lang = $this->options['wovn_default_lang'];
            $this->original_url = $this->accessed_url;
        }

        return null;
    }

    function remove_query($url, $lang_param_name)
    {
        $query_without_url = preg_replace('/([?&])'.$lang_param_name.'=[^&#]+(&|$)?(#)?/', '$1$3', $url);
        return preg_replace('/(\?|&)($|#)/', '$2', $query_without_url);
    }

    public function validate()
    {
        if (!isset($this->target_lang) || !in_array($this->target_lang, $this->options['wovn_supported_langs'])) {
            $this->target_lang = $this->options['wovn_default_lang'];
        }
    }

    public function swap_html($body)
    {
        if (!($this->localization_decider->can_swap($this->request_variables))) {
            Logger::get()->info('The buffer is not swappable.');
            return $body;
        }

        Logger::get()->info('Target language is ' . $this->target_lang . '.');

        $converter = new HtmlConverter($body, $this->original_url, $this->options, $this->target_lang, $this->request_variables);

        if ($this->target_lang === $this->options['wovn_default_lang']) {
            return $converter->insert_snippet_and_lang_tags(false);
        }

        $body_with_fallback_snippet = $converter->insert_snippet_and_lang_tags(true);

        $result_html = $this->request_wovn_api(
            $this->original_url,
            $this->target_lang,
            $body_with_fallback_snippet,
            $this->options
        );

        if (isset($result_html)) {
            return $result_html;
        } else {
            return $body_with_fallback_snippet;
        }
    }

    function request_wovn_api($url, $lang_code, $body, $options)
    {
        if (!$this->request_handler) {
            return null;
        }

        $request_url = $this->build_api_url($options['wovn_user_token'], $lang_code, $body);
        $wp_debug = defined('WP_DEBUG') && true === WP_DEBUG;
        $data = array(
            'url' => $url,
            'token' => $options['wovn_user_token'],
            'lang_code' => $lang_code,
            'url_pattern' => $options['wovn_url_pattern_name'],
            'body' => $body,
            'debug_mode' => $wp_debug,
            'product' => Constants::$product_name,
            'version' => Constants::$product_version,
            'lang_param_name' => $options['wovn_lang_param_name'],
            'translate_canonical_tag' => $options['wovn_translate_canonical_tag'],
            'page_status_code' => http_response_code()
        );

        if (function_exists('http_response_code')) {
            $data['page_status_code'] = http_response_code();
        }

        if ($this->wovn_request_options->getWovnDebugMode()) {
            $data['debug_mode'] = true;
        }

        if (count($options['wovn_lang_code_aliases']) > 0) {
            $data['custom_lang_aliases'] = json_encode($options['wovn_lang_code_aliases']);
        }

        $api_timeout = $this->options['wovn_translation_timeout'];
        if ($this->isSearchEngineBot()) {
            $api_timeout = $this->options['wovn_api_timeout_search_engine_bots'];
        }

        list($result, $headers, $error) = $this->request_handler->send_request(
            'POST',
            $request_url,
            $data,
            $api_timeout,
            $this->options['wovn_http_proxy']
        );

        $data['body'] = "[HIDDEN]";
        Logger::get()->info("API call payload: " . json_encode($data));

        if (!$result) {
            Logger::get()->error('Empty API call result');
            if ($error) {
                Logger::get()->error('API call failed: ' . $error);
                header("X-Wovn-Error: $error");
            }

            return null;
        }

        $result_json = json_decode($result, true);

        if ($this->headers_transformer) {
            $this->headers_transformer->transform_and_apply_api_headers($headers);
        }

        return $result_json['body'];
    }

    function build_api_url($token, $lang_code, $body)
    {
        $path = parse_url($this->original_url, PHP_URL_PATH);
        ;
        $body_hash = md5($body);
        $setting = $this->options;
        ksort($setting);
        $settings_hash = md5(serialize($setting));
        $cache_key_raw = "(token=$token&settings_hash=$settings_hash&body_hash=$body_hash&path=$path&lang=$lang_code)";
        if ($this->wovn_request_options->getCacheDisableMode()) {
            $currentTime = round(microtime(true) * 1000);
            $cache_key_raw = $cache_key_raw . "&timestamp=$currentTime";
        }
        $cache_key = rawurlencode($cache_key_raw);
        $api_url = Constants::$wovn_api_url . '/v0/translation';
        return $api_url . '?cache_key=' . $cache_key;
    }

    private function generate_lang_identifieres($supported_langs, $lang_code_aliases)
    {
        $lang_identifieres = array();
        foreach ($supported_langs as $lang_code) {
            $lang_identifieres[$lang_code] = array_key_exists($lang_code, $lang_code_aliases) ? $lang_code_aliases[$lang_code] : $lang_code;
        }

        return $lang_identifieres;
    }

    private function isSearchEngineBot()
    {
        if (empty($this->request_variables['HTTP_USER_AGENT'])) {
            return false;
        }

        $bots = array(
            'Googlebot/',
            'bingbot/',
            'YandexBot/',
            'YandexWebmaster/',
            'DuckDuckBot-Https/',
            'Baiduspider/',
            'Slurp',
            'Yahoo'
        );
        foreach ($bots as $bot) {
            if (strpos($this->request_variables['HTTP_USER_AGENT'], $bot) !== false) {
                return true;
            }
        }
        return false;
    }
}
