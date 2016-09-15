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

use WBF\components\assets\AssetsManager;
use WBF\includes\Resources;

class CodeEditor {

	/**
	 * Init editor actions. Called by Framework->init()
	 */
	public function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
		add_action( 'updated_option', array( $this, 'on_save' ), 10, 3 );
	}

	/**
	 * Display the editor
	 *
	 * @param $_id
	 * @param $_value
	 * @param string $_desc
	 * @param string $_name
	 * @param string $_lang
	 *
	 * @return string
	 */
	static function display($_id, $_value, $_desc = '', $_name = '', $_lang = 'css'){
		$optionsframework_settings = Framework::get_options_framework_settings();

		// Gets the unique option id
		$option_name = $optionsframework_settings['id'];

		$output = '';
		$id     = '';
		$class  = '';
		$int    = '';
		$value  = '';
		$name   = '';

		$id = strip_tags( strtolower( $_id ) );

		// If a value is passed and we don't have a stored value, use the value that's passed through.
		if ( $_value != '' && $value == '' ) {
			$value = $_value;
		}

		if ( $_name != '' ) {
			$name = $_name;
		} else {
			$name = $option_name . '[' . $id . ']';
		}


		$class = "of-input codemirror";

		$output .= "<textarea id='$id' class='$class' name='$name' data-lang='$_lang' rows='8'>$value</textarea>";

		/*$output .= "<script>
		var editor = CodeMirror.fromTextArea(document.getElementById('{$id}'), {
		  mode: 'css',
		  lineNumbers: true
		});
		</script>";*/

		return $output;
	}

	/**
	 * Get all options that are registered as css editor
	 *
	 * @return array
	 */
	public function get_csseditor_options(){
		$options = Framework::get_registered_options();
		$css_editor_options = [];
		foreach($options as $opt){
			if(isset($opt['type']) && isset($opt['name']) && isset($opt['id']) && $opt['type'] == "csseditor"){
				$css_editor_options[] = $opt['id'];
			}
		}
		return $css_editor_options;
	}

	/**
	 * Enqueue CSS Editor scripts
	 *
	 * @param $hook
	 *
	 * @throws \Exception
	 */
	function scripts( $hook ) {
		if(!of_is_admin_framework_page($hook)){
			return;
		}

		$res = [
			'codemirror' => [
				'uri' => Resources::getInstance()->prefix_url('vendor/codemirror/lib/codemirror.js'),
				'path' => Resources::getInstance()->prefix_path('vendor/codemirror/lib/codemirror.js'),
				'type' => 'js'
			],
			'codemirror-css' => [
				'uri' => Resources::getInstance()->prefix_url('vendor/codemirror/lib/codemirror.css'),
				'path' => Resources::getInstance()->prefix_path('vendor/codemirror/lib/codemirror.css'),
				'type' => 'css'
			],
			//Modes
			'codemirror-mode-css' => [
				'uri' => Resources::getInstance()->prefix_url('vendor/codemirror/mode/css/css.js'),
				'path' => Resources::getInstance()->prefix_path('vendor/codemirror/mode/css/css.js'),
				'type' => 'css'
			],
			//Addons
			'codemirror-addon-hint' => [
				'uri' => Resources::getInstance()->prefix_url('vendor/codemirror/addon/hint/show-hint.js'),
				'path' => Resources::getInstance()->prefix_path('vendor/codemirror/addon/hint/show-hint.js'),
				'type' => 'js'
			],
			'codemirror-addon-hint-style' => [
				'uri' => Resources::getInstance()->prefix_url('vendor/codemirror/addon/hint/show-hint.css'),
				'path' => Resources::getInstance()->prefix_path('vendor/codemirror/addon/hint/show-hint.css'),
				'type' => 'css'
			],
			'codemirror-addon-hint-css' => [
				'uri' => Resources::getInstance()->prefix_url('vendor/codemirror/addon/hint/css-hint.js'),
				'path' => Resources::getInstance()->prefix_path('vendor/codemirror/addon/hint/css-hint.js'),
				'deps' => ['codemirror','codemirror-addon-hint'],
				'type' => 'js'
			],
			//Themes
			'codemirror-theme-ambiance' => [
				'uri' => Resources::getInstance()->prefix_url('vendor/codemirror/theme/ambiance.css'),
				'path' => Resources::getInstance()->prefix_path('vendor/codemirror/theme/ambiance.css'),
				'type' => 'css'
			]
		];
		
		$am = new AssetsManager($res);
		$am->enqueue();
		
        /*if(WBF_ENV == "dev"){
            wp_register_script('of-waboot-codeditor', WBF_URL . '/assets/src/js/admin/code-editor.js', array('jquery', 'codemirror', 'underscore'), Framework::VERSION );
        }else{
            wp_register_script('of-waboot-codeditor', WBF_URL . '/admin/js/code-editor.min.js', array('jquery', 'codemirror', 'underscore'), Framework::VERSION );
        }*/

		//wp_enqueue_script( 'of-waboot-codeditor' );
	}

	/**
	 * Save the custom editor values to a file
	 *
	 * @hooked 'updated_option'
	 *
	 * @param $option
	 * @param $old_value
	 * @param $value
	 */
	function on_save( $option, $old_value, $value ) {
        if(!is_admin()) return;
		if(is_array($value)){
			$valid_options = $this->get_csseditor_options();
			foreach($valid_options as $opt_id){
				if(array_key_exists($opt_id,$value)){
					$content[] = $value[$opt_id];
				}
			}
			if(isset($content) && is_array($content)){
				$new_css = implode("\n",$content);
				$filename = apply_filters("wbf/modules/options/custom_css_filename","client-custom.css");
				if(is_string($filename)){
					$filepath = WBF_OPTIONS_FRAMEWORK_THEME_ASSETS_DIR."/".$filename;
					if(is_dir(dirname($filepath))){
						//Create the file
						$result = file_put_contents( $filepath, $new_css );
					}
				}
			}
		}
	}

	/**
	 * Checks if there is an autogenerated custom client css file and returns it. Returns FALSE otherwise.
	 *
	 * @return string|bool
	 */
	static function custom_css_exists() {
		$filepath = WBF_OPTIONS_FRAMEWORK_THEME_ASSETS_DIR."/client-custom.css"; //todo: make file name customizable
		if (is_file($filepath)) {
			return $filepath;
		}
		return false;
	}
}