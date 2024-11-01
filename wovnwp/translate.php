<?php
namespace Wovnio\Wovnwp;

require_once('constants.php');
require_once('wp-classes/swapper.php');
require_once('wp-classes/utils/content_type.php');
require_once('wp-classes/request_handlers/request_handler_factory.php');
require_once('wp-classes/headers_transformer.php');
require_once('wp-classes/html_converter.php');
require_once('wp-classes/url.php');
require_once('wp-classes/lang.php');
require_once('wp-classes/wovn_options.php');

use Wovnio\Wovnwp\WpClasses\Logger;
use Wovnio\Wovnwp\WpClasses\Utils\ContentType;
use Wovnio\Wovnwp\WpClasses\RequestHandlers\RequestHandlerFactory;
use Wovnio\Wovnwp\WpClasses\HeadersTransformer;
use finfo;

class Translate
{
    private $ob_level;
    private $swapper;

    public function __construct(&$localization_decider, $wovn_request_options)
    {
        $this->ob_level = PHP_INT_MAX;

        // Multiple site wordpress needs the correct `$_SERVER["REQUEST_URI"]` to get the
        // blog_id (site id), and this happen before the request is intercepted by the plugin
        // So we strip language part from  `$_SERVER["REQUEST_URI"]` in wordpress's `index.php`,
        // where request handling start and keep the original in `$_SERVER["WOVN_REQUEST_URI"]`
        // to use later in the translation process
        if (isset($_SERVER["WOVN_REQUEST_URI"])) {
            $request_uri = $_SERVER["WOVN_REQUEST_URI"];
        } else {
            $request_uri = isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : '';
        }

        $accessed_url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $request_uri;
        $wovn_options = WpClasses\WovnOptions::get_instance()->get_all();
        $lang_param_name = $wovn_options['wovn_lang_param_name'];
        $request_variables = array(
            "HTTP_HOST" => isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : '',
            "REQUEST_URI" => $request_uri,
            "QUERY_STRING" => isset($_SERVER["QUERY_STRING"]) ? $_SERVER["QUERY_STRING"] : '',
            "WOVN_QUERY" => isset($_GET[$lang_param_name]) ? $_GET[$lang_param_name] : '',
            "HTTP_USER_AGENT" => isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : '',
        );

        $wovn_version = Constants::$product_version;
        WpClasses\Logger::get()->info("WOVN WordPress Plugin version {$wovn_version} has received a request for {$accessed_url}");

        $this->swapper = new WpClasses\Swapper(
            $accessed_url,
            $wovn_options,
            $wovn_request_options,
            $request_variables,
            RequestHandlerFactory::get_best_available_request_handler($wovn_options),
            $localization_decider,
            new HeadersTransformer()
        );

        add_action('plugins_loaded', array( $this, 'set_lang_and_original_url' ), 1);
        add_action('plugins_loaded', array( $this, 'validate' ), 2);
        // Do not translate in admin page
        if ($localization_decider->can_intercept()) {
            WpClasses\Logger::get()->info('Interception started.');
            add_action('plugins_loaded', array( $this, 'buffer_start' ), 3);
            add_action('shutdown', array( $this, 'buffer_stop' ), 1000);
        } else {
            WpClasses\Logger::get()->info('Request is not intercepted.');
        }
    }

    function set_lang_and_original_url()
    {
        $changed_uri = $this->swapper->set_lang_and_original_url();
        if ($changed_uri) {
            $_SERVER['REQUEST_URI'] = $changed_uri;
        }
    }

    // validation
    function validate()
    {
        $this->swapper->validate();
    }

    function buffer_start()
    {
        ob_start(array( $this, 'buffer_callback' ));
        $this->ob_level = ob_get_level();
    }

    function buffer_callback($buffer)
    {
        if (ContentType::isHtml($buffer)) {
            return $this->swapper->swap_html($buffer);
        } else {
            return $buffer;
        }
    }

    function buffer_stop()
    {
        while (ob_get_level() > $this->ob_level) {
            ob_end_flush();
        }
        WpClasses\Logger::get()->info('Interception ended.');
    }
}
