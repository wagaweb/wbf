<?php

/**
 * Register wbf admin scripts
 */
function wbf_admin_scripts(){
	$scripts = [
		"wbf-admin" => [
			'uri' => defined("SCRIPT_DEBUG") && SCRIPT_DEBUG ? \WBF\includes\Resources::getInstance()->prefix_url("assets/src/js/wbf-admin-bundle.js") : \WBF\includes\Resources::getInstance()->prefix_url("assets/dist/js/wbf-admin.min.js"),
			'path' => defined("SCRIPT_DEBUG") && SCRIPT_DEBUG ? \WBF\includes\Resources::getInstance()->prefix_path("assets/src/js/wbf-admin-bundle.js") : \WBF\includes\Resources::getInstance()->prefix_path("assets/dist/js/wbf-admin.min.js"),
			'deps' => apply_filters("wbf/js/admin/deps",["jquery","backbone","underscore"]),
			'i10n' => [
				'name' => 'wbfData',
				'params' => apply_filters("wbf/js/admin/localization",[
					'ajaxurl' => admin_url('admin-ajax.php'),
					'wpurl' => get_bloginfo('wpurl'),
					'wp_screen' => function_exists("get_current_screen") ? get_current_screen() : null,
					'isAdmin' => is_admin()
				])
			],
			'type' => 'js',
		]
	];
	$am = new \WBF\components\assets\AssetsManager($scripts);
	$am->enqueue();
}
add_action( 'admin_enqueue_scripts', 'wbf_admin_scripts', 99);