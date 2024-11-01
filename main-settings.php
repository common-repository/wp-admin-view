<?php
/*
 * WPAV
  * @author   Krish Johnson (krishjohnson)
 * @url     http://100utils.com
*/

defined('ABSPATH') || die;

/*
*   WPAV menu slug Constant
*/
define( 'WPAV_MENU_SLUG' , 'wpav-options' );

/*
*   WPAV users list slug Constant
*/
define( 'WPAV_ADMIN_USERS_SLUG' , 'wpav_admin_users' );

/*
*   WPAV admin bar items list Constant
*/
define( 'WPAV_ADMINBAR_LIST_SLUG' , 'wpav_adminbar_list' );

//AOF Framework Implementation
require_once( WPAV_PATH . 'includes/acmee-framework/acmee-framework.php' );

//Instantiate the AOF class
$wpav_aof_options = new AcmeeFramework();

add_action( 'admin_enqueue_scripts', 'wpav_aofAssets', 99 );
function wpav_aofAssets($page) {
  if( $page != "toplevel_page_wpav-options" )
      return;
  wp_enqueue_script( 'jquery' );
  wp_enqueue_script( 'jquery-ui-core' );
  wp_enqueue_script( 'jquery-ui-sortable' );
  wp_enqueue_script( 'jquery-ui-slider' );
  wp_enqueue_style('wpav_aofOptions-css', WPAV_AOF_DIR_URI . 'assets/css/aof-framework.css');
  wp_enqueue_style('wpav_aof-ui-css', WPAV_AOF_DIR_URI . 'assets/css/jquery-ui.css');
  wp_enqueue_script( 'wpav_responsivetabsjs', WPAV_AOF_DIR_URI . 'assets/js/easyResponsiveTabs.js', array( 'jquery' ), '', true );
  // Add the color picker css file
  wp_enqueue_style( 'wp-color-picker' );
  wp_enqueue_script( 'wpav_aof-scriptjs', WPAV_AOF_DIR_URI . 'assets/js/script.js', array( 'jquery', 'wp-color-picker' ), false, true );

}

add_action('admin_menu', 'wpav_createOptionsmenu');
function wpav_createOptionsmenu() {
  $aof_page = add_menu_page( 'WP Admin View', 'WP Admin View', 'manage_options', 'wpav-options', 'wpav_generateFields', WPAV_DIR_URI . '/assets/images/wpav.png' );
}

function wpav_generateFields() {
  global $wpav_aof_options;
  $config = wpav_config();
  $wpav_aof_options->generateFields($config);
}

add_action('admin_menu', 'wpav_SaveSettings');
function wpav_SaveSettings() {
	global $wpav_aof_options;
	if($_POST) {
		if ( isset($_POST['aof_options_nonce']) && wp_verify_nonce($_POST['aof_options_nonce'], 'aof_options_form') ){
			$setting = array_map('sanitize_text_field',$_POST);
			$wpav_aof_options->SaveSettings($setting);	
		}
	}
}
