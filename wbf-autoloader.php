<?php

require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/src/WBF.php'; //Backward compatibility

/**
 * PS4 Autoloader
 */
spl_autoload_register( function($class){
	$prefix = "WBF\\";
	$plugin_path = plugin_dir_path( __FILE__ );
	$base_dir = $plugin_path."src/";
	// does the class use the namespace prefix?
	$len = strlen($prefix);
	if (strncmp($prefix, $class, $len) !== 0) {
		// no, move to the next registered autoloader
		return;
	}
	// get the relative class name
	$relative_class = substr($class, $len);
	// replace the namespace prefix with the base directory, replace namespace
	// separators with directory separators in the relative class name, append
	// with .php
	$file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
	// if the file exists, require it
	if (file_exists($file)) {
		require_once $file;
	}
});