<?php
namespace Wovnio\Wovnwp\WpClasses;

use Wovnio\Wovnwp\Constants;

class Lang
{
    /**
     * Provides the ISO639-1 code for a given lang code.
     * Source: https://support.google.com/webmasters/answer/189077?hl=en
     *
     * @param String $lang_code Code of the language.
     *
     * @return String The ISO639-1 code of the language.
     */
    public static function iso639_1_normalization($lang_code)
    {
        if (isset(Constants::$langs[$lang_code])) {
            $code = array('zh-CHT', 'zh-CHS');
            $iso6391 = array('zh-Hant', 'zh-Hans');
            return str_replace($code, $iso6391, $lang_code);
        } else {
            return null;
        }
    }

    public static function get_code($lang_name = null)
    {
        if ($lang_name === null) {
            return null;
        }
        if (isset(Constants::$langs[$lang_name])) {
            return $lang_name;
        }
        foreach (Constants::$langs as $lang_code => $lang) {
            $lower_lang_name = strtolower($lang_name);
            if ($lower_lang_name === strtolower($lang['name']) ||
                    $lower_lang_name === strtolower($lang['en']) ||
                    $lower_lang_name === strtolower($lang['code']) ||
                    $lower_lang_name === strtolower(self::iso639_1_normalization($lang_code))
            ) {
                    return $lang['code'];
            }
        }
        return null;
    }

    public static function is_supported_lang($target_lang, $supported_langs)
    {
        return (isset(Constants::$langs[$target_lang]) && in_array($target_lang, $supported_langs));
    }

    public static function convert_to_custom_alias($lang_code, $lang_code_aliases)
    {
        if (isset($lang_code_aliases[$lang_code])) {
            return $lang_code_aliases[$lang_code];
        }
        return $lang_code;
    }

    public static function convert_to_original_code($lang_identifier, $lang_code_aliases)
    {
        if (empty($lang_code_aliases)) {
            return self::get_code($lang_identifier);
        }

        foreach ($lang_code_aliases as $lang_code => $lang_alias) {
            if (strtolower($lang_identifier) === strtolower($lang_alias)) {
                return self::get_code($lang_code);
            }
        }

        return $lang_identifier;
    }
}
