<?php
/**
 * Apply custom stylesheet to admin panel
 *
 * @since 0.1.0
 * @uses wbf_locate_template_uri()
 */
function wbf_admin_styles() {
	$styles = [
		'wbf-admin-style' => [
			'uri' => \WBF\includes\Resources::getInstance()->prefix_url('assets/dist/css/admin.css'),
			'path' => \WBF\includes\Resources::getInstance()->prefix_path('assets/dist/css/admin.css'),
			'type' => 'css'	
		]
	];
	$am = new \WBF\components\assets\AssetsManager($styles);
	$am->enqueue();
}
add_action('admin_enqueue_scripts', 'wbf_admin_styles');