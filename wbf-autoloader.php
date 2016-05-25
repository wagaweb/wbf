<?php

require_once("vendor/autoload.php");

// FUTURE PSR4 Custom plugin autoloader function
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

spl_autoload_register('wbf_autoloader');

/**
 * Waboot autoloader
 * @param $class
 * @since 0.1.4
 */
function wbf_autoloader($class) {
	
    //Load Options Framework Classes
    if (preg_match("/^Options_Framework_/", $class)) {
        $filename = "class-" . strtolower(preg_replace("/_/", "-", $class)) . ".php";
        if ($class == "Options_Framework_Admin") {
	        \WBF\includes\Utilities::locate_file('vendor/options-framework/' . $filename, true);
        } else {
            $filename = preg_replace("/-framework/", "", $filename);
	        \WBF\includes\Utilities::locate_file('vendor/options-framework/' . $filename, true);
        }
    }
	
    switch ($class) {
	    case 'Mobile_Detect':
		    \WBF\includes\Utilities::locate_file('vendor/mobiledetect/mobiledetectlib/Mobile_Detect.php', true);
		    break;
        case "Less_Cache":
	        \WBF\includes\Utilities::locate_file('includes/compiler/less/vendor/Lessphp/Cache.php', true);
            break;
        case "Less_Parser":
	        \WBF\includes\Utilities::locate_file('includes/compiler/less/vendor/Lessphp/Less.php', true);
            break;
        case "lessc":
	        \WBF\includes\Utilities::locate_file('includes/compiler/less/vendor/Lessphp/lessc.inc.php', true);
            break;
        case "Less_Version":
	        \WBF\includes\Utilities::locate_file('includes/compiler/less/vendor/Lessphp/Version.php', true);
            break;
        case "BootstrapNavMenuWalker":
	        \WBF\includes\Utilities::locate_file('vendor/BootstrapNavMenuWalker.php', true);
            break;
        case "WabootNavMenuWalker":
	        \WBF\includes\Utilities::locate_file('public/menu-navwalker.php', true);
            break;
        case "ThemeUpdate":
        case "ThemeUpdateChecker":
	        \WBF\includes\Utilities::locate_file('vendor/theme-updates/theme-update-checker.php', true);
            break;
		case "PluginUpdateChecker":
		case "PluginUpdate":
		case "PluginInfo":
		case "PluginUpdateChecker_1_6":
		case "PluginInfo_1_6":
		case "PluginUpdate_1_6":
		case "PucFactory":
			\WBF\includes\Utilities::locate_file('vendor/yahnis-elsts/plugin-update-checker/plugin-update-checker.php', true);
			break;
        case "Options_Framework":
	        \WBF\includes\Utilities::locate_file('vendor/options-framework/class-options-framework.php', true);
            break;
    }
}