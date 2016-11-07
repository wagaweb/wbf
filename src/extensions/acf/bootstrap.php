<?php

namespace WBF\extensions\acf;

add_filter("wbf/utilities/get_filtered_post_types/blacklist",'\WBF\extensions\acf\add_acf_post_types_to_invalid_for_behaviors');
function add_acf_post_types_to_invalid_for_behaviors($post_types){
	$post_types[] = "acf-field-group";
	$post_types[] = "acf-field";
	return $post_types;
}

add_action('acf/include_field_types', '\WBF\extensions\acf\wbf_include_field_types');
function wbf_include_field_types(){
	//MultipleFileUpload:
	include_once("acfFields/MultipleFileUpload.php");
	new acfFields\MultipleFileUpload();
	//wcfGallery:
	include_once("acfFields/wcfGallery.php");
	new acfFields\wcfGallery();
}