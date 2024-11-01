<?php
namespace Wovnio\Wovnwp\WpClasses;

require_once('lang.php');

use Wovnio\Wovnwp\Constants;

class Url
{
    /**
     * Adds a language code to a uri.
     *
     * @param string  $absolute_url     The uri to modify, assuming there is no lang code.
     * @param string $url_pattern
     * @param string  $lang_code    The language code to add to the uri.
     *
     * @return string The new uri containing the language code.
     */
    public static function add_lang_code($absolute_url, $lang_code, $options)
    {
        $default_lang = $options['wovn_default_lang'];
        $url_pattern = $options['wovn_url_pattern_name'];
        $lang_param_name = $options['wovn_lang_param_name'];
        $lang_code_aliases = $options['wovn_lang_code_aliases'];

        if (!$lang_code || strlen($lang_code) == 0) {
            return $absolute_url;
        }

        if ($default_lang === $lang_code) {
            return $absolute_url;
        }

        $parsed_url = parse_url($absolute_url);
        // On seriously malformed URLs, parse_url() may return FALSE. (php doc)
        if (!$parsed_url || !array_key_exists('host', $parsed_url)) {
            return $absolute_url;
        }

        $lang_identifier = Lang::convert_to_custom_alias($lang_code, $lang_code_aliases);
        switch ($url_pattern) {
            case 'subdomain':
                $new_uri = preg_replace('/(\/\/)([^\.]*)/', '${1}' . self::format_for_reg_exp(strtolower($lang_identifier)) . '.' . '${2}', $absolute_url, 1);
                break;
            case 'query':
                $new_uri = self::add_query_lang_code($absolute_url, $lang_identifier, $lang_param_name);
                break;
            default:
                //path
                $new_uri = preg_replace('/([^\.]*\.[^?\/]*)(\?|\/|$)/', '${1}/' . self::format_for_reg_exp($lang_identifier) . '${2}', $absolute_url, 1);
        }

        return $new_uri;
    }

    public static function path_lang_code($absolute_url, $options)
    {
        $default_lang = $options['wovn_default_lang'];
        $url_pattern = $options['wovn_url_pattern_name'];
        $lang_code_aliases = $options['wovn_lang_code_aliases'];
        $supported_langs = $options['wovn_supported_langs'];
        $lang_identifiers = $options['wovn_lang_identifiers'];

        $lang_identifieres_str = join('|', array_values($lang_identifiers));

        switch ($url_pattern) {
            case 'subdomain':
                preg_match('/(?:\/\/)?(' . $lang_identifieres_str .')\./i', $absolute_url, $matches);
                break;
            case 'query':
                $lang_param_name = $options['wovn_lang_param_name'];
                preg_match('/' . $lang_param_name . '=(' . $lang_identifieres_str .')(&|#|$)/', $absolute_url, $matches);
                break;
            default:
                //path
                $path_pattern_regex = '@' .
                    '^(?:.*://|//)?' . // 1: schema (optional) like https://
                    '(?:[^/?]*)?' . // 2: host (optional) like wovn.io
                    '/(' . $lang_identifieres_str . ')' . // 3: lang code
                    '(?:/|\?|#|$)' . // 4: path, query, hash or end-of-string like /dir2/?a=b#hash
                    '@';
                preg_match($path_pattern_regex, $absolute_url, $matches);
        }

        $lang_code = ($matches && $matches[1]) ? Lang::convert_to_original_code($matches[1], $lang_code_aliases) : $default_lang;
        return (Lang::is_supported_lang($lang_code, $supported_langs)) ? $lang_code : $default_lang;
    }

    private static function format_for_reg_exp($text)
    {
        return str_replace('$', '\$', str_replace("\\", "\\\\", $text));
    }

    /**
     * Adds a language code to a uri using query pattern.
     *
     * @param String  $uri     The uri to modify.
     * @param String  $lang_code    The language code to add to the uri.
     *
     * @return String The new uri containing the language code.
     */
    private static function add_query_lang_code($uri, $lang_code, $lang_param_name)
    {
        $sep = '?';
        if (preg_match('/\?/', $uri)) {
            $sep = '&';
        }
        $lang_param_and_code = $lang_param_name . '=' . $lang_code;
        if (strpos($uri, $lang_param_and_code) !== false) {
            return $uri;
        }
        return preg_replace('/(#|$)/', $sep . $lang_param_and_code . '${1}', $uri, 1);
    }
}
