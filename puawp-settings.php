<?php

/* the Pop Up Archive SDK is required and comes as part of the plugin. */
/* @todo add a version check for both the plugin and the SDK */
require_once 'includes/Services/Popuparchive.php';

/**
 * Pop Up Archive WP Plugin display class for list of audio clips
 *
 * @category  Services
 * @package   Display_PUAWP_Settings
 * @author    Thomas Crenshaw <thomas@circadigital.biz>
 * @copyright 2014 Thomas Crenshaw <thomas@circadigital.biz>
 * @license   
 * @link      https://circadigital.biz/
 *
 */
function Display_PUAWP_Settings()
{
    /**
     * Error string
     *
     * @var string
     *
     * @access public 
     */
    $errors = '';

    /**
     * Pop Up Archive application client id
     *
     * @var string
     *
     * @access public
     */
    $puawp_client_id = '';

    /**
     * Pop Up Archive application client secret
     *
     * @var string
     *
     * @access public
     */
    $puawp_client_secret = '';

    /**
     * Pop Up Archive application access token
     *
     * @var string
     *
     * @access public
     */
    $puawp_access_token = '';

    /**
     * Pop Up Archive application redirect URI base path
     * 
     * @todo hack a way for the PUA OAuth system to work with WP query parameters
     *
     * @var string
     *
     * @access public
     */
    $puawp_redir_uri_base = site_url().'/wp-admin/admin.php';

    /**
     * Pop Up Archive application redirect URI query path
     * 
     * @todo hack a way for the PUA OAuth system to work with WP query parameters
     *
     * @var string
     *
     * @access public
     */
    $puawp_redir_uri_query = '?page=puawp_options';

    /**
     * File to be uploaded
     *
     * @var string
     *
     * @access public
     */
    $attached_file = '';

    /** @todo: validate for audio item info */
    if (isset($_POST['popuparchive_clear'])) {
        //add the data to the wp_options table
        $options = array(
            'puawp_client_id' => '',
            'puawp_client_secret' => '',
            'puawp_access_token' => '',
            'puawp_redir_uri_base' => '',
            'puawp_redir_uri_query' => ''
        );
        update_option('popuparchive_settings', $options); //store the results in WP options table
        echo '<div id="message" class="updated fade">';
        echo '<p>Settings Cleared</p>';
        echo '</div>';
    } elseif (isset($_POST['popuparchive_update'])) {
        if ($_POST['puawp-client-id'] != "") {
            $puawp_client_id = filter_var($_POST['puawp-client-id'], FILTER_SANITIZE_STRING);
            if ($_POST['puawp-client-id'] == "") {
                $errors .= 'Please enter a valid Pop Up Archive Application ID.<br/><br/>';
            }
        } else {
            $errors .= 'Please enter your Pop Up Archive Application ID.<br/>';
        }

        if ($_POST['puawp-client-secret'] != "") {
            $puawp_client_secret = filter_var($_POST['puawp-client-secret'], FILTER_SANITIZE_STRING);
            if ($_POST['puawp-client-secret'] == "") {
                $errors .= 'Please enter a valid Pop Up Archive Application secret.<br/>';
            }
        } else {
            $errors .= 'Please enter your Pop Up Archive Application secret.<br/>';
        }

        if (!$errors) {
           //add the data to the wp_options table
            $options = array(
                'puawp_client_id' => $puawp_client_id,
                'puawp_client_secret' => $puawp_client_secret,
                'puawp_access_token' => $puawp_access_token,
                'puawp_redir_uri_base' => $puawp_redir_uri_base,
                'puawp_redir_uri_query' => $puawp_redir_uri_query
            );
            update_option('popuparchive_settings', $options); //store the results in WP options table
            echo '<div id="2" class="updated fade">';
            echo '<p>Settings Saved</p>';
            echo '</div>';
        } else {
            echo '<div class="error fade">' . $errors . '<br/></div>';
        }
    }

    /** getting the puawp options out of the database */
    $puawp_settings = get_option('popuparchive_settings');
    $puawp_client_id = $puawp_settings['puawp_client_id'];
    $puawp_client_secret = $puawp_settings['puawp_client_secret'];

    if ($puawp_settings['puawp_access_token'] != "") {
        $puawp_access_token = $puawp_settings['puawp_access_token'];
    } else {

    }
    /** verifying the existance of this variable when it is empty so error isn't displayed
        Pop Up Archive Error: Could not process the request - Error code (0).
    **/
    ////$puawp_access_token = isset($puawp_settings['puawp_access_token'])?$puawp_settings['puawp_access_token']:'';
?>

<div class="wrap">
<div id="poststuff"><div id="post-body">
<div class="postbox">
<h3><label for="title">Before Using This Plugin</label></h3>
<div class="inside">
<p class="postbox-container">To use the WP Pop Up Archive plugin you will firstly need to create a Pop Up Archive app using your current Pop Up Archive account details and then paste some
of the details from your app in the configuration settings of this page.
<br />
<h2>Creating Your Pop Up Archive App</h2>
This literally takes a minute to do. To create a Pop Up Archive app go to <a href="https://www.popuparchive.com/oauth/applications/" target="_blank">THIS PAGE</a> and fill in the details as follows:
<ol>
    <li type="disc">
        Enter the following string for the "Name" of your application - <strong>popuparchive-wp</strong></li>
        <li type="disc">Copy and paste the following url for the "Redirect uri" of your application <span style="color: green; background-color:yellow;"><strong><?php echo site_url();?>/wp-admin/admin.php</strong></span></li>
</ol>
<h2>Configuring the Plugin</h2>
After creating the application on the Pop Up Archive site, copy the following details from your app and paste in the configuration settings on this page:
<ol>
    <li type="disc"><strong>Application ID</strong> - Copy this value from your Pop Up Archive app and paste in the "Application ID" field below.</li>
    <li type="disc"><strong>Secret</strong> - Copy this value of your application's "Secret" and paste in the "Secret" field below.</li>
</ol>
After entering the configuration settings click the "Save Settings" button.
<h2>Connecting To Pop Up Archive</h2>
After saving your settings click the "<strong>Connect To Pop Up Archive</strong>" link in the "Pop Up Archive Connection Status" section below.
This will take you to the Pop Up Archive site and ask you to allow the "popuparchive-wp" plugin to connect to your Pop Up Archive account.
Click the "Connect" button.
</p>
</div></div>
<div class="postbox">
<h3><label for="title">Pop Up Archive Connection Status</label></h3>
<div class="inside puawp_connect_status">
<?php
    if ($puawp_client_id && $puawp_client_secret && $puawp_access_token == "") {
        echo '<div class="puawp_error_msg" style="color:red;"><strong>You are currently disconnectedddd from Pop Up Archive.</strong></div>';
?>

<?php
        popuparchive_authenticate($puawp_settings);
?>
</p>
<?php
    } elseif (!$puawp_client_id && !$puawp_client_secret) {
        echo '<div class="puawp_error_msg" style="color:red;"><strong>The Pop Up Archive Application ID and Secret have not been set</strong></div>';
    } else {
        echo '<div class="puawp_success_msg" style="color:green;"><strong>You are currently connected to Pop Up Archive.</strong></div>';
    }
?>
</div></div>
<form action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="POST"	onsubmit="">
<input type="hidden" name="popuparchive_update" id="popuparchive_update" value="true" />
<div class="postbox">
<h3><label for="title">Enter Your Pop Up Archive Account Details</label></h3>
<div class="inside">
<table class="form-table">
    <tr valign="top">
        <th scope="row"><label for="PUAWPClientID"> Pop Up Archive Application ID:</label>
        </th>
        <td><input type="text" size="40" name="puawp-client-id" value="<?php echo $puawp_client_id; ?>" /></td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="PUAWPClientSecret"> Pop Up Archive Secret:</label>
        </th>
        <td><input type="text" size="40" name="puawp-client-secret" value="<?php echo $puawp_client_secret; ?>" /></td>
    </tr>
</table>
<input name="popuparchive_update" type="submit" value="Save Settings" class="button-primary" />
<input name="popuparchive_clear" type="submit" value="Clear Settings" class="button-primary" />
    </div></div>
<br />
    </form>
</div></div>
</div>
<?php
}

