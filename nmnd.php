<?php
/*
  Plugin Name: NMND Design Activation
  Version: v1.0
  Plugin URI: https://www.nmnd.design
  Author: NMND Design
  Author URI: https://www.nmnd.design
  Description: License Checker
 */


define('YOUR_SPECIAL_SECRET_KEY', '5e6d944b792334.77746741'); 

define('YOUR_LICENSE_SERVER_URL', 'https://nmnd.design'); 

define('YOUR_ITEM_REFERENCE', 'NMND License');

//define some URL's for redirecting
$expired_page = 'https://nmnd.design/website-expired?license='.get_option('nmnd_license_key').'&amp;'.get_option('siteurl').'';
$error_page = 'https://nmnd.design/website-error?license='.get_option('nmnd_license_key').'&amp;'.get_option('siteurl').'';
$disabled_page = 'https://nmnd.design/website-disabled?license='.get_option('nmnd_license_key').'&amp;'.get_option('siteurl').'';

//add the menu item into the settings menu
add_action('admin_menu', 'nmnd_license_menu');

function nmnd_license_menu() {
    add_options_page('NMND Website License Activation', 'NMND Design License', 'manage_options', __FILE__, 'nmnd_license_management_page');
}


//remove the deactivate link on the plugins list
add_filter( 'plugin_action_links', function ( $actions, $plugin_file ) {

    if ( plugin_basename( __FILE__ ) === $plugin_file ) {
        unset( $actions['deactivate'] );
    }

    return $actions;

}, 10, 2 );


//lets add a fool proof redirection function - cheers Dan :)
function redirect($url) {
    if (!headers_sent())
    {    
        header('Location: '.$url);
        exit;
        }
    else
        {  
        echo '<script type="text/javascript">';
        echo 'window.location.href="'.$url.'";';
        echo '</script>';
        echo '<noscript>';
        echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
        echo '</noscript>'; exit;
    }
}

//check license in relatime via API
function check_license() {
    
    $license = get_option('nmnd_license_key');

    $status = 'pending';

    $api_params = array(
        'slm_action' => 'slm_check',
        'secret_key' => YOUR_SPECIAL_SECRET_KEY,
        'license_key' => $license,
    );
        
    // Send query to the license manager server
    $response = wp_remote_get(add_query_arg($api_params, YOUR_LICENSE_SERVER_URL), array('timeout' => 20, 'sslverify' => false));

    if (is_wp_error($response)){
        echo "Unexpected Error! The query returned with an error.";
    }

    //var_dump($response);//uncomment it if you want to look at the full response

    // License data.
    $license_data = json_decode(wp_remote_retrieve_body($response));
    if(isset($license_data)) {
       foreach ($license_data as $key => $value) {
            if($key=='status') $status = $value;
        }
    }
    else $status = 'error';

    return $status;
}


//Lets check the license and redirect the front end if we need to
$status = check_license();

if($status=='error') {
    add_action('wp_head', redirect($error_page));
 }

if($status=='blocked') {
    add_action('wp_head', redirect($disabled_page));
 }

if($status=='expired') {
    add_action('wp_head', redirect($expired_page));
 }




