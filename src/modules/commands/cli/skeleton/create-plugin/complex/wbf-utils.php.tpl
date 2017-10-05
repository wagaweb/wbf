<?php

namespace {{ namespace }}\includes;

/**
 * Get the WBF path
 *
 * @return string|boolean
 */
function get_wbf_path(){
	return get_option('wbf_path',false);
}

/**
 * Get the WBF Plugin Autoloader
 */
function include_wbf_autoloader(){
	$wbf_path = get_wbf_path();

	if(!is_dir($wbf_path)){
		$wbf_path = ABSPATH."wp-content/plugins/wbf";
	}

	//Require the base autoloader
	$wbf_base_autoloader = $wbf_path."/wbf-autoloader.php";
	if(is_file($wbf_base_autoloader)){
		require_once $wbf_base_autoloader;
	}
}

/**
 * Builds the link to download WBF via dashboard.
 *
 * @return string
 */
function get_wbf_admin_download_link(){
	$url = self_admin_url('update.php?action=install-plugin&amp;plugin=wbf');
	$url = wp_nonce_url($url, 'install-plugin_wbf');
	return $url;
}

/**
 * Mod WordPress update system to install WBF from an external source.
 */
function install_wbf_wp_update_hooks(){
	add_filter('plugins_api_args', function(\stdClass $args){
		if(isset($args->slug) && $args->slug === 'wbf'){
			$args->fields['short_description'] = 'WordPress Extension Framework';
			$args->fields['homepage'] = 'https://www.waboot.io';
		}
		return $args;
	});
	add_filter('plugins_api', function($res, $action, $args){
		if(isset($args->slug) && $args->slug === 'wbf'){
			$info_url = "http://update.waboot.org/resource/info/plugin/wbf";
			$info_request = wp_remote_get($info_url);
			if(isset($info_request['response']) && $info_request['response']['code'] === 200){
				$info = json_decode($info_request['body']);
				$res = new \stdClass();
				$res->name = $info->name;
				$res->slug = $info->slug;
				$res->version = $info->version;
				$res->download_link = $info->download_url;
			}
		}
		return $res;
	},10,3);
}