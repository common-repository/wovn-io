<?php
namespace Wovnio\Wovnwp\WpClasses;

use Wovnio\Wovnwp\Constants;

class HtmlConverter
{
    private $html;
    private $accessed_url;
    private $options;
    private $target_lang;
    private $wovn_widget_urls;
    private $request_variables;

    public function __construct($html, $accessed_url, $options, $target_lang, $request_variables)
    {
        $this->html = $html;
        $this->accessed_url = $accessed_url;
        $this->options = $options;
        $this->target_lang = $target_lang;
        $this->request_variables = $request_variables;
        $this->wovn_widget_urls = array("j.wovn.io", "j.dev-wovn.io:3000", Constants::$wovn_api_url . '/widget');
    }

    public function insert_snippet_and_lang_tags($adds_backend_error_mark)
    {
        $this->html = $this->insert_snippet($this->html, $adds_backend_error_mark);
        $this->html = $this->insert_hreflang_tags($this->html);
        $this->html = $this->insert_html_lang_attribute($this->html, $this->options['wovn_default_lang']);

        if ($this->options['wovn_translate_canonical_tag']) {
            $this->html = $this->translate_canonical_tag($this->html);
        }

        return $this->html;
    }

    /**
     * Insert wovn's snippet to ensure snippet is always inserted.
     * If snippet is already inserted, replace it
     *
     * @param string $html
     * @return string
     */
    private function insert_snippet($html, $adds_backend_error_mark)
    {
        $html = $this->remove_snippet($html);

        $snippet_code = $this->build_snippet_code($adds_backend_error_mark);
        $parent_tags = array("(<head\s?.*?>)", "(<body\s?.*?>)", "(<html\s?.*?>)");

        return $this->insert_after_tag($parent_tags, $html, $snippet_code);
    }

    private function remove_snippet($html)
    {
        $snippet_regex = '@' .
        '<script[^>]*' . // open tag
        '(' .
        'src=\"[^">]*(' . implode("|", $this->wovn_widget_urls) . ')[^">]*\"' . // src attribute
        '|' .
        'data-wovnio=\"[^">]+?\"' . // data-wovnio attribute
        ')' .
        '[^>]*><\/script>' . // close tag
        '@';
        return $this->remove_tag_from_html_by_regex($html, $snippet_regex);
    }

    private function insert_after_tag($tag_names, $html, $insert_str)
    {
        foreach ($tag_names as $tag_name) {
            if (preg_match($tag_name, $html, $matches, PREG_OFFSET_CAPTURE)) {
                return substr_replace($html, $insert_str, $matches[0][1] + strlen($matches[0][0]), 0);
            }
        }

        return $html;
    }

    private function remove_tag_from_html_by_regex($html, $regex)
    {
        $result = $html;

        if (preg_match_all($regex, $result, $matches, PREG_OFFSET_CAPTURE)) {
            for ($i = count($matches[0]) - 1; $i >= 0; --$i) {
                $match = $matches[0][$i];
                $result = substr_replace($result, '', $match[1], strlen($match[0]));
            }
        }

        return $result;
    }

    private function build_snippet_code($adds_backend_error_mark)
    {
        // params which used by widget
        $data_wovnio_params = array();
        $data_wovnio_params['key'] = $this->options['wovn_user_token'];
        $data_wovnio_params['backend'] = 'true';
        $data_wovnio_params['currentLang'] = $this->target_lang;
        $data_wovnio_params['defaultLang'] = $this->options['wovn_default_lang'];
        $data_wovnio_params['urlPattern'] = $this->options['wovn_url_pattern_name'];
        $data_wovnio_params['langCodeAliases'] = json_encode($this->options['wovn_lang_code_aliases']);
        $data_wovnio_params['debugMode'] = defined('WP_DEBUG') && true === WP_DEBUG ? 'true' : 'false';
        $data_wovnio_params['langParamName'] = $this->options['wovn_lang_param_name'];

        // params for debug
        $supported_langs = is_array($this->options['wovn_supported_langs']) ? $this->options['wovn_supported_langs'] : array();
        $data_wovnio_info_params = array();
        $data_wovnio_info_params['version'] = "WOVN.wp_" . Constants::$product_version;
        $data_wovnio_info_params['supportedLangs'] = "[" . implode(',', $supported_langs) . "]";
        $data_wovnio_info_params['timeout'] = $this->options['wovn_translation_timeout'];

        $data_wovnio = htmlentities($this->build_params_str($data_wovnio_params));
        $data_wovnio_info = htmlentities($this->build_params_str($data_wovnio_info_params));
        $data_wovnio_type = $adds_backend_error_mark ? 'data-wovnio-type="fallback"' : '';
        $widget_url = $this->options['wovn_widget_url'];

        return "<script src=\"$widget_url\" data-wovnio=\"$data_wovnio\" data-wovnio-info=\"$data_wovnio_info\" $data_wovnio_type async></script>";
    }

