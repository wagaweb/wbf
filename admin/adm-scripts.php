<?php

/**
 * Register wbf admin scripts
 */
function wbf_admin_scripts(){
	$deps = apply_filters("wbf/js/admin/deps",["jquery","backbone","underscore"]);
    if(defined("SCRIPT_DEBUG") && SCRIPT_DEBUG){
		$file_path = WBF_DIRECTORY."/assets/src/js/admin/wbf-admin-bundle.js";
		$file_url = WBF_URL."/assets/src/js/admin/wbf-admin-bundle.js";
    }else{
		$file_path = WBF_DIRECTORY."/admin/js/wbf-admin.min.js";
		$file_url = WBF_URL."/admin/js/wbf-admin.min.js";
    }
	if(is_file($file_path)){
		wp_register_script("wbf-admin",$file_url,$deps,filemtime($file_path),true);
		$wbfData = apply_filters("wbf/js/admin/localization",[
			'ajaxurl' => admin_url('admin-ajax.php'),
			'wpurl' => get_bloginfo('wpurl'),
			'wp_screen' => function_exists("get_current_screen") ? get_current_screen() : null,
			'isAdmin' => is_admin()
		]);
		wp_localize_script("wbf-admin","wbfData",$wbfData);
		wp_enqueue_script("wbf-admin");
	}
}
add_action( 'admin_enqueue_scripts', 'wbf_admin_scripts', 99);