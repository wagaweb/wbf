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
    ComponentsManager::setupComponentsFilters();
    ComponentsManager::setupRegisteredComponents(); //Loads setup() methods of components
}
add_action("wbf_init",'\WBF\modules\components\setup_components', 12);

/**
 * Hides components tab in theme options
 *
 * @param string $section_html
 * @param array $current_option
 * @param array $options
 *
 * @hooked "wbf/modules/options/gui/tab_section/html"
 *
 * @since 0.13.12
 *
 * @return string
 */
function hide_components_tabs($section_html,$current_option,$options){
	if(isset($current_option['component'])){
		$section_html = "";
	}
	return $section_html;
}
add_filter("wbf/modules/options/gui/tab_section/html",'\WBF\modules\components\hide_components_tabs',10,3);

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