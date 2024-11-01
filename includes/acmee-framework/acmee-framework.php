<?php
/*
Plugin Name: Acmee Options Framework
Plugin URI: http://acmeedesign.com
Description: Options framework for Wordpress themes and plugins.
version: 1.2
Author: AcmeeDesign Softwares and Solutions
Author URI: http://acmeedesign.com
 */

/*
* AOF Constants
*/
define( 'WPAV_AOF_VERSION' , '1.2' );
define( 'WPAV_AOF_PATH' , dirname(__FILE__) . "/");
define( 'WPAV_AOF_DIR_URI' , plugin_dir_url(__FILE__) );

require_once ( WPAV_AOF_PATH . 'inc/aof.gfonts.class.php' );
require_once ( WPAV_AOF_PATH . 'inc/aof.class.php' );
