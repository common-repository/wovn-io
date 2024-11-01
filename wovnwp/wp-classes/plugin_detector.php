<?php
namespace Wovnio\Wovnwp\WpClasses;

class PluginDetector
{
    public function amp_plugin_detected()
    {
        $amp_plugin_found = defined('AMP__VERSION') || defined('AMPFORWP_VERSION');

        if (!$amp_plugin_found && function_exists('is_plugin_active')) {
            $amp_plugin_found = is_plugin_active('amp/amp.php') ||
                                is_plugin_active('accelerated-mobile-pages/accelerated-mobile-pages.php');
        }

        return $amp_plugin_found;
    }

    public function all_in_one_calendar_plugin_detected()
    {
        if (function_exists('is_plugin_active')) {
            return is_plugin_active('all-in-one-event-calendar/all-in-one-event-calendar.php');
        }

        return false;
    }
}
