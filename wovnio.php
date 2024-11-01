<?php
namespace Wovnio\Wovnwp;
/*
Plugin Name: WOVN.io
Plugin URI: https://wovn.io/
Description: Localize your website, translate web pages in minutes.
Version: 1.11.0
Author: Wovn Technologies, Inc.
Author URI: https://wovn.io/
License: GPLv2 or later
*/

require_once( 'wovnwp/wp-classes/plugin_detector.php' );
require_once( 'wovnwp/wp-classes/localization_decider.php' );
require_once( 'wovnwp/wp-classes/wovn_options.php' );
require_once( 'wovnwp/wp-classes/wovn_request_options.php' );
require_once( 'wovnwp/wp-classes/logger.php' );
require_once( 'wovnwp/rewrite.php' );
require_once( 'wovnwp/settings.php' );
require_once( 'wovnwp/translate.php' );

use Wovnio\Wovnwp\WpClasses\WovnRequestOptions;

$pluginDetector = new WpClasses\PluginDetector();
$wovn_options = WpClasses\WovnOptions::get_instance();
$query_string = isset($_SERVER["QUERY_STRING"]) ? $_SERVER["QUERY_STRING"] : '';
parse_str($query_string, $queryStringArray);
$wovn_request_options = new WovnRequestOptions($queryStringArray, $wovn_options->get('wovn_debug_mode'));
$localization_decider = new WpClasses\LocalizationDecider($pluginDetector, $wovn_request_options);
$wovn_logger = WpClasses\Logger::get();

if ($wovn_options->get('wovn_logging_enabled') !== false) {
	$log_file_path = $wovn_options->get('wovn_log_file_path');
	$wovn_logger->setLogFilePath($log_file_path);
}

new  UrlRewrite($localization_decider);
if (is_admin()) {
  new WovnSettingsPage();
}
new Translate($localization_decider, $wovn_request_options);
?>
