<?php

namespace {{ namespace }};
use WBF\components\pluginsframework\BasePlugin;
use WBF\components\pluginsframework\ModuleLoader;
use WBF\components\utils\Utilities;

/**
 * The core plugin class.
 *
 * @package    {{ namespace }}
 * @subpackage {{ namespace }}/includes
 */
class Plugin extends BasePlugin {
	/**
	 * @var ModuleLoader
	 */
	protected $loader;

	/**
	 * Define the core functionality of the plugin.
	 */
	public function __construct() {
		parent::__construct( "{{ slug }}", plugin_dir_path( dirname(  __FILE__  ) ) );

		/*
		 * Now you have a reference to this plugin instance in $GLOBALS['wbf_loaded_plugins']['waboot-sample']
		 * You can use this instance to make plugins talk to each other or to use plugins methods in templates.
		 */

		$this->get_loader()->add_action( 'init', $this, 'hello_world' );

		/*
		 * Every actions and filters added through $this->loader is stored in $this->loader->actions and $this->loader->filters.
		 * They are hooked to WP once you call $this->run()
		 */

		/*
		 * Now we can load modules
		 */

		$this->get_loader()->register_module('sample');

		/*
		 * The constructor is endend. Next, as stated in the plugin main file, the method run() will be executed.
		 * This method will call the run() method on Loader.
		 * The Loader run() method will call load_modules(), which in turn register all their hooks by calling their own run() method.
		 */
	}

	public function hello_world(){
		var_dump("Hello World! I'm: ".$this->get_plugin_name());
	}

	/**
	 * Overrides parent method to inject the new Loader.
	 * Loads plugin dependencies, called during parent::__construct()
	 */
	public function load_dependencies() {
		//Load Notice Manager if needed
		$wbf_notice_manager = Utilities::get_wbf_notice_manager();
		$this->notice_manager = &$wbf_notice_manager;

		$this->loader = new ModuleLoader($this,__NAMESPACE__);
	}

	/**
	 * Overrides the parent method to get the new Loader.
	 * @return ModuleLoader
	 */
	public function get_loader() {
		return $this->loader;
	}
}