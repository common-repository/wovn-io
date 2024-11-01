<?php
namespace Wovnio\Wovnwp;

require_once('constants.php');
require_once('wp-classes/redirection_handler.php');
require_once('wp-classes/wovn_options.php');

class UrlRewrite
{
    private $wovn_options;
    /**
     * Start up
     */
    public function __construct(&$localization_decider)
    {
        $this->wovn_options = WpClasses\WovnOptions::get_instance();
        $redirection_handler = new WpClasses\RedirectionHandler($this->wovn_options->get_all(), $localization_decider);

        add_action('init', array( $this, 'add_wovn_rewrite_rule' ));
        add_action('plugins_loaded', array( $this, 'redirect_admin_lang_path' ), 0);
        if ($redirection_handler->is_active() && $localization_decider->can_intercept()) {
            add_action('plugins_loaded', array($redirection_handler, 'audit_headers_and_transform_redirection'), 0);
            add_action('wp_redirect', array($redirection_handler, 'transform_redirect_url'), 0);
        }
        // If you want to check rewrite_rules, Use below.
        // add_filter('mod_rewrite_rules', array( $this, 'check_htaccess' ));
    }

    function add_wovn_rewrite_rule()
    {
        if ($this->wovn_options->get('wovn_url_pattern_name') == 'path') {
            $lang_path_regexp = '/?(?:' . join('|', $this->wovn_options->get('wovn_supported_langs')) . ')($|/.*$)';
            add_rewrite_rule(
                $lang_path_regexp,
                'index.php$1',
                'top'
            );
        }
    }

    // If admin url has lang path, redirect to original url
    function redirect_admin_lang_path()
    {
        if (preg_match('/\/(' . join('|', array_keys(Constants::$langs)) .')\/wp-admin(\/|$)/i', $_SERVER['REQUEST_URI'])) {
            $actual_link = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $lang_path_regexp = '/\/(' . join('|', array_keys(Constants::$langs)) .')(\/|$)/i';
            $redirect_url = preg_replace($lang_path_regexp, '/', $actual_link, 1);

            // don't use wp_redirect to avoid infinite loop with our wp_redirect
            // handler
            header("Location: {$redirect_url}");
            exit;
        }
    }

    //
    // @TODO 納品時には消す
    //
    function check_htaccess($rules)
    {
        print_r("mod_rewrite_rules =>" . $rules);
        return $rules;
    }
}
