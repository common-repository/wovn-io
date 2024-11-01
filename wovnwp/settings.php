<?php
namespace Wovnio\Wovnwp;

require_once('constants.php');
require_once('wp-classes/wovn_options.php');
require_once('wp-classes/wovn_options/request_handler_option_wrapper.php');

use \Wovnio\Wovnwp\WpClasses\WovnOptions\RequestHandlerOptionWrapper;

class WovnSettingsPage
{
        /**
         * Holds the values to be used in the fields callbacks
         */
        private $wovn_options;

        /**
         * Start up
         */
    public function __construct()
    {
            add_action('admin_menu', array( $this, 'add_plugin_page' ));
            add_action('admin_init', array( $this, 'page_init' ));
            add_action('update_option_wovn_options', array( $this, 'flush_rewrite_rules' ), 10, 3);
            add_action('update_option_permalink_structure', array( $this, 'update_permalink_structure_callback' ), 10, 3);
            $this->wovn_options = WpClasses\WovnOptions::get_instance();
    }

        /**
         * Add options page
         */
    public function add_plugin_page()
    {
            // This page will be under "Settings"
            add_options_page(
                'Wovn Settings',
                'WOVN.io',
                'manage_options',
                'wovn-setting-admin',
                array( $this, 'create_admin_page' )
            );
    }

        /**
         * Options page callback
         */
    public function create_admin_page()
    {
            // Set class property
        ?>
                <div class="wrap wovn-plugin-admin">
                        <h2><img src="<?php echo plugins_url('images/header-logo.svg', __FILE__); ?>" alt="WOVN.io"> WOVN.io</h2>
                        <p id="wovn-registration-text">For membership registration and translation settings please go to the <a href="https://wovn.io/" target="_blank">WOVN.io official website.</a><p>
                        <div class="form-table-wrapper wovn-plugin-table">
                            <form method="post" action="options.php">
                        <?php
                                // This prints out all hidden setting fields
                                settings_fields('wovn_option_group');
                                do_settings_sections('wovn-setting-admin');
                                submit_button();
                        ?>
                            </form>
                        </div>
                </div>
                <?php
    }

