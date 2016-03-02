<?php

require_once("vendor/autoload.php");

spl_autoload_register('wbf_autoloader');

/**
 * Waboot autoloader
 * @param $class
 * @since 0.1.4
 */
function wbf_autoloader($class) {

	$prefix = "WBF\\";
	$has_prefix = function() use($class,$prefix){
		// does the class use the namespace prefix?
		$len = strlen($prefix);
		if (strncmp($prefix, $class, $len) !== 0) {
			// no...
			return false;
		}
		return true;
	};

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

    if (preg_match("/conditions/", $class) && $has_prefix()) {
        $childclass = explode('\\', $class);
        $name = end($childclass);
	    \WBF\includes\Utilities::locate_file('admin/conditions/'.$name.'.php', true);
    }

    if (preg_match("/modules/", $class) && $has_prefix()) {
        $childclass = explode('\\', $class);
        $name = end($childclass);
        $module = $childclass[2];
	    \WBF\includes\Utilities::locate_file('modules/'.$module.'/'.$name.'.php', true);
    }
	
    switch ($class) {
		case 'WBF\includes\Utilities':
			\WBF\includes\Utilities::locate_file('includes/class-utilities.php', true);
			break;
	    case 'WBF\includes\License_Interface':
			\WBF\includes\Utilities::locate_file('includes/license-interface.php', true);
		    break;
	    case 'WBF\includes\License':
			\WBF\includes\Utilities::locate_file('includes/class-license.php', true);
		    break;
	    case 'WBF\includes\License_Exception':
			\WBF\includes\Utilities::locate_file('includes/class-license-exception.php', true);
		    break;
        case 'WBF\admin\License_Manager':
			\WBF\includes\Utilities::locate_file('admin/license-manager.php', true);
            break;
        case 'WBF\admin\Notice_Manager':
	        \WBF\includes\Utilities::locate_file('admin/notice-manager.php', true);
            break;
        case 'WBF\includes\Plugin_Update_Checker':
	        \WBF\includes\Utilities::locate_file('includes/plugin-update-checker.php', true);
            break;
	    case 'WBF\includes\Theme_Update_Checker':
		    \WBF\includes\Utilities::locate_file('includes/theme-update-checker.php', true);
		    break;
	    case 'WBF\includes\compiler\Styles_Compiler':
		    \WBF\includes\Utilities::locate_file('includes/compiler/class-styles-compiler.php', true);
		    break;
	    case 'WBF\includes\compiler\Base_Compiler':
		    \WBF\includes\Utilities::locate_file('includes/compiler/interface-base-compiler.php', true);
		    break;
        case 'WBF\includes\compiler\less\Less_Cache':
	        \WBF\includes\Utilities::locate_file('includes/compiler/less/Less_Cache.php', true);
            break;
        case 'WBF\includes\compiler\less\Less_Compiler':
	        \WBF\includes\Utilities::locate_file('includes/compiler/less/Less_Compiler.php', true);
            break;
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
        case "Options_Framework":
	        \WBF\includes\Utilities::locate_file('vendor/options-framework/class-options-framework.php', true);
            break;
    }
}