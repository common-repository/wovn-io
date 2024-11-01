<?php
namespace Wovnio\Wovnwp\WpClasses;

require_once('url.php');

class RedirectionHandler
{
    private $target_lang = null;
    private $exit_on_redirection = false;

    private $options = null;
    private $localization_decider = null;

    public function __construct($options, $localization_decider, $exit_on_redirection = true)
    {
        $this->options = $options;
        $request_uri = null;
        if (isset($_SERVER["WOVN_REQUEST_URI"])) {
            $request_uri = $_SERVER["WOVN_REQUEST_URI"];
        } elseif (isset($_SERVER["REQUEST_URI"])) {
            $request_uri = $_SERVER["REQUEST_URI"];
        }

        if (isset($request_uri)) {
            $servr_name = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
            $this->target_lang = Url::path_lang_code($servr_name . $request_uri, $this->options);
            $this->exit_on_redirection = $exit_on_redirection;
            $this->localization_decider = $localization_decider;
        }
    }

    public function is_active()
    {
        return $this->options['wovn_url_pattern_name'] && $this->options['wovn_default_lang'] && $this->target_lang;
    }

    public function transform_redirect_url($url)
    {
        if (!$this->is_active() || !$this->localization_decider->can_change_url($url)) {
            return $url;
        }

        return Url::add_lang_code($url, $this->target_lang, $this->options);
    }

    public function audit_headers_and_transform_redirection()
    {
        if (!$this->is_active()) {
            return;
        }

        $response_headers = headers_list();

        foreach (array('location', 'Location') as $location_header) {
            if (array_key_exists($location_header, $response_headers)) {
                $redirect_url = $this->transform_redirect_url($response_headers[$location_header]);

                header("Location: {$redirect_url}");
                if ($this->exit_on_redirection) {
                    exit;
                }
            }
        }
    }
}
