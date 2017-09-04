<?php

namespace {{ namespace }};
use WBF\components\pluginsframework\BasePlugin;

/**
 * The core plugin class.
 *
 * @package    WBSample
 * @subpackage WBSample/includes
 */
class Plugin extends BasePlugin {
	/**
	 * Define the core functionality of the plugin.
	 */
	public function __construct() {
		parent::__construct( "{{ slug }}", plugin_dir_path( dirname(  __FILE__  ) ) );

		/*
		 * Now you have a reference to this plugin instance in $GLOBALS['wbf_loaded_plugins']['waboot-sample']
		 * You can use this instance to make plugins talk to each other or to use plugins methods in templates.
		 */

		$this->loader->add_action( 'init', $this, 'hello_world' );

        /*
        * Every actions and filters added through $this->loader is stored in $this->loader->actions and $this->loader->filters.
        * They are hooked to WP once you call $this->run()
        */
    }

    public function hello_world(){
        var_dump("Hello World! I'm: ".$this->get_plugin_name());
    }
}