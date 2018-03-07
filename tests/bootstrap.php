<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Wbf
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL;
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/wbf.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

/*
 * CUSTOMIZATIONS: Begin
 */

define ('WBF_PLUGIN_DIR', _get_plugin_dir());
define ('WBF_PREVENT_STARTUP', true);

function _get_plugin_dir(){
	$_tests_dir = dirname(__FILE__);
	$_plugin_dir = dirname($_tests_dir);
	return $_plugin_dir;
}

function _load_wbf(){
	echo esc_html( 'Loading WBF...' . PHP_EOL );
	require_once WBF_PLUGIN_DIR.'/wbf.php';
}
tests_add_filter( 'muplugins_loaded', '_load_wbf' );

/*function _install_wbf(){
	echo esc_html( 'Installing WBF...' . PHP_EOL );
}
tests_add_filter( 'setup_theme', '_install_wbf' );*/

/*
 * CUSTOMIZATIONS: End
 */

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
