<?php
/*
 * This file is part of WBF Framework: https://github.com/wagaweb/wbf
 *
 * @author WAGA Team <dev@waga.it>
 */

namespace WBF\components\pluginsframework;

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 */
class Loader {

	const TYPE_ACTION = 'action';
	const TYPE_FILTER = 'filter';

	/**
	 * @var array $actions The actions registered with WordPress to fire when the plugin loads.
	 */
	protected $actions = [];

	/**
	 * @var array $filters The filters registered with WordPress to fire when the plugin loads.
	 */
	protected $filters = [];

	/**
	 * @var object
	 */
	public $public_plugin;

	/**
	 * @var object
	 */
	public $admin_plugin;

	/**
	 * @var array
	 */
	public $classes;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @param Plugin $caller
	 */
	public function __construct($caller = null) {
		if(isset($caller)){
			if($caller->get_public_class_name()){
				$class_name = $caller->get_public_class_name();
				$this->public_plugin = new $class_name($caller->get_plugin_name(), $caller->get_version(), $caller);
			}
			if($caller->get_admin_class_name()){
				$class_name = $caller->get_admin_class_name();
				$this->admin_plugin = new $class_name($caller->get_plugin_name(), $caller->get_version(), $caller);
			}
		}
	}

	/**
	 * A utility function that is used to register the actions and hooks into a single
	 * collection.
	 *
	 * @param string $type The collection of hooks that is being registered (that is, actions or filters).
	 * @param string $hook The name of the WordPress filter that is being registered.
	 * @param callable $callback The name of the function definition on the $component.
	 * @param int $priority The priority at which the function should be fired.
	 * @param int $accepted_args The number of arguments that should be passed to the $callback.
	 *
	 * @return Loader
	 */
	private function add( $type, $hook, $callback, $priority, $accepted_args ) {
		switch ($type){
			case self::TYPE_ACTION:
				$this->actions[] = [
					'hook'          => $hook,
					'callback'      => $callback,
					'priority'      => $priority,
					'accepted_args' => $accepted_args
				];
				break;
			case self::TYPE_FILTER:
				$this->filters[] = [
					'hook'          => $hook,
					'callback'      => $callback,
					'priority'      => $priority,
					'accepted_args' => $accepted_args
				];
				break;
		}
		return $this;
	}

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @var string $hook The name of the WordPress action that is being registered.
	 * @var object|callable $component A reference to the instance of the object on which the action is defined.
	 * @var string $callback The name of the function definition on the $component.
	 * @var int $priority The priority at which the function should be fired.
	 * @var int $accepted_args The number of arguments that should be passed to the $callback.
	 */
	public function add_action( $hook, $callable_or_component, $function_name = null, $priority = 10, $accepted_args = 1 ) {
		if(!is_callable($callable_or_component)){
			if(!isset($function_name)){
				_doing_it_wrong(__FUNCTION__,'You cannot call the function without a $function_name paramater',"1.0.9");
			}
			$callback = [$callable_or_component,$function_name];
		}else{
			$callback = $callable_or_component;
		}
		$this->add( self::TYPE_ACTION, $hook, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @var string $hook The name of the WordPress filter that is being registered.
	 * @var object|callable $callable_or_component A reference to the instance of the object on which the filter is defined.
	 * @var string Optional $function_name The name of the function definition on the $component.
	 * @var int $priority The priority at which the function should be fired.
	 * @var int $accepted_args The number of arguments that should be passed to the $callback.
	 */
	public function add_filter( $hook, $callable_or_component, $function_name = null, $priority = 10, $accepted_args = 1 ) {
		if(!is_callable($callable_or_component)){
			if(!isset($function_name)){
				_doing_it_wrong(__FUNCTION__,'You cannot call the function without a $function_name paramater',"1.0.9");
			}
			$callback = [$callable_or_component,$function_name];
		}else{
			$callback = $callable_or_component;
		}
		$this->add( self::TYPE_FILTER, $hook, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new ajax action
	 *
	 * @since 1.0.5
	 *
	 * @param string $action
	 * @param $callable_or_component
	 * @param null $function_name
	 * @param bool $public if TRUE wp_ajax_nopriv will be added as well
	 * @param int $priority
	 * @param int $accepted_args
	 */
	public function add_ajax_action($action, $callable_or_component, $function_name = null, $public = true, $priority = 10, $accepted_args = 1){
		if(!is_callable($callable_or_component)){
			if(!isset($function_name)){
				_doing_it_wrong(__FUNCTION__,'You cannot call the function without a $function_name paramater',"1.0.9");
			}
			$callback = [$callable_or_component,$function_name];
		}else{
			$callback = $callable_or_component;
		}
		$this->add( self::TYPE_ACTION, "wp_ajax_".$action, $callback, $priority, $accepted_args);
		if($public){
			$this->add( self::TYPE_ACTION, "wp_ajax_nopriv_".$action, $callback, $priority, $accepted_args);
		}
	}

	/**
	 * Append a new class (Not used at the moment)
	 *
	 * @param $class_obj
	 */
	public function add_class($class_obj){
		if(is_object($class_obj)){
			$this->classes[] = $class_obj;
		}
	}

	/**
	 * Register the filters and actions with WordPress.
	 */
	public function run() {
		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], $hook['callback'], $hook['priority'], $hook['accepted_args'] );
		}
		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], $hook['callback'], $hook['priority'], $hook['accepted_args'] );
		}
	}
}