<?php

namespace {{ namespace }};

/**
 * The plugin bootstrap file
 *
 * Plugin Name:       {{ name }}
 * Plugin URI:        {{ url }}
 * Description:       {{ description }}
 * Version:           0.0.1
 * Author:            Foo
 * Author URI:        http://www.foo.bar/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       {{ slug }}
 * Domain Path:       /languages
 */

use {{ namespace }}\Plugin;

if ( ! defined( 'WPINC' ) ) {
	die; //If this file is called directly, abort.
}

// Custom PS4 autoloader
spl_autoload_register( function($class){
	$prefix = "{{ namespace }}\\";
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
	}else{
		return;
	}
});

require_once 'src/includes/wbf-utils.php';
includes\include_wbf_autoloader();

if(class_exists("\\WBF\\components\\pluginsframework\\BasePlugin")){
	require_once 'src/Plugin.php';
	$plugin = new Plugin();
	$plugin->run();
}else{
	if(is_admin()){
		add_action( 'admin_init' , function(){
			includes\install_wbf_wp_update_hooks();
		});
		add_action( 'admin_notices', function(){
			?>
            <div class="error">
                <p>
                    <?php echo includes\get_wbf_download_button('{{ name }}'); ?>
                </p>
            </div>
			<?php
		});
	}
}