        /**
         * Register and add settings
         */
    public function page_init()
    {
            register_setting(
                'wovn_option_group', // Option group
                'wovn_options', // Option name
                array( $this, 'sanitize' ) // Sanitize
            );

            add_settings_section(
                'setting_section_id', // ID
                '', // Title
                array( $this, 'print_section_info' ), // Callback
                'wovn-setting-admin' // Page
            );

            // user_token
            add_settings_field(
                'wovn_user_token',
                '<i class=" dashicons-before dashicons-admin-network"></i>&nbsp;&nbsp;Project Token',
                array( $this, 'wovn_user_token_callback' ),
                'wovn-setting-admin',
                'setting_section_id'
            );

            // default_lang
            add_settings_field(
                'wovn_default_lang',
                '<i class=" dashicons-before dashicons-admin-site"></i>&nbsp;&nbsp;Default Language',
                array( $this, 'wovn_default_lang_callback' ),
                'wovn-setting-admin',
                'setting_section_id'
            );

            // url_pattern_name
            add_settings_field(
                'wovn_url_pattern_name',
                '<i class=" dashicons-before dashicons-admin-links"></i>&nbsp;&nbsp;Url Pattern Name',
                array( $this, 'wovn_url_pattern_name_callback' ),
                'wovn-setting-admin',
                'setting_section_id'
            );

            // lang_param_name
            add_settings_field(
                'wovn_lang_param_name',
                '<i class=" dashicons-before dashicons-admin-tools"></i>&nbsp;&nbsp;Lang Param Name',
                array( $this, 'wovn_lang_param_name_callback' ),
                'wovn-setting-admin',
                'setting_section_id'
            );

            // supported_langs
            add_settings_field(
                'wovn_supported_langs',
                '<i class=" dashicons-before dashicons-translation"></i>&nbsp;&nbsp;Supported Languages',
                array( $this, 'wovn_supported_langs_callback' ),
                'wovn-setting-admin',
                'setting_section_id'
            );

            // request_handler
            add_settings_field(
                'wovn_request_handler',
                '<i class="dashicons-before dashicons-admin-tools"></i>&nbsp;&nbsp;Translation Request Tool (advanced)',
                array( $this, 'wovn_request_handler_callback' ),
                'wovn-setting-admin',
                'setting_section_id'
            );

            // translation_timeout
            add_settings_field(
                'wovn_translation_timeout',
                '<i class="dashicons-before dashicons-admin-tools"></i>&nbsp;&nbsp;Translation Request Timeout (seconds) (advanced)',
                array( $this, 'wovn_translation_timeout_callback' ),
                'wovn-setting-admin',
                'setting_section_id'
            );

            // wovn api time out search engine bot
            add_settings_field(
                'wovn_api_timeout_search_engine_bots',
                '<i class="dashicons-before dashicons-admin-tools"></i>&nbsp;&nbsp;Translation Request Timeout for Search Engine Bots (seconds)',
                array( $this, 'wovn_api_timeout_search_engine_bots_callback' ),
                'wovn-setting-admin',
                'setting_section_id'
            );

            // http_proxy
            add_settings_field(
                'wovn_http_proxy',
                '<i class="dashicons-before dashicons-admin-tools"></i>&nbsp;&nbsp;HTTP Proxy (advanced)',
                array( $this, 'wovn_http_proxy_callback' ),
                'wovn-setting-admin',
                'setting_section_id'
            );

            // logging toggle
            add_settings_field(
                'wovn_logging_enabled',
                '<i class="dashicons-before dashicons-admin-tools"></i>&nbsp;&nbsp;WOVN Logging',
                array( $this, 'wovn_logging_enabled_callback' ),
                'wovn-setting-admin',
                'setting_section_id'
            );
            // logging file path
            add_settings_field(
                'wovn_log_file_path',
                '<i class="dashicons-before dashicons-admin-tools"></i>&nbsp;&nbsp;WOVN Log File Path',
                array( $this, 'wovn_log_file_path_callback' ),
                'wovn-setting-admin',
                'setting_section_id'
            );
            // widget url
            add_settings_field(
                'wovn_widget_url',
                '<i class="dashicons-before dashicons-admin-tools"></i>&nbsp;&nbsp;Widget URL',
                array( $this, 'wovn_widget_url_callback' ),
                'wovn-setting-admin',
                'setting_section_id'
            );
            // wovn debug mode
            add_settings_field(
                'wovn_debug_mode',
                '<i class="dashicons-before dashicons-admin-tools"></i>&nbsp;&nbsp;[DEBUG SETTING] WOVN Debug Mode',
                array( $this, 'wovn_debug_mode_callback' ),
                'wovn-setting-admin',
                'setting_section_id'
            );
            // wovn translate canonical tag
            add_settings_field(
                'wovn_translate_canonical_tag',
                '<i class="dashicons-before dashicons-admin-tools"></i>&nbsp;&nbsp;Translate Canonical Tag',
                array( $this, 'wovn_translate_canonical_tag_callback' ),
                'wovn-setting-admin',
                'setting_section_id'
            );

            wp_enqueue_style('wovn-css', plugins_url('css/styles.css', __FILE__), array());
    }

        /**
         * Load our custom CSS sheet.
         */
    function load_custom_admin_style()
    {
        wp_enqueue_style('wovn', plugins_url('css/styles.css', __FILE__), array());
    }

        /**
         * Sanitize each setting field as needed
         *
         * @param array $input Contains all settings fields as array keys
         */
    public function sanitize($input)
    {
            $new_input = $input;
        if (isset($input['wovn_user_token'])) {
                $new_input['wovn_user_token'] = sanitize_text_field($input['wovn_user_token']);
        }

        if (empty($input['wovn_lang_param_name'])) {
                $new_input['wovn_lang_param_name'] = Constants::$default_lang_param_name;
        }

        if (empty($input['wovn_api_timeout_search_engine_bots'])) {
            $new_input['wovn_api_timeout_search_engine_bots'] = Constants::$default_wovn_api_timeout_search_engine_bots;
        }

        if ($input['wovn_logging_enabled'] == "true") {
            $new_input['wovn_logging_enabled'] = true;
        } else {
            $new_input['wovn_logging_enabled'] = false;
        }
        if ($input['wovn_debug_mode'] == "true") {
            $new_input['wovn_debug_mode'] = true;
        } else {
            $new_input['wovn_debug_mode'] = false;
        }
        if ($input['wovn_translate_canonical_tag'] == "true") {
            $new_input['wovn_translate_canonical_tag'] = true;
        } else {
            $new_input['wovn_translate_canonical_tag'] = false;
        }

        $supported_langs = $input['wovn_supported_langs'];
        $lang_code_aliases_temp = $input['wovn_lang_code_aliases_temp'];

        if (!empty($supported_langs) && !empty($lang_code_aliases_temp)) {
                $new_input['wovn_lang_code_aliases'] = array_filter(
                    $lang_code_aliases_temp,
                    function ($alias, $lang_code) use ($supported_langs) {
                        return (in_array($lang_code, $supported_langs) && !empty($alias));
                    },
                    ARRAY_FILTER_USE_BOTH
                );
        }
            return $new_input;
    }

