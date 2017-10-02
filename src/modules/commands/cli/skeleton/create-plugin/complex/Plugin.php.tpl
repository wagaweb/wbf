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

        /*
         * If you followed the standard structure, you now will have access to both Frontend and Admin parts of your plugin:
         */
        $admin_part = $this->loader->admin_plugin;
        $frontend_part = $this->loader->public_plugin;

        /*
         * And can specify hooks like this:
         */
        $this->loader->add_action( 'admin_init', $admin_part, 'hello_admin');
        $this->loader->add_action( 'init', $frontend_part, 'hello_frontend');
    }

    public function hello_world(){
        var_dump("Hello World! I'm: ".$this->get_plugin_name());
    }
}