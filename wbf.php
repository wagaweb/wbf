<?php

/**
 * Waboot Framework Plugin file
 *
 * @link              http://www.waga.it
 * @package           WBF
 *
 * @wordpress-plugin
 * Plugin Name:       Waboot Framework
 * Plugin URI:        http://www.waga.it
 * Description:       WordPress Extension Framework
 * Version:           0.15.1
 * Author:            WAGA
 * Author URI:        http://www.waga.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wbf
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if( ! class_exists('\WBF\WBF') ) :

	if (!defined('WBF_ENV')) {
		define('WBF_ENV', 'production');
	}

	//Utilities
	require_once( 'src/includes/utilities-functions.php' );

	//Define directory
	if(!defined("WBF_DIRECTORY")){
		define("WBF_DIRECTORY", __DIR__);
	}
	//Define uri
	if(preg_match("/wp-content\/themes/", WBF_DIRECTORY )){
		//If WBF is in a theme
		$url = rtrim(path_to_url(dirname(WBF_DIRECTORY."/wbf.php")),"/")."/"; //ensure trailing slash
		define("WBF_URL", $url);
	}else{
		//If is in the plugin directory
		define("WBF_URL", get_bloginfo("url") . "/wp-content/plugins/wbf/");
	}
	define("WBF_ADMIN_DIRECTORY", WBF_DIRECTORY . "/admin");
	define("WBF_PUBLIC_DIRECTORY", WBF_DIRECTORY . "/public");

	/*if(!defined("WBF_THEME_DIRECTORY_NAME")){
		define("WBF_THEME_DIRECTORY_NAME","wbf");
	}*/

	if(!defined("WBF_WORK_DIRECTORY_NAME")){
		define("WBF_WORK_DIRECTORY_NAME","wbf-wd");
	}

	/*if(!defined("WBF_THEME_DIRECTORY")){
		define("WBF_THEME_DIRECTORY",rtrim(get_stylesheet_directory(),"/")."/".WBF_THEME_DIRECTORY_NAME);
	}*/

	if(!defined("WBF_WORK_DIRECTORY")){
		define("WBF_WORK_DIRECTORY", WP_CONTENT_DIR."/".WBF_WORK_DIRECTORY_NAME);
	}
	
	require_once("wbf-autoloader.php");
	require_once("backup-functions.php");
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	//Backward compatibility
	class WBF extends \WBF\PluginCore{}

	$GLOBALS['wbf'] = \WBF\PluginCore::getInstance();

else:
	//HERE WBF IS ALREADY DEFINED. We can't tell if by a plugin or others... So...

	if(!defined("WBF_DIRECTORY")){
		define("WBF_DIRECTORY", __DIR__);
	}

	//If this is a plugin, then force the options to point over the plugin.
	if(preg_match("/plugins/",WBF_DIRECTORY."/wbf.php") && preg_match("/themes/",get_option("wbf_path"))){
		define("WBF_URL", get_bloginfo("url") . "/wp-content/plugins/wbf/");
		define("WBF_ADMIN_DIRECTORY", WBF_DIRECTORY . "/admin");
		define("WBF_PUBLIC_DIRECTORY", WBF_DIRECTORY . "/public");
		update_option( "wbf_path", WBF_DIRECTORY );
		update_option( "wbf_url", get_bloginfo("url") . "/wp-content/plugins/wbf/" );
	}

endif; // class_exists check

if(!function_exists("WBF")){
	/**
	 * Return the registered instance of WBF
	 *
	 * @return WBF
	 */
	function WBF(){
		global $wbf;
		return $wbf;
	}
}