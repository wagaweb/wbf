<?php

namespace WBF\extensions\acf;

// ACF INTEGRATION
if(!is_plugin_active("advanced-custom-fields-pro/acf.php") && !is_plugin_active("advanced-custom-fields/acf.php")){
	require_once \WBF::get_path().'vendor/acf/acf.php';
	require_once 'acf-integration.php';
}

add_filter("wbf/utilities/get_filtered_post_types/blacklist",'\WBF\extensions\acf\add_acf_post_types_to_invalid_for_behaviors');
function add_acf_post_types_to_invalid_for_behaviors($post_types){
	$post_types[] = "acf-field-group";
	$post_types[] = "acf-field";
	return $post_types;
}