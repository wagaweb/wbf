<?php
/**
 * Waboot Framework Plugin file
 *
 * @wordpress-plugin
 * Plugin Name:       Waboot Framework
 * Plugin URI:        https://www.waboot.io
 * Description:       A comprehensive WordPress framework
 * Version:           1.1.0
 * Author:            WAGA Team <dev@waga.it>
 * Author URI:        https://www.waga.it/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wbf
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if(!function_exists("WBF")){
	/**
	 * Return the registered instance of WBF
	 *
	 * @return \WBF\PluginCore
	 */
	function WBF(){
		global $wbf;
		return $wbf;
	}
}

if( !isset($GLOBALS['wbf']) || !$GLOBALS['wbf'] instanceof \WBF\PluginCore ) {

	if (!defined('WBF_ENV')) {
		define('WBF_ENV', 'production');
	}

	//Utilities
	require_once( 'src/includes/utilities-functions.php' );

	require_once("wbf-autoloader.php");
	require_once("backup-functions.php");
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	$GLOBALS['wbf'] = new \WBF\PluginCore(
		dirname(__FILE__),
		\WBF\components\utils\Paths::path_to_url(dirname(__FILE__))
	);

	if(!defined("WBF_PREVENT_STARTUP")){
		if($GLOBALS['wbf']->is_plugin()){
			$GLOBALS['wbf']->startup();
		}
	}

}else{
	//HERE WBF IS ALREADY DEFINED. We can't tell if by a plugin or others... So...

	if(!defined("WBF_DIRECTORY")){
		define("WBF_DIRECTORY", __DIR__);
	}

	//If this is a plugin, then force the options to point over the plugin.
	if(preg_match("/plugins/",WBF_DIRECTORY."/wbf.php") && preg_match("/themes/",get_option("wbf_path"))){
		define("WBF_URL", site_url() . "/wp-content/plugins/wbf/");
		update_option( "wbf_path", WBF_DIRECTORY );
		update_option( "wbf_url", site_url() . "/wp-content/plugins/wbf/" );
	}

}; // class_exists check