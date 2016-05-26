<?php

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
		require_once( $component_params['file'] );
		$class_name = self::get_component_class_name($component_params['nicename']);
		if(!class_exists($class_name)){
			$class_name = $class_name."Component";
		}
		if(class_exists($class_name)){
			return new $class_name($component_params);
		}
		return false;
	}

	/**
	 * Returns the component class name
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
}