        /**
         * Print the Section text
         */
    public function print_section_info()
    {
    }

        /**
         * Get the settings option array and print one of its values
         */
    public function wovn_user_token_callback()
    {
            printf(
                '<input type="text" id="wovn_user_token" name="wovn_options[wovn_user_token]" value="%s" />',
                esc_attr($this->wovn_options->get('wovn_user_token'))
            );
    }

        /**
         * Get the settings option array and print one of its values
         */
    public function wovn_default_lang_callback()
    {
            echo '<select id="wovn_default_lang" name="wovn_options[wovn_default_lang]">';
        foreach (array_values(Constants::$langs) as $lang) {
            printf(
                '<option value="%s" %s>%s</option>',
                $lang['code'],
                selected($this->wovn_options->get('wovn_default_lang'), $lang['code']),
                $lang['name']
            );
        }
            print '</select>';
    }

        /**
         * Get the settings option array and print one of its values
         */
    public function wovn_url_pattern_name_callback()
    {
        $permalink_structure = get_option('permalink_structure');
        $url_pattern_names = Constants::$url_pattern_names;
        echo '<select id="wovn_url_pattern_name" name="wovn_options[wovn_url_pattern_name]">';
        foreach ($url_pattern_names as $name) {
            printf(
                '<option value="%s" %s %s>%s</option>',
                $name,
                selected($this->wovn_options->get('wovn_url_pattern_name'), $name),
                (empty($permalink_structure) && $name == 'path') ? 'disabled="disabled"' : '',
                $name
            );
        }
        echo '</select>';
        if (empty($permalink_structure)) {
            echo('<br>Please change your pemalink settings if you\'d like to set url pattern name to "<b>path</b>"');
        }
    }

        /**
         * Get the settings option array and print one of its values
         */
    public function wovn_supported_langs_callback()
    {
        ?>
                <table class="wp-list-table widefat wovn-lang-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Orginal name</th>
                            <th>English name</th>
                            <th>alias</th>
                            <th>Enabled</th>
                        </tr>
                    </thead>
                    <tbody>
        <?php
        foreach (array_values(Constants::$langs) as $lang) {
            $lang_code = $lang['code'];
            $lang_name = $lang['name'];
            $lang_english_name = $lang['en'];

            $supported_langs = $this->wovn_options->get('wovn_supported_langs');
            $lang_code_aliases_temp = $this->wovn_options->get('wovn_lang_code_aliases_temp') ?: array();

            $checked = in_array($lang_code, $supported_langs) ? 'checked="checked"' : '';
            $lang_code_alias = array_key_exists($lang_code, $lang_code_aliases_temp) ? $lang_code_aliases_temp[$lang_code] : '';

            echo '<tr>';
            echo "<td>$lang_code</td>";
            echo "<td>$lang_name</td>";
            echo "<td>$lang_english_name</td>";
            echo "<td><input type=\"text\" id=\"$lang_code\" name=\"wovn_options[wovn_lang_code_aliases_temp][$lang_code]\" value=\"$lang_code_alias\" placeholder=\"$lang_code\"></td>";
            echo "<td><input type=\"checkbox\" id=\"$lang_code\" name=\"wovn_options[wovn_supported_langs][]\" value=\"$lang_code\" $checked /></td>";
            echo '</tr>';
        }
        ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Code</th>
                        <th>Orginal name</th>
                        <th>English name</th>
                        <th>alias</th>
                        <th>Enabled</th>
                    </tr>
                </tfoot>
            </table>
        <?php
    }

    public function wovn_lang_param_name_callback()
    {
        printf(
            '<input type="text" id="wovn_lang_param_name" name="wovn_options[wovn_lang_param_name]" value="%s" />',
            esc_attr($this->wovn_options->get('wovn_lang_param_name'))
        );
    }

