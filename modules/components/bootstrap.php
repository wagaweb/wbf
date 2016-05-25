<?php
/*
 * WBF Component Framework
 *
 * As all other modules, keep in mind that this piece of code will be executed during "after_setup_theme"
 *
 * @package   Behaviors Framework
 * @author    Riccardo D'Angelo <riccardo@waga.it>
 * @license   copyrighted
 * @link      http://www.waga.it
 * @copyright WAGA.it
 */

namespace WBF\modules\components;

require_once "functions.php";

$GLOBALS['loaded_components'] = array();
$GLOBALS['registered_components'] = array();

function module_init(){
    ComponentsManager::init();
    ComponentsManager::toggle_components(); //enable or disable components if necessary (manage the disable\enable actions sent by admin page)
}
add_action("wbf_after_setup_theme",'\WBF\modules\components\module_init');

function setup_components(){
    //ComponentsManager::setupComponentsFilters();
    ComponentsManager::setupRegisteredComponents(); //Loads setup() methods of components
}
add_action("wbf_init",'\WBF\modules\components\setup_components', 12);

/**
 * Hides components tab in theme options
 * @since 0.13.12
 *
 * @param $tabs
 * @param array $options
 *
 * @hooked "wbf/modules/options/gui/tab_section/tabs"
 *
 * @return array
 */
function hide_components_tabs($tabs,$options){
	foreach($tabs as $k => $current_option){
		if(isset($current_option['component'])){
			unset($tabs[$k]);
		}
	}
	return $tabs;
}
add_filter("wbf/modules/options/gui/tab_section/tabs",'\WBF\modules\components\hide_components_tabs',10,2);

function hide_components_options($options){
	$current_screen = get_current_screen();
	if(preg_match("/components/",$current_screen->base)){
		return $options;
	}
	foreach($options as $k => $opt){
		if(isset($opt['component']) && $opt['component']){
			unset($options[$k]);
		}
	}
	return $options;
}
add_filter("wbf/modules/options/gui/options_to_render",'\WBF\modules\components\hide_components_options',10);

/**
 * WP HOOKS
 */

add_action( 'wbf_admin_submenu', '\WBF\modules\components\ComponentsManager::add_menu', 11 );
add_action( 'admin_enqueue_scripts', '\WBF\modules\components\ComponentsManager::scripts' );

function components_enqueue(){
    ComponentsManager::enqueueRegisteredComponent('wp_enqueue_scripts');
}
add_action('wp_enqueue_scripts', '\WBF\modules\components\components_enqueue');

function components_widgets(){
    ComponentsManager::registerComponentsWidgets();
}
add_action('widgets_init', '\WBF\modules\components\components_widgets');

function components_init(){
    ComponentsManager::enqueueRegisteredComponent('wp');
}
add_action('wp', '\WBF\modules\components\components_init');