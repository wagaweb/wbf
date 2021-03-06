<?php

/**
 * Backward compatibility for trail
 * @param array $args
 */

if(!function_exists("waboot_breadcrumb_trail")):
	/**
	 *
	 *
	 * @param array $args
	 */
	function waboot_breadcrumb_trail( $args = array() ){
		\WBF\components\breadcrumb\trail($args);
	}
endif;

if(!function_exists("get_behavior")):
	/**
	 * Behaviors framework backup functions; handles the case in which the Behaviors are not loaded
	 *
	 * @param $name
	 * @param int $post_id
	 * @param string $return
	 *
	 * @return array|bool|mixed|string
	 */
	function get_behavior( $name, $post_id = 0, $return = "value" ) {
		if (class_exists('\WBF\modules\behaviors\BehaviorsManager')) {
			return \WBF\modules\behaviors\get_behavior( $name, $post_id, $return = "value" ); //call the behavior framework function
		} else {
			return WBF()->get_behavior( $name, $post_id, $return = "value" ); //call the backup function
		}
	}
endif;

if(!function_exists("of_get_option")):
	/**
	 * \WBF\modules\options\of_get_option wrapper function
	 * @param $name
	 * @param bool $default
	 * @return \WBF\modules\options\of_get_option output
	 */
	function of_get_option($name, $default = false){
		return \WBF\modules\options\of_get_option($name,$default);
	}
endif;

if(!function_exists("component_is_loaded")):
	/**
	 *
	 * @param $name
	 * @return bool
	 */
	function component_is_loaded($name){
		if(class_exists('\WBF\modules\components\ComponentsManager')) {
			return \WBF\modules\components\ComponentsManager::is_loaded_by_name($name);
		}
		return false;
	}
endif;