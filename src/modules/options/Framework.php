<?php
namespace WBF\modules\options;

use WBF\components\utils\Utilities;
use WBF\modules\options\fields\BaseField;

class Framework{

	/**
	 * @var Admin
	 */
	var $admin;

	/**
	 * @var array
	 */
	var $fields;

	public function __construct() {
		Framework::set_theme_option_default_root_id();

		//Create the framework working directory
		if(defined("WBF_OPTIONS_FRAMEWORK_THEME_ASSETS_DIR") && !is_dir(WBF_OPTIONS_FRAMEWORK_THEME_ASSETS_DIR)){
			Utilities::mkpath(WBF_OPTIONS_FRAMEWORK_THEME_ASSETS_DIR);
		}

		$this->admin = new Admin();

		//Loads up fields
		$fields = [
			'text' => "WBF\\modules\\options\\fields\\Text",
			'password' => "WBF\\modules\\options\\fields\\Password",
			'csseditor' => "WBF\\modules\\options\\fields\\CodeEditor",
			'typography' => "WBF\\modules\\options\\fields\\FontSelector",
			'gfont' => "WBF\\modules\\options\\fields\\FontSelector",
			'fonts_selector' => "WBF\\modules\\options\\fields\\MultipleFontSelector",
			'textarea' => "WBF\\modules\\options\\fields\\Textarea",
			'select' => "WBF\\modules\\options\\fields\\Select",
			'radio' => "WBF\\modules\\options\\fields\\Radio",
			'images' => "WBF\\modules\\options\\fields\\Images",
			'checkbox' => "WBF\\modules\\options\\fields\\Checkbox",
			'multicheck' => "WBF\\modules\\options\\fields\\Multicheck",
			'color' => "WBF\\modules\\options\\fields\\Color",
			'advanced_color' => "WBF\\modules\\options\\fields\\Advanced_Color",
			'upload' => "WBF\\modules\\options\\fields\\MediaUploader",
			'background' => "WBF\\modules\\options\\fields\\Background",
			'editor' => "WBF\\modules\\options\\fields\\Editor",
			'info' => "WBF\\modules\\options\\fields\\Info",
			'heading' => "WBF\\modules\\options\\fields\\Heading",
		];
		$fields = apply_filters("wbf/modules/options/fields/available",$fields);
		foreach ($fields as $name => $class){
			if(class_exists($class)){
				$f = new $class();
				$this->fields[$name] = $f;
				if($f instanceof BaseField){
					if(method_exists($f,"init")){
						$f->init();
					}
					if(method_exists($f,"sanitize")){
						add_filter( "of_sanitize_{$name}", [$f,"sanitize"], 10, 2 );
					}
					if(method_exists($f,"get_value")){
						add_filter( "wbf/theme_options/{$name}/get_value", [$f,"get_value"], 10 );
					}
				}
			}
		}

		$this->load_hooks();

		do_action("wbf/modules/options/after_init");
	}

	/**
	 * Initialize the framework.
	 */
	public function load_hooks(){
		add_action( "wbf_init_end", [$this,'register_options'] );
		add_action( "wbf_init_end", function(){
			$this->admin->init();
		}, 11 );
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

	public function register_options(){
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
	}

	/**
	 * Get current registered theme options.
	 *
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
	static function get_registered_options(){
		static $options = null;

		if ( !$options ) {
			$orgzr = Organizer::getInstance();
			$options = $orgzr->generate();
		}

		return $options;
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
	 * Retrieves an option value. If no value has been saved, it returns $default.
	 *
	 * @param $name
	 * @param bool $default
	 *
	 * @return mixed
	 */
	static function get_option($name, $default = null){
		static $config = '';
		static $options_in_file = array();
		static $options = array();

		if(!is_array($config)) $config = self::get_options_root_id();

		//[WABOOT MOD] Tries to return the default value sets into $options array if $default is null
		if(is_null($default)){
			if(empty($options_in_file)) $options_in_file = self::get_registered_options();
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
			$option_type = self::get_option_type($name);
			$value = $options[$name];
			$value = apply_filters("wbf/theme_options/{$option_type}/get_value",$value);
		}else{
			$value = $default;
		}

		$value = apply_filters("wbf/theme_options/get/{$name}",$value);

		return $value;
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
	 * Get the value of the wordpress option that tells the system under which wordpress option the current theme options are stored.
	 *
	 * Eg: optionsframework = "wbf_<theme-name>_theme_options"
	 *     wbf_<theme-name>_theme_options = current active theme options values
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

		$saved_options = apply_filters("wbf/modules/options/get_saved_options",$saved_options);

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

	static function get_registered_fields(){
		global $wbf_options_framework;
		if(isset($wbf_options_framework)){
			return $wbf_options_framework->fields;
		}
		return [];
	}

	/**
	 * Checks if $options is an option that can contain values (eg: not heading or info)
	 *
	 * @param $option
	 *
	 * @return bool
	 */
	static function option_can_have_value($option){
		global $wbf_options_framework;
		$fields = $wbf_options_framework->fields;
		if(isset($fields[$option['type']]) && $fields[$option['type']] instanceof BaseField){
			return $fields[$option['type']]->can_have_value();
		}
		return true; //todo: default to false?
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