<?php
/**
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

class Framework{

	/**
	 * @var Admin
	 */
	var $admin;

	/**
	 * @var array
	 */
	var $extensions;

	/**
	 * Initialize the framework.
	 */
	public function init(){
		Framework::set_theme_option_default_root_id();
		//Create the framework working directory
		if(defined("WBF_OPTIONS_FRAMEWORK_THEME_ASSETS_DIR") && !is_dir(WBF_OPTIONS_FRAMEWORK_THEME_ASSETS_DIR)){
			Utilities::mkpath(WBF_OPTIONS_FRAMEWORK_THEME_ASSETS_DIR);
		}

		$this->admin = new Admin();
		$this->admin->init();

		//Loads up extensions todo: refactor a bit
		$mu = new MediaUploader();
		$mu->init();
		$ce = new CodeEditor();
		$ce->init();
		$fs = new FontSelector();
		$fs->init();
		$ac = new Advanced_Color();
		$ac->init();
		$this->extensions = [$mu,$ce,$fs];
		
		do_action("wbf/modules/options/after_init");
	}

	/**
	 * Sets defaults theme options root id
	 */
	static function set_theme_option_default_root_id() {
		// Load current theme
		$current_theme_name = wp_get_theme()->get_stylesheet();
		$current_theme_name = preg_replace("/\W/", "_", strtolower($current_theme_name));
		$current_root_id = "wbf_".$current_theme_name."_options";
		self::set_options_root_id($current_root_id);
	}

	/**
	 * Get current registered theme options.
	 *
	 * @alias-of Framework::_optionsframework_options()
	 * @return array
	 */
	static function &get_registered_options(){
		return self::_optionsframework_options();
	}

	/**
	 * Get the options of type $type among the current registered options
	 *
	 * @param $type
	 *
	 * @return array
	 */
	static function get_registered_options_of_type($type){
		$registered_options = self::get_registered_options();
		$registered_options_of_type = [];
		foreach ($registered_options as $opt){
			if(isset($opt['type'])){
				if(is_array($type) && in_array($opt['type'],$type)){
					$registered_options_of_type[] = $opt;
				}elseif($opt['type'] === $type){
					$registered_options_of_type[] = $opt;
				}
			}
		}
		return $registered_options_of_type;
	}

    /**
     * Get current registered theme options.
     * The functions use the filter "options_framework_location" to determine options file existance and location, then try to call the function "optionsframework_options()".
     * At the end it calls the action "wbf/theme_options/register" and the filter "of_options" (with the current $options as parameter)
     *
     * Allows for manipulating or setting options via 'of_options' filter
     * For example:
     *
     * <code>
     * add_filter( 'of_options', function( $options ) {
     *     $options[] = array(
     *         'name' => 'Input Text Mini',
     *         'desc' => 'A mini text input field.',
     *         'id' => 'example_text_mini',
     *         'std' => 'Default',
     *         'class' => 'mini',
     *         'type' => 'text'
     *     );
     *
     *     return $options;
     * });
     * </code>
     *
     * Also allows for setting options via a return statement in the
     * options.php file.  For example (in options.php):
     *
     * <code>
     * return array(...);
     * </code>
     *
     * @return array (by reference)
     */
    static function &_optionsframework_options() {
        static $options = null;

        if ( !$options ) {
            // Load options from options.php file (if it exists)
            $locations = apply_filters( 'options_framework_location', false ); //todo: will be deprecated
            $locations = apply_filters( 'wbf/modules/options/include_file', $locations );

			if(is_array($locations)){
				foreach($locations as $loc){
					if(is_file($loc)){
						require_once $loc;
					}else{
						$r = locate_template( $loc, true );
					}
				}
			}elseif(is_string($locations)){
				if(is_file($locations)){
					require_once $locations;
				}else{
					$r = locate_template( $locations, true );
				}
			}

	        $orgzr = Organizer::getInstance();

	        do_action("wbf/theme_options/register",$orgzr); //This action can hook different functions to of_options filter (is used by Component Manager for example)

            // Allow setting/manipulating options via filters
	        $orgzr->reset_section();
	        $orgzr->reset_group();
	        $additional_options = [];
            $additional_options = apply_filters( 'of_options', $additional_options ); //todo: will be deprecated
            $additional_options = apply_filters( 'wbf/modules/options/available', $additional_options );
	        if(is_array($additional_options) && !empty($additional_options)){
		        foreach($additional_options as $opt){
			        $orgzr->add($opt);
		        }
	        }

	        $options = $orgzr->generate();
        }

        return $options;
    }

	/**
	 * Update theme options with new values
	 *
	 * @param $values
	 *
	 * @param bool $merge if YES the $values will be merged with already saved options
	 * @param bool|array $validate_against if ARRAY, this will be passed as $base argument to validate_options()
	 *
	 * @return array|bool
	 */
	static function update_theme_options($values,$merge = false,$validate_against = false){
		$new_options = Admin::validate_options($values,$validate_against);
		if(!$values || !is_array($values)){
			return false;
		}
		$options_to_update = $new_options;
		if($merge){
			$of_options = Framework::get_options_values(); //Gets the the saved options values
			$new_options = wp_parse_args($new_options,$of_options); //Merge the arrays
		}else{
			$new_options = $values;
		}
		$id = self::get_options_root_id();
		if(!update_option($id,$new_options)){
			return false;
		}else{
			return $options_to_update;	
		}
	}

	static function reset_theme_options(){
		$id = self::get_options_root_id();
		return delete_option($id);
	}

	/**
	 * Set a new value for a specific theme option
	 * @param $id
	 * @param $value
	 *
	 * @return bool
	 */
	static function set_option_value($id,$value){
		global $wp_settings_errors;
		$bak_settings_errors = get_settings_errors();

		$options = Framework::get_options_values();
		if(isset($options[$id])){
			$options[$id] = $value;
		}else{
			$defaults = Framework::get_default_values();
			if(isset($defaults[$id])){
				$options[$id] = $value;
			}
		}

		//Remove actions and settings errors
		remove_action( "updated_option", '\WBF\modules\options\of_options_save', 9999);
		$wp_settings_errors = array();

		$result = update_option(self::get_options_root_id(),$options); //update...

		//Read...
		add_action( "updated_option", '\WBF\modules\options\of_options_save', 9999, 3 );
		$wp_settings_errors = $bak_settings_errors;

		return $result;
	}

	/**
	 * Get the option entity for the specified ID
	 * @param string $id
	 *
	 * @return array|false
	 */
	static function get_option_object($id){
		$all_options = self::get_registered_options();
		foreach($all_options as $opt){
			if(isset($opt['id']) && $opt['id'] == $id){
				return $opt;
			}
		}
		return false;
	}

	/**
	 * Get the "type" of the specified option ID
	 * @param string $id
	 *
	 * @return bool
	 */
	static function get_option_type($id){
		$option = self::get_option_object($id);
		if(isset($option['type']))
			return $option['type'];
		else
			return false;
	}

	/**
	 * Get if the specified option must recompile styles or not
	 * @param $id
	 *
	 * @return bool
	 */
	static function option_must_recompile_styles($id){
		$option = self::get_option_object($id);
		return isset($option['recompile_styles']) && $option['recompile_styles'];
	}

	/**
	 * Get the option that contains the current active options key.
	 */
	static function get_options_framework_settings(){
		$opt_root = get_option('optionsframework');
		return $opt_root;
	}

	/**
	 * Update the option that contains the current active options key
	 * 
	 * @param $settings
	 */
	static function set_options_framework_settings($settings){
		update_option('optionsframework', $settings);
	}

	/**
	 * Get the current options root id (the name of the option that contains the current valid options. Default to the current theme name)
	 * @return string|false
	 */
	static function get_options_root_id(){
		$opt_root = self::get_options_framework_settings();
		if(isset($opt_root['id'])){
			return $opt_root['id'];
		}
		return false;
	}

	static function set_options_root_id($id){
		$opt_root = self::get_options_framework_settings();
		if(!is_array($opt_root)) $opt_root = [];
		$opt_root['id'] = $id;
		self::set_options_framework_settings($opt_root);
	}

	/**
	 * Get all currently valid options
	 * @return array|false
	 */
	static function get_options_values(){
		$opt_id = self::get_options_root_id();
		if($opt_id){
			$values = get_option($opt_id);
		}

		if(!isset($values) || !$values || empty($values)){
			//Returns the defaults
			$values = self::get_default_values();
		}

		if(is_array($values) && !empty($values)){
			return $values;
		}

		return false;
	}

	/**
	 * Get the saved options only
	 */
	static function get_saved_options(){
		$optionsframework_settings = Framework::get_options_root_id();
		// Gets the unique option id
		if ($optionsframework_settings) {
			$options_db_key = $optionsframework_settings;
		} else {
			$options_db_key = 'optionsframework';
		}
		$saved_options = get_option($options_db_key);	
		return $saved_options;
	}

	static function get_options_values_filtered(){
		$options = self::get_options_values();
		foreach($options as $k => $v){
			$options[$k] = apply_filters("wbf/theme_options/get/{$k}",$v);
		}
		return $options;
	}

	/**
	 * Get the default values for all the theme options
	 *
	 * Get an array of all default values as set in
	 * options.php. The 'id','std' and 'type' keys need
	 * to be defined in the configuration array. In the
	 * event that these keys are not present the option
	 * will not be included in this function's output.
	 *
	 * @return array Re-keyed options configuration array.
	 *
	 */
	static function get_default_values() {
		$output = array();
		$config = Framework::get_registered_options();
		foreach ( (array) $config as $option ) {
			if ( ! isset( $option['id'] ) ) {
				continue;
			}
			if ( ! isset( $option['std'] ) ) {
				continue;
			}
			if ( ! isset( $option['type'] ) ) {
				continue;
			}
			if ( has_filter( 'of_sanitize_' . $option['type'] ) ) {
				$output[$option['id']] = apply_filters( 'of_sanitize_' . $option['type'], $option['std'], $option );
			}
		}
		return $output;
	}

	/**
	 * Returns all theme options values of options with specified $suffix
	 * @param $suffix
	 *
	 * @return array
	 */
	static function get_options_values_by_suffix($suffix){
		$options = self::get_options_values();
		$results = [];
		foreach($options as $k => $v){
			if(preg_match("/^({$suffix})/",$k)){
				$results[$k] = $v;
			}
		}
		return $results;
	}

	/**
	 * Checks if $options is an option that can contain values (eg: not heading or info)
	 *
	 * @param $option
	 *
	 * @return bool
	 */
	static function is_valuable_option($option){
		return $option['type'] != "heading" && $option['type'] != "info";
	}

	/**
	 * Sanitize and standardize an option in
	 * 
	 * @param $id
	 *
	 * @return mixed
	 */
	static function sanitize_option_id($id){
		//$id = preg_replace( '/[^a-zA-Z0-9._\-]/', '', strtolower( $id ) );
		$id = preg_replace( '/[^a-zA-Z0-9._\-]/', '', $id );
		return $id;
	}
}