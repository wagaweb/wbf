<?php
namespace WBF\modules\options\fields;

use WBF\components\assets\AssetsManager;
use WBF\includes\Resources;
use WBF\modules\options\fields\BaseField;
use WBF\modules\options\fields\Field;
use WBF\modules\options\Framework;

class CodeEditor extends BaseField  implements Field  {

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
	 * @param string $_lang
	 *
	 * @return string
	 */
	public function get_html(){
		$_lang = 'css';

		$class = "of-input codemirror";
		$output = "<textarea id='{$this->get_field_name()}' class='$class' name='{$this->get_field_name()}' data-lang='$_lang' rows='8'>{$this->value}</textarea>";
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
		if(!\WBF\modules\options\of_is_admin_framework_page($hook)){
			return;
		}

		$res = [
			'codemirror' => [
				'uri' => WBF()->prefix_url('assets/vendor/codemirror/js/codemirror.js'),
				'path' => WBF()->prefix_path('assets/vendor/codemirror/js/codemirror.js'),
				'type' => 'js'
			],
			'codemirror-css' => [
				'uri' => WBF()->prefix_url('assets/vendor/codemirror/css/codemirror.css'),
				'path' => WBF()->prefix_path('assets/vendor/codemirror/css/codemirror.css'),
				'type' => 'css'
			],
			//Modes
			'codemirror-mode-css' => [
				'uri' => WBF()->prefix_url('assets/vendor/codemirror/js/css.js'),
				'path' => WBF()->prefix_path('assets/vendor/codemirror/js/css.js'),
				'type' => 'css'
			],
			//Addons
			'codemirror-addon-hint' => [
				'uri' => WBF()->prefix_url('assets/vendor/codemirror/js/show-hint.js'),
				'path' => WBF()->prefix_path('assets/vendor/codemirror/js/show-hint.js'),
				'type' => 'js'
			],
			'codemirror-addon-hint-style' => [
				'uri' => WBF()->prefix_url('assets/vendor/codemirror/css/show-hint.css'),
				'path' => WBF()->prefix_path('assets/vendor/codemirror/css/show-hint.css'),
				'type' => 'css'
			],
			'codemirror-addon-hint-css' => [
				'uri' => WBF()->prefix_url('assets/vendor/codemirror/js/css-hint.js'),
				'path' => WBF()->prefix_path('assets/vendor/codemirror/js/css-hint.js'),
				'deps' => ['codemirror','codemirror-addon-hint'],
				'type' => 'js'
			],
			//Themes
			'codemirror-theme-ambiance' => [
				'uri' => WBF()->prefix_url('assets/vendor/codemirror/css/ambiance.css'),
				'path' => WBF()->prefix_path('assets/vendor/codemirror/css/ambiance.css'),
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
				$new_css = stripslashes($new_css);
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

	public function sanitize( $input, $option ) {
		global $allowedposttags;
		$output = wp_kses( $input, $allowedposttags);
		return $output;
	}

	/**
	 * Filter the option value before getting it
	 *
	 * @hooked (via WBF\modules\options\Framework) 'wbf/theme_options/<name>/get_value'
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	public function get_value($value){
		return stripslashes($value);
	}
}