    private function build_params_str($params_array)
    {
        $params = array();
        foreach ($params_array as $key => $value) {
            $param_str = "$key=$value";
            array_push($params, $param_str);
        }
        return implode('&', $params);
    }

    /**
     * Insert hreflang tags for all supported_langs
     *
     * @param string $html
     * @return string
     */
    private function insert_hreflang_tags($html)
    {
        if (isset($this->options['wovn_supported_langs'])) {
            if (is_array($this->options['wovn_supported_langs'])) {
                $lang_codes = $this->options['wovn_supported_langs'];
            } else {
                $lang_codes = array($this->options['wovn_supported_langs']);
            }
        } else {
            $lang_codes = array();
        }

        $lang_codes_with_pipe = implode('|', $lang_codes);
        $hreflang_regex = "/<link[^>]*hreflang=[^>]*($lang_codes_with_pipe)[^>]*\>/iU";
        $html = $this->remove_tag_from_html_by_regex($html, $hreflang_regex);
        $url_pattern = $this->options['wovn_url_pattern_name'];
        $default_lang = $this->options['wovn_default_lang'];
        $lang_param_name = $this->options['wovn_lang_param_name'];

        $hreflangTags = array();
        foreach ($lang_codes as $lang_code) {
            $href = htmlentities(Url::add_lang_code($this->accessed_url, $lang_code, $this->options));
            array_push($hreflangTags, '<link rel="alternate" hreflang="' . Lang::iso639_1_normalization($lang_code) . '" href="' . $href . '">');
        }

        $parent_tags = array("(<head\s?.*?>)", "(<body\s?.*?>)", "(<html\s?.*?>)");

        return $this->insert_after_tag($parent_tags, $html, implode('', $hreflangTags));
    }

    /**
     * Insert canonical tag for the current lang
     *
     * @param string $html
     * @return string
     */
    private function translate_canonical_tag($html)
    {
        $canonical_tag_regex = "/(<link[^>]*rel=\"canonical\"[^>]*href=\")([^\"]*)(\"[^>]*>)/";
        preg_match($canonical_tag_regex, $html, $matches);
        if (count($matches) < 4) {
            return $html;
        }
        $original_canonical_url = $matches[2];

        // TODO: make Url::add_lang_code able to determine host so we don't need this
        if (parse_url($original_canonical_url, PHP_URL_HOST) !=  $this->request_variables['HTTP_HOST'] && !empty($this->request_variables['HTTP_HOST'])) {
            return $html;
        }

        $translated_canonical_url = htmlentities(Url::add_lang_code($original_canonical_url, $this->target_lang, $this->options));
        $replacement = '\1' . $translated_canonical_url . '\3';
        return preg_replace($canonical_tag_regex, $replacement, $html);
    }

    private function insert_html_lang_attribute($html, $lang_code)
    {
        if (preg_match('/<html\s?.*?>/', $html, $matches)) {
            $html_open_tag = $matches[0];
            if (preg_match('/lang=["\']?[a-zA-Z-]*["\']?/', $html_open_tag)) {
                return $html;
            }
            $replacement = $html_open_tag;
            $replacement = str_replace('<html', "<html lang=\"$lang_code\"", $replacement);
            return str_replace($html_open_tag, $replacement, $html);
        }
        return $html;
    }
}
