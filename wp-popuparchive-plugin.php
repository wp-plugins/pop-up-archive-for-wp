<?php
/*
Plugin Name: Pop Up Archive
Plugin URI: https://github.com/popuparchive/popuparchive-wp
Description: This plugin will let you embed Pop Up Archive audio files and automatically generated tags into your posts and pages.
Author: Thomas Crenshaw / Pop Up Archive
Version: 1.1.0
Author URI: https://www.popuparchive.com
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* check to see if the class is already loaded */
if (!class_exists('Popuparchive_WP')) {
    /**
     * Main plugin class.
     *
     * @since 1.0.0
     *
     * @todo just in case - add a get_instance class to ensure not creating multiple instances of popuparchive
     *
     *
     * @package Popuparchive_WP
     * @author  Thomas Crenshaw
     */
    class Popuparchive_WP
    {
        /*
         * Declare keys here as well as our tabs array which
         * is populated when registering settings
         */
        private $edit_puawp_settings_page_key = 'edit_puawp_settings_page';
        private $puawp_display_page_key = 'puawp_display_page';
        private $puawp_display_usage_key = 'puawp_display_usage';
        private $puawp_options_key = 'puawp_options';
        private $puawp_settings_tabs = array();

       /**
         * Holds the class object.
         *
         * @since 1.0.0
         *
         * @var object
         */
        public static $instance;

        /**
         * Plugin version, used for cache-busting of style and script file references.
         *
         * @since 1.0.0
         *
         * @var string
         */
        public $version = '1.0.0';

        /**
         * The name of the plugin.
         *
         * @since 1.0.0
         *
         * @var string
         */
        public $plugin_name = 'Pop Up Archive';

        /**
         * Unique plugin slug identifier.
         *
         * @since 1.0.0
         *
         * @var string
         */
        public $plugin_slug = 'popuparchive-wp';

        /**
         * Plugin file.
         *
         * @since 1.0.0
         *
         * @var string
         */
        public $file = __FILE__;

        /**
         * Primary class constructor.
         *
         * @since 1.0.0
         */
        function __construct()
        {
            /* fire a hook before the plugin is loaded */
            do_action( 'popuparchive_pre_init' );
            $this->define_constants();
            $this->loader_operations();
            /* Load the plugin. */
            add_action( 'init', array( $this, 'init' ), 0 );
        }

        /**
         * Initializes and loads the plugin into WordPress
         *
         * @since 1.0.0
         */
        public function init()
        {
            /* run hook once the plugin has been initialized */
            do_action( 'popuparchive_init');

            /* load components that are only used by the admin */
            if (is_admin()) {
                $this->require_admin();
            }
        }

        /**
         * Loads all the admin functionality into scope
         *
         * @since 1.0.0
         */
        public function require_admin()
        {
             require plugin_dir_path( __FILE__ ) . 'includes/admin/addtopost.php';
        }

        /**
         * Define the constants.
         *
         * @since 1.0.0
         */
        function define_constants()
        {
            define('PUAWP_PLUGIN_PATH', dirname(__FILE__));
            define('PUAWP_PLUGIN_URL', plugins_url('',__FILE__));
        }

        /**
         * Begin loading the applicable items that power the plugin.
         *
         * @since 1.0.0
         */
        function loader_operations()
        {
            add_action( 'init', array( $this, 'init_tasks' ) );
            add_action( 'plugins_loaded', array( $this,'popuparchive_plugins_loaded'));
            add_action( 'admin_init', array( $this, 'admin_init_tasks' ) );
            add_action( 'admin_menu', array( $this, 'add_admin_menus' ) );
            if (!is_admin()) {
                add_filter('widget_text', 'do_shortcode');
            }
        }

        /**
         * Load the necessary libraries for the Pop Up Archive plugin.
         *
         * @todo slim this down to something that is a) realistic and b) manageable
         *
         * @since 1.0.0
         */
        function load_libs()
        {
            wp_enqueue_script('jquery');
            if (isset($_GET['page']) && $_GET['page'] == 'puawp_options') {
            /* some ajax/jquery script enqueues etc */
                wp_enqueue_script('jquery-ui-core');
                wp_enqueue_script('jquery-ui-widget');
                wp_enqueue_script('jquery-ui-position');
                wp_enqueue_script('jquery-ui-dialog');

                /* media upload stuff for later */
                wp_enqueue_script('media-upload');
                wp_enqueue_script('thickbox');
                wp_enqueue_style('thickbox'); //style sheet for thickbox
            }
        }

        /**
         * Initialize the admin tasks.
         *
         * @since 1.0.0
         */
        function init_tasks()
        {
            if (is_admin()) {
                $this->load_settings();
                $this->load_libs();
            } else {
                // add front end init tasks here
            }
        }

        /**
         * Call the initial admin methods.
         *
         * @since 1.0.0
         */
        function admin_init_tasks()
        {
            $this->register_puawp_settings_page();
            $this->register_puawp_display_page();
            $this->register_puawp_display_usage();
            $this->puawp_options_setup();
        }

        /**
         * Calls methods after the plugin is loaded
         *
         * @since 1.0.0
         */
        function popuparchive_plugins_loaded()
        {
            add_shortcode( 'popuparchive', array ( $this, 'popuparchive_shortcode' ) );
        }

        /**
         * Creates the shortcode for the plugin.
         *
         * @since 1.0.0
         *
         * @global object $post The current post object.
         *
         * @param array $atts Array of shortcode attributes.
         * @return string     The audio clip output.
         */
        function popuparchive_shortcode($atts)
        {
            if (!isset($atts['name'])) {
                if ($atts['audio_file_id'] && $atts['item_id'] && $atts['collection_id']) {
                    $atts['name'] = $this->get_audio_item_name($atts['collection_id'], $atts['item_id']);
                } else {
                return '<div style="color:red;"><p><strong>Pop Up Archive Plugin Error: There is an issue with the data provided in the shortcode.
                <br />Please verify that you have correctly entered the audio file id, the item id and the collection id</strong></p></div>';                }
            }

            return "<iframe frameborder='0' scrolling='no' seamless='yes' width='508 height='95' name='".$atts['name']."' src='https://www.popuparchive.com/embed_player/".rawurlencode($atts['name'])."/".$atts['audio_file_id']."/".$atts['item_id']."/".$atts['collection_id']."' ></iframe>";

        }

        /**
         * Audio item name is required for the iFrame used in the shortcode.
         *
         * @since 1.0.0
         *
         * @param string $collection_id The unique identifier for a collection.
         * @param string $item_id       The unique identifier for an audio item.
         *
         * @return string     The audio item name.
         */
        function get_audio_item_name($collection_id, $item_id)
        {
            require_once 'includes/Services/Popuparchive.php';
            $pua_options = get_option('popuparchive_settings');

            if ($pua_options) {
                $puawp_client_id = $pua_options['puawp_client_id'];
                $puawp_client_secret = $pua_options['puawp_client_secret'];
                $puawp_access_token = $pua_options['puawp_access_token'];
                $puawp_redir_uri = $pua_options['puawp_redir_uri_base'].$pua_options['puawp_redir_uri_query'];
            }

            $popuparchive = new Popuparchive_Services($puawp_client_id, $puawp_client_secret, $puawp_redir_uri);
            if ($puawp_access_token && $puawp_client_id && $puawp_client_secret) {
                $popuparchive->setAccessToken($puawp_access_token);
            } else {
                /* display an error stating that the user needs to authenticate first */

                return '<div style="color:red;"><p><strong>Pop Up Archive Plugin Error: You need to authenticate your connection to the Pop Up Archive API.
                <br />Please go to the Pop Up Archive plugin settings and configure your API credentials.</strong></p></div>';
            }
            try {
                $item_metadata = $popuparchive->getItemById($collection_id, $item_id);
            } catch (Popuparchive_Services_Invalid_Http_Response_Code_Exception $e) {
                echo '<div style="color:red;"><p><strong>Pop Up Archive Plugin Error: Could not display your Pop Up Archive Items - Error code ('.$e->getHttpCode().').</strong></p></div>';

                return;
            }
            $item_metadata_decode = json_decode($item_metadata, true);

            return $item_metadata_decode['title'];
        }

        /**
         * Begin setting up the Pop Up Archive plugin options.
         *
         * @since 1.0.0
         *
         * @return void
         */
        function puawp_options_setup()
        {
            global $pagenow;
            if ('media-upload.php' == $pagenow || 'async-upload.php' == $pagenow) {
                /* Here we will customize the 'Insert into Post' Button text inside Thickbox */
                add_filter( 'gettext', array($this, 'replace_thickbox_text'), 1, 2);
            }
        }

        /**
         * Replace the thickbox text for our Pop Up Archive insert into text item.
         *
         * @since 1.0.0
         *
         * @param string $translated_text The translated text if we are doing translation.
         * @param string $text            The text to display.
         *
         * @return string The text to display.
         */
        function replace_thickbox_text($translated_text, $text)
        {
            if ('Insert into Post' == $text) {
                $referer = strpos( wp_get_referer(), 'puawp_options' );
                if ($referer != '') {
                    return ('Select For Pop Up Archive Upload');
                }
            }

            return $translated_text;
        }

        /**
         * Load the admin page settings
         *
         * Loads both tab settings from the database into their respective arrays.
         * Uses array_merge to merge with default values if they're missing.
         *
         * @since 1.0.0
         *
         * @return void
         */
        function load_settings()
        {
            $this->pua_edit_settings = (array) get_option( $this->edit_puawp_settings_page_key );
            $this->pua_display_settings = (array) get_option( $this->puawp_display_page_key );
            $this->pua_display_usage = (array) get_option( $this->puawp_display_usage_key );
            /* Merge with defaults */
            $this->edit_puawp_settings_page = array_merge( array(
                'edit_pua_option' => 'Pop Up Archive Settings Page'
            ), $this->pua_edit_settings );

            $this->pua_display_settings_page = array_merge( array(
                'pua_display_option' => 'Manage Audio'
            ), $this->pua_display_settings );

            $this->pua_display_usage_page = array_merge( array(
                'pua_usage_option' => 'Usage Info'
            ), $this->pua_display_usage );
        }

        /*
         * It is time to register the display templates page via the WordPress Settings API
         * and append the setting to the tabs array of the object.
         */

        /**
         * Register the Admin settings page
         *
         * @since 1.0.0
         */
        function register_puawp_settings_page()
        {
            $this->puawp_settings_tabs[$this->edit_puawp_settings_page_key] = 'Settings';
            register_setting( $this->edit_puawp_settings_page_key, $this->edit_puawp_settings_page_key );
        }

        /**
         * Register the Pop Up Archive display page display template.
         *
         * @since 1.0.0
         *
         * @return void
         */
        function register_puawp_display_page()
        {
            $this->puawp_settings_tabs[$this->puawp_display_page_key] = 'Manage Audio Assets';
            register_setting( $this->puawp_display_page_key, $this->puawp_display_page_key );
        }

        /**
         * Register the Pop Up Archive Usage page display template
         *
         * @since 1.0.0
         *
         * @return void
         */
        function register_puawp_display_usage()
        {
            $this->puawp_settings_tabs[$this->puawp_display_usage_key] = 'Usage Info';
            register_setting( $this->puawp_display_usage_key, $this->puawp_display_usage_key );
        }

        /* Define an admin page. */
        /**
         * Called during admin_menu, adds an options page under Settings.
         *
         * @since 1.0.0
         *
         * @return void
         */
        function add_admin_menus()
        {
            $pua_admin_page = add_menu_page('Pop Up Archive', 'Pop Up Archive', 'manage_options', $this->puawp_options_key, array($this, 'popuparchive_page'), PUAWP_PLUGIN_URL.'/assets/css/images/popuparchive-icon.png');
            add_action( 'admin_print_styles-' .$pua_admin_page, array( &$this, 'pua_load_style' ) );
        }

        /**
         * Properly load the popuparchive.css file.
         *
         * @since 1.0.0
         *
         * @return void
         */
        function pua_load_style()
        {
            /* Register */
            wp_register_style('pua-styles', PUAWP_PLUGIN_URL.'/assets/css/popuparchive.css', array(), '1.0.0', 'all');

             /* Enqueue
              * It will be called only on the plugin admin page, enqueue our stylesheet here
              */
            wp_enqueue_style('pua-styles');
        }

        /**
         * Plugin Options page rendering goes here, checks for active tab and replaces key with the related
         * settings key. Uses the plugin_options_tabs method to render the tabs.
         *
         * @since 1.0.0
         *
         * @return void
         */
        function popuparchive_page()
        {
            $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->edit_puawp_settings_page_key;
            ?>
            <div class="wrap">
                <?php
                $this->plugin_options_tabs();
                if ($tab == 'edit_puawp_settings_page') {
                    include_once 'puawp-settings.php';
                    Display_PUAWP_Settings();
                } elseif ($tab == 'puawp_display_page') {
                    include_once 'puawp-display-items.php';
                    renderAudioItemsList();
                } elseif ($tab == 'puawp_display_usage') {
                    include_once 'puawp-display-usage.php';
                }
                ?>
            </div>
            <?php
        }
        /**
         * Gets the currently selected tab.
         *
         * @since 1.0.0
         *
         * @return string The tab.
         */
        function current_tab()
        {
            $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->edit_puawp_settings_page_key;

            return $tab;
        }

        /**
         * Render plugin settings tabs
         *
         * Renders our tabs in the plugin options page, walks through the object's tabs array and prints
         * them one by one. Provides the heading for the plugin_options_page method.
         *
         * @since 1.0.0
         *
         * @return void
         */
        function plugin_options_tabs()
        {
            $current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->edit_puawp_settings_page_key;

            echo '<h2 class="nav-tab-wrapper">';
            foreach ($this->puawp_settings_tabs as $tab_key => $tab_caption) {
                $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
                echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->puawp_options_key . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
            }
            echo '</h2>';
        }

        /**
         * Write to the WP log
         *
         * @since 1.0.0
         *
         * @param array|object $log type of object.
         *
         * @return void
         */
        function write_log($log)
        {
            if (true === WP_DEBUG) {
                if ( is_array( $log ) || is_object( $log ) ) {
                    error_log( print_r( $log, true ) );
                } else {
                    error_log( $log );
                }
            }
        }
        /**
         * Returns the singleton instance of the class.
         *
         * @since 1.0.0
         *
         * @return object The Popuparchive_WP object.
         */
        public static function get_instance()
        {
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Popuparchive_WP ) ) {
                self::$instance = new Popuparchive_WP();
            }

            return self::$instance;
        }

    } //end Popuparchive_WP class
} // end of Popuparchive_WP conditional check (no 'else')
$puawp_page = new Popuparchive_WP;

/**
 * Add the settings link to the plugins page.
 *
 * @since 1.0.0
 *
 * @param string $links The link to display on the plugin pagefor the settings page.
 * @param string $file  Name of file for validation conditional.
 *
 * @return string $links The link to the settings page for display.
 */
function puawp_settings_link($links, $file)
{
    if ( $file == plugin_basename( __FILE__ ) ) {
        $settings_link = '<a href="admin.php?page=puawp_options">Settings</a>';
        array_unshift($links, $settings_link);
    }

    return $links;
}
add_filter('plugin_action_links', 'puawp_settings_link', 10, 2);

?>
