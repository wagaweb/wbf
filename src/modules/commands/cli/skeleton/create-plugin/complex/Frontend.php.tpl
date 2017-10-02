<?php

namespace {{ namespace }}\frontend;

class Frontend{
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

    public function hello_frontend(){
        var_dump("I'm the frontend part of: ".$this->plugin->get_plugin_name()."!");

        /*
        * You can easily use a class from another part of the plugin:
        */
        $Foo = $this->plugin->get_loader()->admin_plugin->Foo;

        var_dump($Foo->hello_foo()." -- Called from the frontend!");
    }
}