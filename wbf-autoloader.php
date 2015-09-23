<?php

require_once("vendor/autoload.php");

spl_autoload_register('wbf_autoloader');

/**
 * Waboot autoloader
 * @param $class
 * @since 0.1.4
 */
function wbf_autoloader($class)
{
    //Load Options Framework Classes
    if (preg_match("/^Options_Framework_/", $class)) {
        $filename = "class-" . strtolower(preg_replace("/_/", "-", $class)) . ".php";
        if ($class == "Options_Framework_Admin") {
	        require_once WBF_DIRECTORY.'/vendor/options-framework/' . $filename;
        } else {
            $filename = preg_replace("/-framework/", "", $filename);
	        require_once WBF_DIRECTORY.'/vendor/options-framework/' . $filename;
        }
    }

    /*if (preg_match("/^Waboot_Options_/", $class)) {
        $filename = "class-" . strtolower(preg_replace("/_/", "-", $class)) . ".php";
        locate_template('wbf/admin/' . $filename, true);
    }*/

    if (preg_match("/conditions/", $class)) {
        $childclass = explode('\\', $class);
        $name = end($childclass);
	    require_once WBF_DIRECTORY.'/admin/conditions/'.$name.'.php';
    }

    if (preg_match("/modules/", $class)) {
        $childclass = explode('\\', $class);
        $name = end($childclass);
        $module = $childclass[2];
        require_once WBF_DIRECTORY.'/modules/'.$module.'/'.$name.'.php';
    }

    switch ($class) {
	    case "WBF\includes\License_Interface":
		    require_once WBF_DIRECTORY.'/includes/license-interface.php';
		    break;
	    case "WBF\includes\License":
		    require_once WBF_DIRECTORY.'/includes/class-license.php';
		    break;
	    case "WBF\includes\License_Exception":
		    require_once WBF_DIRECTORY.'/includes/class-license-exception.php';
		    break;
        case "WBF\admin\License_Manager":
	        require_once WBF_DIRECTORY.'/admin/license-manager.php';
            break;
        case "WBF\admin\Notice_Manager":
	        require_once WBF_DIRECTORY.'/admin/notice-manager.php';
            break;
        case "WBF\includes\Plugin_Update_Checker":
	        require_once WBF_DIRECTORY.'/includes/plugin-update-checker.php';
            break;
	    case "WBF\includes\Theme_Update_Checker":
		    require_once WBF_DIRECTORY.'/includes/theme-update-checker.php';
		    break;
	    case 'WBF\includes\compiler\Styles_Compiler':
		    require_once WBF_DIRECTORY.'/includes/compiler/class-styles-compiler.php';
		    break;
	    case 'WBF\includes\compiler\Base_Compiler':
		    require_once WBF_DIRECTORY.'/includes/compiler/interface-base-compiler.php';
		    break;
        case 'WBF\includes\compiler\less\Less_Cache':
	        require_once WBF_DIRECTORY.'/includes/compiler/less/Less_Cache.php';
            break;
        case 'WBF\includes\compiler\less\Less_Compiler':
	        require_once WBF_DIRECTORY.'/includes/compiler/less/Less_Compiler.php';
            break;
        case "Less_Cache":
	        require_once WBF_DIRECTORY.'/includes/compiler/less/vendor/Lessphp/Cache.php';
            break;
        case "Less_Parser":
	        require_once WBF_DIRECTORY.'/includes/compiler/less/vendor/Lessphp/Less.php';
            break;
        case "lessc":
	        require_once WBF_DIRECTORY.'/includes/compiler/less/vendor/Lessphp/lessc.inc.php';
            break;
        case "Less_Version":
	        require_once WBF_DIRECTORY.'/includes/compiler/less/vendor/Lessphp/Version.php';
            break;
        case "BootstrapNavMenuWalker":
	        require_once WBF_DIRECTORY.'/vendor/BootstrapNavMenuWalker.php';
            break;
        case "WabootNavMenuWalker":
	        require_once WBF_DIRECTORY.'/public/menu-navwalker.php';
            break;
        case "ThemeUpdate":
        case "ThemeUpdateChecker":
	        require_once WBF_DIRECTORY.'/vendor/theme-updates/theme-update-checker.php';
            break;
        /*case "PluginUpdateChecker":
        case "PluginUpdate":
        case "PluginInfo":
        case "PluginUpdateChecker_1_6":
        case "PluginInfo_1_6":
        case "PluginUpdate_1_6":
        case "PucFactory":
            locate_template('wbf/vendor/plugin-updates/plugin-update-checker.php', true);
            break;*/
        /*case "Mobile_Detect":
            locate_template('wbf/vendor/Mobile_Detect.php', true);
            break;*/
        case "Options_Framework":
	        require_once WBF_DIRECTORY.'/vendor/options-framework/class-options-framework.php';
            break;
    }
}