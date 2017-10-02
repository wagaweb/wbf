<?php

namespace {{ namespace }};

class Admin{
	/**
	 * @var \{{ namespace }}\Plugin
	 */
	var $plugin;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param null|string $plugin_name
	 * @param null|string $version
	 * @param null|Plugin $core The plugin main object
	 */
	public function __construct( $plugin_name = null, $version = null, $core = null ) {
		if(isset($core)) $this->plugin = $core;
    }

    public function hello_admin(){
        var_dump("I'm the admin part of: ".$this->plugin->get_plugin_name()."!");
    }
}