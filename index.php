<?php
/*
Plugin Name: WP Admin View
Plugin URI: https://100utils.com
Description: WP Admin View plugin provide several options to customize WordPress Admin theme, elements & views.
Version: 1.0.0
Author: Krish Johnson (krishjohnson)
Author URI: https://www.paypal.me/pawan1085
Text-Domain: wpav
Domain Path: /languages
 *
*/

/*
*   WPAV Version
*/

define( 'WPAV_VERSION' , '5.0.3' );

/*
*   WPAV Path Constant
*/
define( 'WPAV_PATH' , dirname(__FILE__) . "/");

/*
*   WPAV URI Constant
*/
define( 'WPAV_DIR_URI' , plugin_dir_url(__FILE__) );

/*
*   WPAV Options slug Constant
*/
define( 'WPAV_OPTIONS_SLUG' , 'wpav_options' );

/*
* Enabling Global Customization for Multi-site installation.
* Delete below two lines if you want to give access to all blog admins to customizing their own blog individually.
* Works only for multi-site installation
*/
if(is_multisite())
    define('NETWORK_ADMIN_CONTROL', true);
// Delete the above two lines to enable customization per blog


require_once( WPAV_PATH . 'includes/wpav-options.php' );

/*
 * Main configuration for AOF class
 */

if(!function_exists('wpav_config')) {
  function wpav_config() {
    if(!is_multisite()) {
        $multi_option = false;
    }
     elseif(is_multisite() && !defined('NETWORK_ADMIN_CONTROL')) {
         $multi_option = false;
     }
     else {
         $multi_option = true;
     }

     /* Stop editing after this */
     $wpav_fields = get_wpav_options();
     $config = array(
         'multi' => $multi_option, //default = false
         'wpav_fields' => $wpav_fields,
       );

       return $config;
  }
}

//Implement main settings
require_once( WPAV_PATH . 'main-settings.php' );

function wpav_load_textdomain(){
   load_plugin_textdomain('wpav', false, dirname( plugin_basename( __FILE__ ) )  . '/languages' );
}
add_action('plugins_loaded', 'wpav_load_textdomain');

include_once WPAV_PATH . 'includes/fa-icons.class.php';
include_once WPAV_PATH . 'includes/wpav.class.php';
include_once WPAV_PATH . 'includes/wpavmenu.class.php';
include_once WPAV_PATH . 'includes/wpavthemes.class.php';
include_once WPAV_PATH . 'includes/wpav-impexp.class.php';
//include_once WPAV_PATH . 'includes/deactivate.class.php';
