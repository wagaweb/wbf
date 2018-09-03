<?php
/**
 * Waboot Framework Plugin file
 *
 * @wordpress-plugin
 * Plugin Name:       Waboot Framework
 * Plugin URI:        https://www.waboot.io
 * Description:       A comprehensive WordPress framework
 * Version:           1.1.3
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
	 * @throws Exception
	 */
	function WBF(){
		global $wbf;
		if($wbf instanceof \WBF\PluginCore){
			return $wbf;
		}else{
			throw new \Exception('WBF() does not have returned an instance of WBF. Is it the framework initialized?');
		}
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

}