<?php

namespace WBSample\includes;

/**
 * Get WBF Path
 * @return string|boolean
 * @throws \Exception
 */
function get_wbf_path(){
	$wbf_path = get_option( "wbf_path" );
	if(!$wbf_path) return false;
	return $wbf_path;
}

/**
 * Get the WBF Plugin Autoloader
 */
function include_wbf_autoloader(){
	try{
		$wbf_path = get_wbf_path();
	}catch(\Exception $e){
		$wbf_path = ABSPATH."wp-content/plugins/wbf";
	}

	//Require the base autoloader
	$wbf_base_autoloader = $wbf_path."/wbf-autoloader.php";
	if(is_file($wbf_base_autoloader)){
		require_once $wbf_base_autoloader;
	}
}