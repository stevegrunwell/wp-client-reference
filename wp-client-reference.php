<?php
/**
 * Plugin Name: WP Client Reference
 * Plugin URI: http://stevegrunwell.com/wp-client-reference
 * Description: Create a reference guide for clients right in the WordPress administration area
 * Version: 0.4
 * Author: Steve Grunwell
 * Author URI: http://stevegrunwell.com
 * License: GPL2
 *
 * @author Steve Grunwell <stevegrunwell@gmail.com>
 * @package WordPress
 * @subpackage WP Client Reference
 * @version 0.4
 */

require_once dirname(__FILE__) . '/functions.php';

define('WPCLIENTREF_VIEWS_DIR', trailingslashit(dirname(__FILE__)) . 'views/');
define('WPCLIENTREF_STATUS_KEY_PATTERN', '_wpclientref_settings_%d');
$wpclientref;

/** Install the plugin dependencies upon activation */
register_activation_hook(__FILE__, array('WPClientReference', 'install'));

/**
 * Instantiate our WPClientReference class
 * @global $wpclientref
 * @return void
 */
function wpclientref_init(){
  global $wpclientref;
  $wpclientref = new WPClientReference;
  return;
}

/** Make sure we're only initializing the plugin in the admin area */
if( is_admin() ){
  add_action('init', 'wpclientref_init');
}

?>