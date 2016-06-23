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

require_once "CustomizerManager.php";
require_once "functions.php";
require_once "sanitization.php";

define('OPTIONS_FRAMEWORK_URL', \WBF::prefix_url('vendor/options-framework/'));
define('OPTIONS_FRAMEWORK_DIRECTORY', \WBF::prefix_url('vendor/options-framework/'));
if(!defined('WBF_OPTIONS_FRAMEWORK_THEME_ASSETS_DIR')){
	define('WBF_OPTIONS_FRAMEWORK_THEME_ASSETS_DIR',WBF()->resources->get_working_directory()."/options");
}

add_action( "wbf_init",'\WBF\modules\options\module_init', 11 );
add_action( "updated_option", '\WBF\modules\options\of_options_save', 9999, 3 );
add_action( "wbf/compiler/pre_compile", '\WBF\modules\options\of_create_styles', 9999, 3 );
add_filter( "wbf/compiler/parser/line/import", '\WBF\modules\options\of_parse_generated_file', 10, 5 );

/**
 * Font selector actions
 */
add_action("wp_ajax_gfontfetcher_getFonts",'\WBF\modules\options\FontSelector::getFonts');
add_action("wp_ajax_nopriv_gfontfetcher_getFonts",'\WBF\modules\options\FontSelector::getFonts');
add_action("wp_ajax_gfontfetcher_getFontInfo",'\WBF\modules\options\FontSelector::getFontInfo');
add_action("wp_ajax_nopriv_gfontfetcher_getFontInfo",'WBF\modules\options\FontSelector::getFontInfo');

/**
 * Adds theme options generated css
 */
add_action( 'wp_enqueue_scripts', '\WBF\modules\options\add_client_custom_css', 99 );

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

function optionsframework_init() {
	global $wbf_options_framework;

    // Instantiate the main plugin class.
    $options_framework = new Framework;
    $options_framework->init();

	$GLOBALS['wbf_options_framework'] = $options_framework; //todo: this is bad, found another way
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