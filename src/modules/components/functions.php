<?php
/**
 * @package   Components Module
 * @author    Riccardo D'Angelo <riccardo@waga.it>, WAGA <dev@waga.it>
 * @license   GPL-2.0+
 * @link      http://www.waboot.com
 * @copyright WAGA.it
 */

namespace WBF\modules\components;

/**
 * Get the components directory URI in the parent theme
 *
 * @uses get_root_dirname()
 *
 * @return string
 */
function get_root_components_directory_uri(){
    return get_template_directory_uri()."/".get_root_dirname()."/";
}

/**
 * Get the components directory URI in the child themes
 *
 * @uses get_child_dirname()
 *
 * @return string
 */
function get_child_components_directory_uri(){
    return get_stylesheet_directory_uri()."/".get_child_dirname()."/";
}

/**
 * Get the components directory PATH in the parent theme
 *
 * @uses get_root_dirname()
 *
 * @return string
 */
function get_root_components_directory(){
	$location = apply_filters("wbf/modules/components/root_directory_location",get_template_directory());
	$name = get_root_dirname();
	$directory = $location."/".$name."/";
    return $directory;
}

/**
 * Get the components directory PATH in child themes
 *
 * @uses get_child_dirname()
 *
 * @return string
 */
function get_child_components_directory(){
	$location = apply_filters("wbf/modules/components/child_directory_location",get_stylesheet_directory());
	$name = get_child_dirname();
	$directory = $location."/".$name."/";
	return $directory;
}

/**
 * Get the components directory name for parent theme
 *
 * @return mixed
 */
function get_root_dirname(){
	static $dirname;
	if(isset($dirname)) return $dirname;
	$dirname = apply_filters("wbf/modules/components/root_dirname","components");
	return $dirname;
}

/**
 * Get the components directory name for child themes
 *
 * @return mixed
 */
function get_child_dirname(){
	static $dirname;
	if(isset($dirname)) return $dirname;
	$dirname = apply_filters("wbf/modules/components/child_dirname","components");
	return apply_filters("wbf/modules/components/child_dirname","components");
}

/**
 * Prints out the status of a specific components
 *
 * @param array $comp_data
 */
function print_component_status($comp_data){
    if ( ComponentsManager::is_active( $comp_data ) ) {
        _ex("active","component status","wbf");
    } else {
        _ex("inactive","component status","wbf");
    }
}