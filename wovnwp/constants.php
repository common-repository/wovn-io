<?php
namespace Wovnio\Wovnwp;

class Constants
{
    static $product_name = 'WOVNWP';
    static $product_version = '1.11.0';
    static $wovn_api_url = 'https://wovn.global.ssl.fastly.net';
    static $widget_url = 'https://j.wovn.io/1';
    /**
     * The lang class contains the langs supported by wovn in this form
     */
    static $langs = array(
        'ar' => array('name' => 'العربية', 'code' => 'ar', 'en' => 'Arabic'),
        'eu' => array('name' => 'Euskara', 'code' => 'eu', 'en' => 'Basque'),
        'bn' => array('name' => 'বাংলা ভাষা', 'code' => 'bn', 'en' => 'Bengali'),
        'bg' => array('name' => 'Български', 'code' => 'bg', 'en' => 'Bulgarian'),
        'ca' => array('name' => 'Català', 'code' => 'ca', 'en' => 'Catalan'),
        'zh-CN' => array('name' => '简体中文（中国）', 'code' => 'zh-CN', 'en' => 'Simplified Chinese (China)'),
        'zh-CHS' => array('name' => '简体中文', 'code' => 'zh-CHS', 'en' => 'Simplified Chinese'),
        'zh-Hant-HK' => array('name' => '繁體中文（香港）', 'code' => 'zh-Hant-HK', 'en' => 'Traditional Chinese (Hong Kong)'),
        'zh-Hant-TW' => array('name' => '繁體中文（台湾）', 'code' => 'zh-Hant-TW', 'en' => 'Traditional Chinese (Taiwan)'),
        'zh-CHT' => array('name' => '繁體中文', 'code' => 'zh-CHT', 'en' => 'Traditional Chinese'),
        'da' => array('name' => 'Dansk', 'code' => 'da', 'en' => 'Danish'),
        'nl' => array('name' => 'Nederlands', 'code' => 'nl', 'en' => 'Dutch'),
        'en' => array('name' => 'English', 'code' => 'en', 'en' => 'English'),
        'en-AU' => array('name' => 'English (Australia)', 'code' => 'en-AU', 'en' => 'English (Australia)'),
        'en-CA' => array('name' => 'English (Canada)', 'code' => 'en-CA', 'en' => 'English (Canada)'),
        'en-IN' => array('name' => 'English (India)', 'code' => 'en-IN', 'en' => 'English (India)'),
        'en-NZ' => array('name' => 'English (New Zealand)', 'code' => 'en-NZ', 'en' => 'English (New Zealand)'),
        'en-ZA' => array('name' => 'English (South Africa)', 'code' => 'en-ZA', 'en' => 'English (South Africa)'),
        'en-GB' => array('name' => 'English (United Kingdom)', 'code' => 'en-GB', 'en' => 'English (United Kingdom)'),
        'en-SG' => array('name' => 'English (Singapore)', 'code' => 'en-SG', 'en' => 'English (Singapore)'),
        'en-US' => array('name' => 'English (United States)', 'code' => 'en-US', 'en' => 'English (United States)'),
        'fi' => array('name' => 'Suomi', 'code' => 'fi', 'en' => 'Finnish'),
        'fr' => array('name' => 'Français', 'code' => 'fr', 'en' => 'French'),
        'fr-CA' => array('name' => 'Français (Canada)', 'code' => 'fr-CA', 'en' => 'French (Canada)'),
        'fr-FR' => array('name' => 'Français (France)', 'code' => 'fr-FR', 'en' => 'French (France)'),
        'fr-CH' => array('name' => 'Français (Suisse)', 'code' => 'fr-CH', 'en' => 'French (Switzerland)'),
        'gl' => array('name' => 'Galego', 'code' => 'gl', 'en' => 'Galician'),
        'de' => array('name' => 'Deutsch', 'code' => 'de', 'en' => 'German'),
        'de-AT' => array('name' => 'Deutsch (Österreich)', 'code' => 'de-AT', 'en' => 'German (Austria)'),
        'de-DE' => array('name' => 'Deutsch (Deutschland)', 'code' => 'de-DE', 'en' => 'German (Germany)'),
        'de-LI' => array('name' => 'Deutsch (Liechtenstein)', 'code' => 'de-LI', 'en' => 'German (Liechtenstein)'),
        'de-CH' => array('name' => 'Deutsch (Schweiz)', 'code' => 'de-CH', 'en' => 'German (Switzerland)'),
        'el' => array('name' => 'Ελληνικά', 'code' => 'el', 'en' => 'Greek'),
        'he' => array('name' => 'עברית', 'code' => 'he', 'en' => 'Hebrew'),
        'hu' => array('name' => 'Magyar', 'code' => 'hu', 'en' => 'Hungarian'),
        'id' => array('name' => 'Bahasa Indonesia', 'code' => 'id', 'en' => 'Indonesian'),
        'it' => array('name' => 'Italiano', 'code' => 'it', 'en' => 'Italian'),
        'it-IT' => array('name' => 'Italiano (Italia)', 'code' => 'it-IT', 'en' => 'Italian (Italy)'),
        'it-CH' => array('name' => 'Italiano (Svizzera)', 'code' => 'it-CH', 'en' => 'Italian (Switzerland)'),
        'ja' => array('name' => '日本語', 'code' => 'ja', 'en' => 'Japanese'),
        'ko' => array('name' => '한국어', 'code' => 'ko', 'en' => 'Korean'),
        'lv' => array('name' => 'Latviešu', 'code' => 'lv', 'en' => 'Latvian'),
        'mn' => array('name' => 'монгол', 'code' => 'mn', 'en' => 'Mongolian'),
        'ms' => array('name' => 'Bahasa Melayu', 'code' => 'ms', 'en' => 'Malay'),
        'my' => array('name' => 'ဗမာစာ', 'code' => 'my', 'en' => 'Burmese'),
        'ne' => array('name' => 'नेपाली भाषा', 'code' => 'ne', 'en' => 'Nepali'),
        'no' => array('name' => 'Norsk', 'code' => 'no', 'en' => 'Norwegian'),
        'fa' => array('name' => 'زبان_فارسی', 'code' => 'fa', 'en' => 'Persian'),
        'pl' => array('name' => 'Polski', 'code' => 'pl', 'en' => 'Polish'),
        'pt' => array('name' => 'Português', 'code' => 'pt', 'en' => 'Portuguese'),
        'pt-BR' => array('name' => 'Português (Brasil)', 'code' => 'pt-BR', 'en' => 'Portuguese (Brazil)'),
        'pt-PT' => array('name' => 'Português (Portugal)', 'code' => 'pt-PT', 'en' => 'Portuguese (Portugal)'),
        'ru' => array('name' => 'Русский', 'code' => 'ru', 'en' => 'Russian'),
        'es' => array('name' => 'Español', 'code' => 'es', 'en' => 'Spanish'),
        'es-AR' => array('name' => 'Español (Argentina)', 'code' => 'es-AR', 'en' => 'Spanish (Argentina)'),
        'es-CL' => array('name' => 'Español (Chile)', 'code' => 'es-CL', 'en' => 'Spanish (Chile)'),
        'es-CO' => array('name' => 'Español (Colombia)', 'code' => 'es-CO', 'en' => 'Spanish (Colombia)'),
        'es-CR' => array('name' => 'Español (Costa Rica)', 'code' => 'es-CR', 'en' => 'Spanish (Costa Rica)'),
        'es-HN' => array('name' => 'Español (Honduras)', 'code' => 'es-HN', 'en' => 'Spanish (Honduras)'),
        'es-419' => array('name' => 'Español (Latinoamérica)', 'code' => 'es-419', 'en' => 'Spanish (Latin America)'),
        'es-MX' => array('name' => 'Español (México)', 'code' => 'es-MX', 'en' => 'Spanish (Mexico)'),
        'es-PE' => array('name' => 'Español (Perú)', 'code' => 'es-PE', 'en' => 'Spanish (Peru)'),
        'es-ES' => array('name' => 'Español (España)', 'code' => 'es-ES', 'en' => 'Spanish (Spain)'),
        'es-US' => array('name' => 'Español (Estados Unidos)', 'code' => 'es-US', 'en' => 'Spanish (United States)'),
        'es-UY' => array('name' => 'Español (Uruguay)', 'code' => 'es-UY', 'en' => 'Spanish (Uruguay)'),
        'es-VE' => array('name' => 'Español (Venezuela)', 'code' => 'es-VE', 'en' => 'Spanish (Venezuela)'),
        'sw' => array('name' => 'Kiswahili', 'code' => 'sw', 'en' => 'Swahili'),
        'sv' => array('name' => 'Svensk', 'code' => 'sv', 'en' => 'Swedish'),
        'tl' => array('name' => 'Tagalog', 'code' => 'tl', 'en' => 'Tagalog'),
        'th' => array('name' => 'ภาษาไทย', 'code' => 'th', 'en' => 'Thai'),
        'hi' => array('name' => 'हिन्दी', 'code' => 'hi', 'en' => 'Hindi'),
        'tr' => array('name' => 'Türkçe', 'code' => 'tr', 'en' => 'Turkish'),
        'uk' => array('name' => 'Українська', 'code' => 'uk', 'en' => 'Ukrainian'),
        'ur' => array('name' => 'اردو', 'code' => 'ur', 'en' => 'Urdu'),
        'uz' => array('name' => 'Oʻzbekcha', 'code' => 'uz', 'en' => 'Uzbek'),
        'vi' => array('name' => 'Tiếng Việt', 'code' => 'vi', 'en' => 'Vietnamese'),
        'km' => array('name' => 'ភាសាខ្មែរ', 'code' => 'km', 'en' => 'Khmer'),
        'ta' => array('name' => 'தமிழ்', 'code' => 'ta', 'en' => 'Tamil'),
        'si' => array('name' => 'සිංහල', 'code' => 'si', 'en' => 'Sinhala')
    );

    static $url_pattern_names = array('query', 'path', 'subdomain');
    static $default_lang_param_name = 'wovn';
    static $default_translation_timeout = 1;
    static $default_wovn_api_timeout_search_engine_bots = 5;
    static $wovn_cache_disable_param = 'wovnCacheDisable';
    static $wovn_debug_mode_query_param = 'wovnDebugMode';
    static $wovn_disable_param = 'wovnDisable';
}