function popuparchive_authenticate($puawp_options)
{
    //get Pop Up Archive options
 //   $puawp_options = get_option('popuparchive_settings');
    if ($puawp_options) {
        $puawp_client_id = $puawp_options['puawp_client_id'];
        $puawp_client_secret = $puawp_options['puawp_client_secret'];
        /** @todo trap when there is no token returned */
        $puawp_access_token = $puawp_options['puawp_access_token'];
        $puawp_redir_uri = $puawp_options['puawp_redir_uri_base'].$puawp_options['puawp_redir_uri_query'];
    }
    $popuparchive = new Popuparchive_Services($puawp_client_id, $puawp_client_secret, $puawp_redir_uri);
 
    if ($puawp_access_token == "") {
        //$params = array('scope' => 'non-expiring');
//        $authorizeUrl = $popuparchive->getAuthorizeUrl($params);
        $authorizeUrl = $popuparchive->getAuthorizeUrl();
        echo '<br /><a id="puawp_connect_url" style="border-style:solid; padding:5px; border-color:orange;" href="'.$authorizeUrl.'">Click Here To Connect To Pop Up Archive</a>';
        
        if(isset($_GET['code'])) {
            try {
                /** @todo: - use isset to check if "code" param below exists */
//                $post_data = array();
//                $curl_opts = array(CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false,);
//                $accessToken = $popuparchive->accessToken($_GET['code'], $post_data, $curl_opts);
//                $accessToken = $popuparchive->accessToken($_GET['code'], $post_data);
                $accessToken = $popuparchive->simpleAccessTokenRequest($_GET['code']);
                echo puawp_jquery_snippet();
            } catch (Popuparchive_Services_Invalid_Http_Response_Code_Exception $e) {
                //exit($e->getMessage());
                echo '<div style="color:red;"><p><strong>Pop Up Archive Error: Could not process the request - Error code ('.$e->getHttpCode().').</strong></p></div>';

                return;
            }
        }
        //store the token in the options
        $puawp_redir_uri_base = site_url().'/wp-admin/admin.php';
        $puawp_redir_uri_query = '?page=puawp_options';
        $param = array('puawp_client_id' => $puawp_client_id,
                    'puawp_client_secret' => $puawp_client_secret,
                    'puawp_access_token' => $accessToken['access_token'],
                    'puawp_redir_uri_base' => $puawp_redir_uri_base,
                    'puawp_redir_uri_query' => $puawp_redir_uri_query
                    );
        update_option('popuparchive_settings', $param); //store the results in WP options table
        $popuparchive->setAccessToken($accessToken['access_token']);
    } elseif ($puawp_access_token) {
        $popuparchive->setAccessToken($puawp_access_token);
    }
}

function puawp_jquery_snippet()
{
    $code = '<script type="text/javascript">
                jQuery.noConflict();
                jQuery(document).ready(function ($) {
                    $(".puawp_error_msg").remove();
                    $("#puawp_connect_url").remove();
                    $("<div/>")
                        .attr("class","puawp_success_msg")
                        .css("color","green")
                        .css("font-weight", "bold")
                        .val("You are currently connected to Pop Up Archive.")
                        .text("You are currently connected to Pop Up Archive.")
                        .appendTo($(".puawp_connect_status"));

                });
                </script>';

    return $code;
}
?>
