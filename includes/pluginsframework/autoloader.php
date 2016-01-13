<?php

namespace WBF\includes\pluginsframework;

spl_autoload_register( 'WBF\includes\pluginsframework\plugin_autoload' );

function plugin_autoload( $class ) {
	$wbf_path = defined("WBF_DIRECTORY") ? WBF_DIRECTORY : get_option( "wbf_path" );

	require_once $wbf_path."/vendor/autoload.php";

	if ( $wbf_path ) {
		$plugin_main_class_dir = $wbf_path . "/includes/pluginsframework/";

		if ( preg_match( "/pluginsframework/", $class ) ) {
			$childclass = explode('\\', $class);
			$name = end($childclass);
			require_once( $plugin_main_class_dir.$name.'.php');
		}

		switch($class){
			case "WBF\admin\Notice_Manager":
				require_once($wbf_path . "/admin/notice-manager.php");
				break;
			case "WBF\includes\pluginsframework\License_Interface":
				require_once($wbf_path . "/includes/license-interface.php");
				break;
			case "WBF\includes\License_Interface":
				require_once($wbf_path . "/includes/license-interface.php");
				break;
			case "WBF\admin\License_Manager":
				require_once($wbf_path . "/admin/license-manager.php");
				break;
			case "WBF\includes\License":
				require_once($wbf_path . "/includes/class-license.php");
				break;
			case "WBF\includes\License_Exception":
				require_once($wbf_path . "/includes/class-license-exception.php");
				break;
			case "WBF\includes\Plugin_Update_Checker":
				require_once($wbf_path . "/includes/plugin-update-checker.php");
				break;
			/*case "PluginUpdateChecker":
			case "PluginUpdate":
			case "PluginInfo":
			case "PluginUpdateChecker_1_6":
			case "PluginInfo_1_6":
			case "PluginUpdate_1_6":
			case "PucFactory":
				require_once($wbf_path . "/vendor/plugin-updates/plugin-update-checker.php");
				break;*/
		}
	}
}