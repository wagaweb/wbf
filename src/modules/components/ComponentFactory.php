<?php
/**
 * @package   Components Module
 * @author    Riccardo D'Angelo <riccardo@waga.it>, WAGA <dev@waga.it>
 * @license   GPL-2.0+
 * @link      http://www.waboot.com
 * @copyright WAGA.it
 */

namespace WBF\modules\components;

class ComponentFactory {
	/**
	 * Return an instance of Component
	 *
	 * @param array $component_params
	 *
	 * @throws \Exception
	 *
	 * @use self::get_component_class_name()
	 *
	 * @return Component|false
	 */
	public static function create($component_params){
		if(!isset($component_params['file']) || empty($component_params) || !is_array($component_params)){
			throw new \Exception("Invalid component params provided. Unable to instance a new component.");
		}
		if(!file_exists($component_params['file'])) return false;
		require_once( $component_params['file'] );
		$class_name = self::get_component_class_name($component_params['nicename']);
		if(!class_exists($class_name)){
			$class_name = $class_name."Component";
		}
		if(class_exists($class_name)){
			return new $class_name($component_params);
		}else{
			throw new \Exception( sprintf( __( "Component class (%s) not defined. Unable to activate the component.", "wbf" ), $component_params['nicename'] ) );
		}
	}

	/**
	 * Returns the component class name.
	 *
	 * Applies some transformations.
	 *
	 * - ucfirst of $component_name
	 * - ucfirst of any char after an underscore
	 *
	 * So:
	 *
	 * with component name: test -> Test
	 * with component name: test_me -> Test_Me
	 * with component name: testMe -> TestMe
	 * with component name: TestMe -> TestMe
	 *
	 * @param $component_name
	 *
	 * @return string
	 */
	public static function get_component_class_name($component_name){
		//return ucfirst( $component_name ) . "Component";
		$class_name = ucfirst( $component_name );
		$parts = implode('_', array_map("ucfirst", explode('_', $class_name)));
		return $parts;
	}

	/**
	 * Get the component metadata from the beginning of the file. Mimics the get_plugin_data() WP funtion.
	 *
	 * @param $component_file
	 *
	 * @return array
	 */
	public static function get_component_data( $component_file ) {
		$default_headers = array(
			'Name'         => 'Component Name',
			'Version'      => 'Version',
			'Description'  => 'Description',
			'Category'     => 'Category',
			'Tags'         => 'Tags',
			'Author'       => 'Author',
			'AuthorURI'    => 'Author URI',
			'ComponentURI' => 'Component URI',
		);

		$component_data = get_file_data( $component_file, $default_headers );

		if(isset($component_data['Tags']) && !empty($component_data['Tags'])){
			$component_data['Tags'] = str_replace(" ","",$component_data['Tags']);
			$component_data['Tags'] = explode(",",$component_data['Tags']);
		}

		return $component_data;
	}
	
	/**
	 * Get the possibile paths for a component named $c_name. The component does not have to exists.
	 *
	 * @param $c_name
	 *
	 * @return array
	 */
	public static function generate_component_mainfile_path( $c_name ) {
		$core_dir  = get_root_components_directory();
		$child_dir = get_child_components_directory();

		$c_name = strtolower( $c_name );

		return array(
			'core'  => $core_dir . $c_name . "/$c_name.php",
			'child' => $core_dir . $c_name . "/$c_name.php"
		);
    }
}