        /**
         * Get the settings option print one of its values
         */
    public function wovn_request_handler_callback()
    {
        $request_handler = $this->wovn_options->get('wovn_request_handler');
        $request_handler_option_wrapper = new RequestHandlerOptionWrapper($request_handler);
        $any_selected = $request_handler_option_wrapper->use_any() ? 'selected' : '';
        $curl_selected = !$any_selected && $request_handler_option_wrapper->use_curl() ? 'selected' : '';
        $file_get_contents_selected = !$any_selected && $request_handler_option_wrapper->use_file_get_contents() ? 'selected' : '';

        echo '<select id="wovn_request_handler" name="wovn_options[wovn_request_handler]">';
        echo '  <option value="' . RequestHandlerOptionWrapper::RH_ALL . '"' . $any_selected . '>Any</option>';
        echo '  <option value="' . RequestHandlerOptionWrapper::RH_CURL . '"' . $curl_selected . '>cURL</option>';
        echo '  <option value="' . RequestHandlerOptionWrapper::RH_FILE_GET_CONTENTS . '"' . $file_get_contents_selected . '>file_get_contents</option>';
        echo '</select>';
    }

        /**
         * Get the settings option and print one of its values
         */
    public function wovn_translation_timeout_callback()
    {
        $translation_timeout = $this->wovn_options->get('wovn_translation_timeout');

        echo "<input id=\"wovn_translation_timeout\" name=\"wovn_options[wovn_translation_timeout]\" type=\"number\" min=\"1\" value=\"$translation_timeout\">";
    }

    public function wovn_http_proxy_callback()
    {
        printf(
            '<input type="text" id="wovn_http_proxy" name="wovn_options[wovn_http_proxy]" value="%s" />',
            esc_attr($this->wovn_options->get('wovn_http_proxy'))
        );
    }

    public function wovn_logging_enabled_callback()
    {
        $wovn_logging_enabled = $this->wovn_options->get('wovn_logging_enabled');
        echo '<select id="wovn_logging_enabled" name="wovn_options[wovn_logging_enabled]">';
        echo '  <option value="false" ' . ($wovn_logging_enabled ? '' : 'selected') . '>OFF</option>';
        echo '  <option value="true" ' . ($wovn_logging_enabled ? 'selected' : '') . '>ON</option>';
        echo '</select>';
    }

    public function wovn_log_file_path_callback()
    {
        printf(
            '<input type="text" id="wovn_log_file_path" name="wovn_options[wovn_log_file_path]" value="%s" />',
            esc_attr($this->wovn_options->get('wovn_log_file_path'))
        );
    }

    public function wovn_widget_url_callback()
    {
        printf(
            '<input type="text" id="wovn_widget_url" name="wovn_options[wovn_widget_url]" value="%s" />',
            esc_attr($this->wovn_options->get('wovn_widget_url'))
        );
    }

    public function wovn_debug_mode_callback()
    {
        $wovn_debug_mode = $this->wovn_options->get('wovn_debug_mode');
        echo '<select id="wovn_debug_mode" name="wovn_options[wovn_debug_mode]">';
        echo '  <option value="false" ' . ($wovn_debug_mode ? '' : 'selected') . '>OFF</option>';
        echo '  <option value="true" ' . ($wovn_debug_mode ? 'selected' : '') . '>ON</option>';
        echo '</select>';
    }

    public function wovn_translate_canonical_tag_callback()
    {
        $wovn_translate_canonical_tag = $this->wovn_options->get('wovn_translate_canonical_tag');
        echo '<select id="wovn_translate_canonical_tag" name="wovn_options[wovn_translate_canonical_tag]">';
        echo '  <option value="false" ' . ($wovn_translate_canonical_tag ? '' : 'selected') . '>OFF</option>';
        echo '  <option value="true" ' . ($wovn_translate_canonical_tag ? 'selected' : '') . '>ON</option>';
        echo '</select>';
    }

    public function wovn_api_timeout_search_engine_bots_callback()
    {
        $wovn_api_timeout_search_engine_bots = $this->wovn_options->get('wovn_api_timeout_search_engine_bots');

        echo "<input id=\"wovn_translation_timeout\" name=\"wovn_options[wovn_api_timeout_search_engine_bots]\" type=\"number\" min=\"1\" value=\"$wovn_api_timeout_search_engine_bots\">";
    }

        // after save options, flush rewrite rules
    public function flush_rewrite_rules($old_value, $new_value)
    {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }

        // Url_pattern_name 'path' is not possible with Default permalinks.
    public function update_permalink_structure_callback($old_value, $new_value)
    {
        // Set class property
        $options = $this->wovn_options->get_all();
        $permalink_structure = get_option('permalink_structure');
        if (empty($permalink_structure) && $options['wovn_url_pattern_name'] == 'path') {
            $options['wovn_url_pattern_name'] = 'query';
            update_option('wovn_options', $options);
        }
    }
}
