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
 *
 * @package    Waboot_Plugin
 */
class Loader {

	/**
	 * The array of actions registered with WordPress.
	 *
	 * @access   protected
	 * @var      array $actions The actions registered with WordPress to fire when the plugin loads.
	 */
	protected $actions;

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @access   protected
	 * @var      array $filters The filters registered with WordPress to fire when the plugin loads.
	 */
	protected $filters;

	public $public_plugin;

	public $admin_plugin;

	public $classes;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @param Plugin $caller
	 */
	public function __construct($caller = null) {

		$this->actions = array();
		$this->filters = array();

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
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @var      string $hook The name of the WordPress action that is being registered.
	 * @var      object $component A reference to the instance of the object on which the action is defined.
	 * @var      string $callback The name of the function definition on the $component.
	 * @var      int      Optional    $priority         The priority at which the function should be fired.
	 * @var      int      Optional    $accepted_args    The number of arguments that should be passed to the $callback.
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new ajax action
	 *
	 * @since 1.0.5
	 *
	 * @param string $action
	 * @param object $component
	 * @param string $callback
	 * @param bool $public if TRUE wp_ajax_nopriv will be added as well
	 * @param int $priority
	 * @param int $accepted_args
	 */
	public function add_ajax_action($action, $component, $callback, $public = true, $priority = 10, $accepted_args = 1){
		$this->actions = $this->add( $this->actions, "wp_ajax_".$action, $component, $callback, $priority, $accepted_args);
		if($public){
			$this->actions = $this->add( $this->actions, "wp_ajax_nopriv_".$action, $component, $callback, $priority, $accepted_args);
		}
	}

	/**
	 * A utility function that is used to register the actions and hooks into a single
	 * collection.
	 *
	 * @access   private
	 * @var      array $hooks The collection of hooks that is being registered (that is, actions or filters).
	 * @var      string $hook The name of the WordPress filter that is being registered.
	 * @var      object $component A reference to the instance of the object on which the filter is defined.
	 * @var      string $callback The name of the function definition on the $component.
	 * @var      int      Optional    $priority         The priority at which the function should be fired.
	 * @var      int      Optional    $accepted_args    The number of arguments that should be passed to the $callback.
	 * @return   type                                   The collection of actions and filters registered with WordPress.
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {

		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args
		);

		return $hooks;

	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @var      string $hook The name of the WordPress filter that is being registered.
	 * @var      object $component A reference to the instance of the object on which the filter is defined.
	 * @var      string $callback The name of the function definition on the $component.
	 * @var      int      Optional    $priority         The priority at which the function should be fired.
	 * @var      int      Optional    $accepted_args    The number of arguments that should be passed to the $callback.
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	public function add_class($class_obj){
		if(is_object($class_obj)){
			$this->classes[] = $class_obj;
		}
	}

	/**
	 * Register the filters and actions with WordPress.
	 *
	 */
	public function run() {

		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], array(
					$hook['component'],
					$hook['callback']
				), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], array(
					$hook['component'],
					$hook['callback']
				), $hook['priority'], $hook['accepted_args'] );
		}

	}
}