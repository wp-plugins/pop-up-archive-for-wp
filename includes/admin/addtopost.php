<?php
/**
 * Pop Up Archive WordPress Plugin Media Modal File and Class
 *
 * @category  WordPress
 * @copyright 2014 Thomas Crenshaw <thomas@circadigital.biz>
 * @license
 * @link      https://circadigital.biz/
 * @author    Thomas Crenshaw <thomas@circadigital.biz>
 * @package   Popuparchive_WP_List_Table
 */
require_once dirname(dirname(dirname(__FILE__))).'/puawp-settings.php';
require_once dirname(dirname(dirname(__FILE__))).'/puawp-display-items.php';
require_once dirname(dirname(__FILE__)).'/Services/Popuparchive.php';

/* Add a new Tab */


/**
 *
 *
 * @param unknown $tabs
 * @return unknown
 */
function popuparchive_media_upload_tab($tabs) {
    $newtab = array('popuparchive_wp' => __('Pop Up Archive', 'popuparchive_wp'));

    return array_merge($tabs, $newtab);
}


add_filter('media_upload_tabs', 'popuparchive_media_upload_tab');

/* Add Scripts and Styles to New Tab **/
////add_action('admin_print_scripts-media-upload-popup', 'popuparchive_wp_option_scripts', 2000);
////add_action('admin_print_styles-media-upload-popup', 'popuparchive_wp_option_styles', 2000);

/* Pop Up Archive Media Tab (Iframe) content*/


/**
 *
 */
function popuparchive_wp_media_process() {
    media_upload_header();
    get_popuparchive_audio_items(true);
}


/* load Iframe in the tab page */


/**
 *
 *
 * @return unknown
 */
function popuparchive_wp_media_menu_handle() {
    return wp_iframe( 'popuparchive_wp_media_process');
}


add_action('media_upload_popuparchive_wp', 'popuparchive_wp_media_menu_handle');


/**
 * Add Pop Up Archive button to Upload/Insert
 *
 * @param unknown $context
 * @return unknown
 */
function popuparchive_plugin_media_button($context) {
    global $post_ID;
    $plugin_media_button = ' %s' . '<a id="add_popuparchive" title="Insert Pop Up Archive Player" href="media-upload.php?post_id='.$post_ID.'&tab=popuparchive_wp&selectFormat=audio&paged=1&TB_iframe=1&width=640&height=584" class="thickbox"><img alt="Insert Pop Up Archive Player" src="'.PUAWP_PLUGIN_PATH.'assests/css/images/popuparchive-icon.png"></a>';

    return sprintf($context, $plugin_media_button);
}


add_filter('media_buttons_context', 'popuparchive_plugin_media_button');


/**
 * Let's create some magic
 *
 * @param unknown $popuparchive_init (optional)
 * @return unknown
 */
function get_popuparchive_audio_items($popuparchive_init = false) {

    // default settings
    $options = get_option('popuparchive_settings');

    //get Pop Up Archive options
    $puawp_options = get_option('popuparchive_settings');
    if ($puawp_options) {
        $puawp_client_id = $puawp_options['puawp_client_id'];
        $puawp_client_secret = $puawp_options['puawp_client_secret'];

        // @todo trap when there is no token returned
        $puawp_access_token = $puawp_options['puawp_access_token'];
        $puawp_redir_uri = $puawp_options['puawp_redir_uri_base'].$puawp_options['puawp_redir_uri_query'];
    }
    $popuparchive = new Popuparchive_Services($puawp_client_id, $puawp_client_secret, $puawp_redir_uri);
    if ($puawp_access_token && $puawp_client_id && $puawp_client_secret) {
        $popuparchive->setAccessToken($puawp_access_token);
    } else {
        //display an error stating that the user needs to authenticate first
        return '<div style="color:red;"><p><strong>Pop Up Archive Plugin Error: You need to authenticate your connection to the Pop Up Archive API.
        <br />Please go to the Pop Up Archive plugin settings and configure your API credentials.</strong></p></div>';
    }

    renderAudioItemsList(true, $_REQUEST['post_id']);

}


/**
 * Set the authorization code that was returned from the OAuth query
 *
 * @param puawp_options array contains the Pop Up Archive OAuth2 options
 *
 *
 * @access public
 * @param unknown $puawp_options
 * @return object Pop Up Archive object
 * */
function puawp_set_access_token_module($puawp_options) {
    $popuparchive = new Popuparchive_Services($puawp_options['puawp_client_id'], $puawp_options['puawp_client_secret'], $puawp_options['puawp_redir_uri_base'].$puawp_options['puawp_redir_uri_query']);
    $popuparchive->setAccessToken($puawp_options['puawp_access_token']);

    return $popuparchive;
} // end puawp_set_access_token_module