//render the admin page for activtation 
function nmnd_license_management_page() {
    echo '<div class="wrap">';
    echo '<h2>NMND Deisgn License</h2>';

    /*** License activate button was clicked ***/
    if (isset($_REQUEST['activate_license'])) {
        $license_key = $_REQUEST['nmnd_license_key'];


//######################Edit/Added by ParaTheme ########################################//
        if(is_multisite())
            {
                $domain = site_url();
            }
        else
            {
                $domain = $_SERVER['SERVER_NAME'];
            }

//######################Edit/Added by ParaTheme ########################################//


        // API query parameters
        $api_params = array(
            'slm_action' => 'slm_activate',
            'secret_key' => YOUR_SPECIAL_SECRET_KEY,
            'license_key' => $license_key,
            'registered_domain' => $domain, // Edit/Added by ParaTheme
            'item_reference' => urlencode(YOUR_ITEM_REFERENCE),
        );

        // Send query to the license manager server
        $response = wp_remote_get(add_query_arg($api_params, YOUR_LICENSE_SERVER_URL), array('timeout' => 20, 'sslverify' => false));

        // Check for error in the response
        if (is_wp_error($response)){
            echo "Unexpected Error! The query returned with an error.";
        }

        //var_dump($response);//uncomment it if you want to look at the full response
        
        // License data.
        $license_data = json_decode(wp_remote_retrieve_body($response));
        
        // TODO - Do something with it.
        //var_dump($license_data);//uncomment it to look at the data
        
        if($license_data->result == 'success'){//Success was returned for the license activation
            
            //Uncomment the followng line to see the message that returned from the license server
            echo '<br />The following message was returned from the server: '.$license_data->message;
            
            //Save the license key in the options table
            update_option('nmnd_license_key', $license_key); 
        }
        else{
            //Show error to the user. Probably entered incorrect license key.
            
            //Uncomment the followng line to see the message that returned from the license server
            echo '<br />The following message was returned from the server: '.$license_data->message;
        }

    }
    /*** End of license activation ***/
    
    /*** License activate button was clicked ***/
    if (isset($_REQUEST['deactivate_license'])) {
        $license_key = $_REQUEST['nmnd_license_key'];

//######################Edit/Added by ParaTheme ########################################//

        if(is_multisite())
            {
                $domain = site_url();
            }
        else
            {
                $domain = $_SERVER['SERVER_NAME'];
            }
//######################Edit/Added by ParaTheme ########################################//


        // API query parameters
        $api_params = array(
            'slm_action' => 'slm_deactivate',
            'secret_key' => YOUR_SPECIAL_SECRET_KEY,
            'license_key' => $license_key,
            'registered_domain' => $domain, // Edit/Added by ParaTheme
            'item_reference' => urlencode(YOUR_ITEM_REFERENCE),
        );

        // Send query to the license manager server
        $response = wp_remote_get(add_query_arg($api_params, YOUR_LICENSE_SERVER_URL), array('timeout' => 20, 'sslverify' => false));

        // Check for error in the response
        if (is_wp_error($response)){
            echo "Unexpected Error! The query returned with an error.";
        }

        //var_dump($response);//uncomment it if you want to look at the full response
        
        // License data.
        $license_data = json_decode(wp_remote_retrieve_body($response));
        
        // TODO - Do something with it.
        //var_dump($license_data);//uncomment it to look at the data
        
        if($license_data->result == 'success'){//Success was returned for the license activation
            
            //Uncomment the followng line to see the message that returned from the license server
            echo '<br />The following message was returned from the server: '.$license_data->message;
            
            //Remove the licensse key from the options table. It will need to be activated again.
            update_option('nmnd_license_key', '');
        }
        else{
            //Show error to the user. Probably entered incorrect license key.
            
            //Uncomment the followng line to see the message that returned from the license server
            echo '<br />The following message was returned from the server: '.$license_data->message;
        }
        
    }
    /*** End license deactivation ***/
  


   //fetch the status from the function at the top 
    $status = check_license();

    //Spit out the visible admin panel pages
    ?>
    <?php if($status=='active') { ?>
            <div class="notice notice-success"> 
                    <p><strong>License Active.</strong></p>
                </div>
    <?php } ?>

   <?php if($status=='expired') { ?>
            <div class="notice notice-error"> 
                    <p><strong>License Expired.</strong></p>
                </div>
    <?php } ?>

   <?php if($status=='blocked') { ?>
            <div class="notice notice-error"> 
                    <p><strong>License Blocked.</strong></p>
                </div>
    <?php } ?>

   <?php if($status=='error') { ?>
            <div class="notice notice-error"> 
                    <p><strong>Licensing Server Error.</strong></p>
                </div>
    <?php } ?>




    <p>Please enter the license key for this website to activate it.</p>
    <form action="" method="post">
        <table class="form-table">
            <tr>
                <th style="width:100px;"><label for="nmnd_license_key">License Key</label></th>
                <td ><input class="regular-text" type="text" id="nmnd_license_key" name="nmnd_license_key"  value="<?php echo get_option('nmnd_license_key'); ?>" ></td>
            </tr>
        </table>
        <p class="submit">
           <?php if($status!='active') { ?> <input type="submit" name="activate_license" value="Activate" class="button-primary" /><?php } ?>
            <?php if($status=='active') { ?>  <input type="submit" name="deactivate_license" value="Deactivate" class="button" /><?php } ?>
        </p>
    </form>

    <?php
    
    echo '</div>';
}
