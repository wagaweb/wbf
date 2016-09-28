<?php
/**
 * Options Framework WBF Edition
 *
 * As all other modules, keep in mind that this piece of code will be executed during "after_setup_theme"
 *
 * @package   Options Module
 * @author    Riccardo D'Angelo <riccardo@waga.it>, WAGA <dev@waga.it>
 * @license   GPL-2.0+
 * @link      http://www.waboot.com
 * @copyright WAGA.it
 * 
 * Based on Devin Price' Options_Framework
 */

namespace WBF\modules\options;

use WBF\components\utils\Utilities;
use WBF\modules\options\fields\CodeEditor;

require_once "CustomizerManager.php";
require_once "functions.php";

define('OPTIONS_FRAMEWORK_URL', \WBF::prefix_url('src/modules/options/'));
define('OPTIONS_FRAMEWORK_DIRECTORY', \WBF::prefix_url('src/modules/options/'));
if(!defined('WBF_OPTIONS_FRAMEWORK_THEME_ASSETS_DIR')){
	define('WBF_OPTIONS_FRAMEWORK_THEME_ASSETS_DIR',WBF()->resources->get_working_directory()."/options");
}

//Initialization
add_action( "wbf_init", __NAMESPACE__.'\\module_init', 11 );

//Backward compatibility hack:
add_action( "wbf_init", __NAMESPACE__."\\convert_old_theme_options", 12 );

/*
 * Options saving
 */
add_filter( "pre_update_option", __NAMESPACE__.'\\of_options_pre_save', 9999, 3 );
add_action( "updated_option", __NAMESPACE__.'\\of_options_save', 9999, 3 );

/*
 * Style compiler integration
 */
add_action( "wbf/compiler/pre_compile", __NAMESPACE__.'\\of_generate_style_file', 9999, 3 );
add_filter( "wbf/compiler/parser/line/import", __NAMESPACE__.'\\of_parse_generated_file', 10, 5 );

/*
 * Font selector actions
 */
add_action("wp_ajax_gfontfetcher_getFonts", __NAMESPACE__.'\\FontSelector::getFonts');
add_action("wp_ajax_nopriv_gfontfetcher_getFonts", __NAMESPACE__.'\\FontSelector::getFonts');
add_action("wp_ajax_gfontfetcher_getFontInfo", __NAMESPACE__.'\\FontSelector::getFontInfo');
add_action("wp_ajax_nopriv_gfontfetcher_getFontInfo", __NAMESPACE__.'\\FontSelector::getFontInfo');

/*
 * Adds theme options generated css
 */
add_action( 'wp_enqueue_scripts', __NAMESPACE__.'\\add_client_custom_css', 99 );

/**
 * Init the module
 *
 * @hooked 'wbf_init'
 */
function module_init(){
    add_action( 'init', '\WBF\modules\options\optionsframework_init', 20 );
	//Bind to Theme Customizer
	CustomizerManager::init();
}

/**
 * Init the framework
 *
 * @hooked 'init'
 */
function optionsframework_init() {
	global $wbf_options_framework;

    // Instantiate the main plugin class.
    $options_framework = new Framework;
    $options_framework->init();

	$GLOBALS['wbf_options_framework'] = $options_framework; //todo: this is bad, found another way
}

/**
 * From WBF 0.14.0 the "root_id" has changed from <theme-name> to <wbf_theme-name_options>.
 * This function transfer old theme options to the new one.
 *
 * @hooked 'wbf_init'
 */
function convert_old_theme_options(){
    $new_theme_options = get_option(Framework::get_options_root_id());
    if(is_array($new_theme_options) && !empty($new_theme_options)){
        return;
    }
    $theme = wp_get_theme();
    $old_theme_options = get_option($theme->get_stylesheet(),false);
    if($old_theme_options && is_array($old_theme_options) && !empty($old_theme_options)){
        update_option(Framework::get_options_root_id(),$old_theme_options);
        //delete_option($theme->get_stylesheet());
    }
}

/**
 * Helper function to return the theme option value.
 * If no value has been saved, it returns $default.
 * Needed because options are saved as serialized strings.
 *
 * Not in a class to support backwards compatibility in themes.
 */
function of_get_option( $name, $default = false ) {
    static $config = '';
	static $options_in_file = array();
	static $options = array();

	if(!is_array($config)) $config = Framework::get_options_root_id();

    //[WABOOT MOD] Tries to return the default value sets into $options array if $default is false
    if(!$default){
	    if(empty($options_in_file)) $options_in_file = Framework::get_registered_options();
        foreach($options_in_file as $opt){
            if(isset($opt['id']) && $opt['id'] == $name){
                if(isset($opt['std'])){
                    $default = $opt['std'];
                }
            }
        }
    }

    if(!isset($config) || !$config){
        return $default;
    }

    if(empty($options)) $options = get_option( $config );

    if ( isset( $options[$name] ) ) {
	    $value = $options[$name];
    }else{
	    $value = $default;
    }

	$value = apply_filters("wbf/theme_options/get/{$name}",$value);

    return $value;
}

/**
 * Adds client custom CSS
 */
function add_client_custom_css(){
	//if(is_admin()) return;
	$client_custom_css = CodeEditor::custom_css_exists();
	if($client_custom_css){
		$uri = Utilities::path_to_url($client_custom_css);
		$version = filemtime($client_custom_css);

		wp_enqueue_style('client-custom',$uri,false,$version);
	}
}