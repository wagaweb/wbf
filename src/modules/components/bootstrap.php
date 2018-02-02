<?php
/**
 * WBF Component Framework
 *
 * As all other modules, keep in mind that this piece of code will be executed during "after_setup_theme"
 */

namespace WBF\modules\components;

require_once __DIR__.'/functions.php';

$GLOBALS['loaded_components'] = array();
$GLOBALS['registered_components'] = array();

/**
 * Backup registered components (for backward compatibility)
 */
function backup_current_components_states(){
	$parent_registered_components = ComponentsManager::get_registered_components();
	$child_registered_components = ComponentsManager::get_registered_components(true);
	if(!get_option("wbf_components_states_backuped_once",false)){
		//Backup
		update_option(get_template()."_registered_components_backup",$parent_registered_components);
		update_option(get_stylesheet()."_registered_components_backup",$child_registered_components);
		update_option("wbf_components_states_backuped_once",true);
		//Update the states
		$states = [];
		if($parent_registered_components && is_array($parent_registered_components) && !empty($parent_registered_components)){
			foreach ($parent_registered_components as $component_name => $params){
				$states[$component_name] = isset($params['enabled']) && $params['enabled'] ? 1 : 0;
			}
		}
		if($child_registered_components && is_array($child_registered_components) && !empty($child_registered_components)){
			foreach ($child_registered_components as $component_name => $params){
				$states[$component_name] = isset($params['enabled']) && $params['enabled'] ? 1 : 0;
			}
		}
		ComponentsManager::update_components_state($states);
	}
}
add_action("wbf/modules/components/before_init", __NAMESPACE__."\\backup_current_components_states");

function module_init(){
    ComponentsManager::init();
    ComponentsManager::toggle_components(); //enable or disable components if necessary (manage the disable\enable actions sent by admin page)
}
add_action("wbf_init",'\WBF\modules\components\module_init', 10);

function setup_components(){
    ComponentsManager::setupComponentsFilters();
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

add_action( 'wbf_admin_submenu', '\WBF\modules\components\GUI::add_menu', 11 );
add_action( 'admin_enqueue_scripts', '\WBF\modules\components\GUI::scripts' );

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