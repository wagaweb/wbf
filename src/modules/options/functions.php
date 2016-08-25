<?php
/**
 * @package   Options Module
 * @author    Riccardo D'Angelo <riccardo@waga.it>, WAGA <dev@waga.it>
 * @license   GPL-2.0+
 * @link      http://www.waboot.com
 * @copyright WAGA.it
 *
 * Based on Devin Price' Options_Framework
 */

namespace WBF\modules\options;
use WBF\components\utils\Utilities;
use WBF\modules\components\ComponentFactory;
use \WBF\modules\components\ComponentsManager;


/**
 * Checks if the dependencies of theme options are met
 */
function of_check_options_deps(){
    $wbf_notice_manager = Utilities::get_wbf_notice_manager();
    $deps_to_achieve = _of_get_theme_options_deps();
    if(!empty($deps_to_achieve)){
        if(!empty($deps_to_achieve['components'])){
            $wbf_notice_manager->clear_notices("theme_opt_component_deps_everyrun");
            foreach($deps_to_achieve['components'] as $c_name){
                if(!ComponentsManager::is_active($c_name)){
                    //Register new notice that tells that the component is not present
                    $message = __("An option requires the component <strong>$c_name</strong>, but it is not active.","wbf");
                    $wbf_notice_manager->add_notice($c_name."_not_active",$message,"error","theme_opt_component_deps_everyrun");
                }else{
                    $wbf_notice_manager->remove_notice($c_name."_not_active");
                }
            }
        }else{
            $wbf_notice_manager->clear_notices("theme_opt_component_deps_everyrun");
        }
    }else{
        $wbf_notice_manager->clear_notices("theme_opt_component_deps_everyrun");
    }
}

/**
 * Perform actions before Theme Options is saved and before che $value == $old_value check has been done
 *
 * @hooked 'pre_update_option'
 *
 * @param $value
 * @param $option
 * @param $old_value
 *
 * @return string|array
 */
function of_options_pre_save($value,$option,$old_value){
	//Checking if we are saving the correct option
	$config_id = Framework::get_options_root_id();
	if($option != $config_id) return $value;

	//Ok let's go!
	if(!Admin::is_options_page()) return $value;
	$value = apply_filters("wbf/modules/options/pre_save",$value,$option,$old_value);
	return $value;
}

/**
 * Performs actions before Theme Option has been saved (called during "update_option")
 *
 * @hooked 'update_option'
 *
 * @param $option
 * @param $old_value
 * @param $value
 *
 * @uses of_recompile_styles()
 * @throws \Exception
 */
function of_options_save($option, $old_value, $value){
	//Checking if we are saving the correct option
    $config_id = Framework::get_options_root_id();
    if($option != $config_id) return;

	//Ok let's go!
	$wbf_notice_manager = Utilities::get_wbf_notice_manager();

	$must_recompile_flag = false;
	$must_update_styles_flag = false;
	$deps_to_achieve = array();
	$all_options = Framework::get_registered_options();

	/*
	 * Check differences beetween new values and old value
	 */
	$multidimensional_options = array();
	foreach($all_options as $k => $opt){
		if(isset($opt['std']) && is_array($opt['std'])){
			$multidimensional_options[$opt['id']] = $opt;
		}
	}
	//$diff = @array_diff_assoc($old_value,$value);
	$diff = @array_diff_assoc($value,$old_value);
	foreach($multidimensional_options as $id => $opt){
		if(isset($old_value[$id]) && isset($value[$id])){
			$tdiff = @array_diff_assoc($old_value[$id],$value[$id]);
			if(is_array($tdiff) && !empty($tdiff)){
				$diff[$id] = $tdiff;
			}
		}
	}

	//Doing actions with modified options
	foreach($all_options as $k => $opt_data){
		if(isset($opt_data['id']) && array_key_exists($opt_data['id'],$diff)){ //True if the current option has been modified
			/** BEGIN OPERATIONS HERE: **/
			/*
			 * Check upload fields
			 */
			if($opt_data['type'] == "upload"){
				$upload_to = isset($opt_data['upload_to']) ? $opt_data['upload_to'] : false;
				$upload_as = isset($opt_data['upload_as']) ? $opt_data['upload_as'] : false;
				$allowed_extensions = isset($opt_data['allowed_extensions']) ? $opt_data['allowed_extensions'] : array("jpg","jpeg","png","gif","ico");
				$file_path = url_to_path($value[$opt_data['id']]);
				if(is_file($file_path)){ //by doing this we take into account only the files uploaded to the site and not external one
					$oFile = new \SplFileObject($file_path);
					try{
						if(!in_array($oFile->getExtension(),$allowed_extensions)) throw new \Exception("Invalid file extension");
						if($upload_to){
							//We need to copy the uploaded file and update the value
							if(is_dir($upload_to)){
								$upload_to = rtrim($upload_to,"/");
								$new_path = $upload_as && !empty($upload_as) ? $upload_to."/".$upload_as.".".$oFile->getExtension() : $upload_to."/".$oFile->getBasename();
								if(!copy($oFile->getRealPath(),$new_path)){
									throw new \Exception("Cant move file");
								}
								$new_opt_value = path_to_url($new_path);
								$value[$opt_data['id']] = $new_opt_value;
								Framework::set_option_value($opt_data['id'],$new_opt_value); //set new value
							}else{
								throw new \Exception("Invalid upload location");
							}
						}
					}catch(\Exception $e){
						//Reset the old value
						$old_opt_value = $old_value[$opt_data['id']];
						$value[$opt_data['id']] = $old_opt_value;
						Framework::set_option_value($opt_data['id'],$old_opt_value);
					}
				}
			}
			/*
			 * Check if must recompile
			 */
			if(isset($opt_data['recompile_styles']) && $opt_data['recompile_styles']){
				$must_recompile_flag = true;
			}
			/*
			 * Check if must update styles (aka: create\update a simple css file
			 */
			if(isset($opt_data['update_styles']) && $opt_data['update_styles']){
				$must_update_styles_flag = true; //Todo: implement this
			}
			/*
			 * Check if must perform some post actions
			 */
			if(isset($opt_data['save_action']) && is_string($opt_data['save_action']) && $opt_data['save_action'] != ""){
				$action = $opt_data['save_action'];
				//Todo: implement an action that deploy a simple css file with the options value. Maybe the developer could choose between: "update_styles" -> will create\update a css file, or "recompile_styles"
				if($action == "recompile_styles"){
					$must_recompile_flag = true;
				}elseif($action == "update_styles"){
					$must_update_styles_flag = true; //Todo: implement this
				}else{
					$on_save_callbacks[] = $action; //Build up a callback stack
				}
			}
			/*
			 * Check theme options dependencies
			 *
			 * Usage: options can specify dependencies at a global or value-specific level.
			 *
			 * - Global:
			 * $opt_data['deps']['_global']['components'] = ['foo']
			 * The "foo" component has to be active when this option has any value.
			 *
			 * - Value-specific:
			 * $opt_data['deps']['foo']['components'] = ['bar']
			 * The "bar" component has to be active when the option has the value of "foo"
			 *
			 */
			if(isset($opt_data['deps'])){
				if(isset($opt_data['deps']['_global'])){
					if(isset($opt_data['deps']['_global']['components'])){
						$deps_to_achieve['components'][] = $opt_data['deps']['_global']['components'];
					}
					unset($opt_data['deps']['_global']);
				}
				if(!empty($opt_data['deps'])){
					foreach($opt_data['deps'] as $v => $deps){
						if(!is_array($deps)) continue;
						if(array_key_exists($opt_data['id'],$value) && $value[$opt_data['id']] == $v){ //true the option has the value specified into deps array
							//Then set the deps to achieve
							foreach($deps as $type => $deps_names){
								if(!is_array($deps_names)) continue;
								if(array_key_exists($type,$deps_to_achieve)){
									$deps_to_achieve[$type] = array_merge($deps_to_achieve[$type],$deps_names);
								}else{
									$deps_to_achieve[$type] = $deps_names;
								}
							}
						}
					}
				}
			}
		}
	}

	/*
	 * If the "Reset to defaults" button was pressed
	 */
	if(isset($_POST['reset'])){
		$must_recompile_flag = true;
		$must_update_styles_flag = true;
	}

	/*
	 * Recompile styles if needed
	 */
	if($must_recompile_flag){
		of_recompile_styles($value);
	}

	/*
	 * Create\update a simple css file if needed
	 */
	if($must_update_styles_flag){
		//Todo: implement this
		//of_create_styles("css");
	}

	/*
	 * Call che callbacks if needed
	 */
	if(isset($on_save_callbacks) && !empty($on_save_callbacks)){
		$on_save_callbacks = array_unique($on_save_callbacks);
		foreach($on_save_callbacks as $cb){
			$cb = trim($cb);
			if(function_exists($cb)){
				call_user_func($cb,$option,$old_value,$value);
			}
		}
	}

	if(!empty($deps_to_achieve)){
		$wbf_notice_manager->clear_notices("theme_opt_component_deps");
		if(!empty($deps_to_achieve['components'])){
			if(\WBF::getInstance()->module_is_loaded("components")){
				//Try to enable all the required components
				$registered_components = ComponentsManager::getAllComponents();
				foreach($deps_to_achieve['components'] as $c_name){
					if(!ComponentsManager::is_active($c_name)){
						if(ComponentsManager::is_present($c_name)){
							ComponentsManager::enable($c_name, ComponentsManager::is_child_component( $c_name ));
						}else{
							//Register new notice that tells that the component is not present
							$message = __("An option requires the component <strong>$c_name</strong>, but it is not present","wbf");
							$wbf_notice_manager->add_notice($c_name."_component_not_present",$message,"error","theme_opt_component_deps","FileIsPresent", ComponentFactory::generate_component_mainfile_path( $c_name ) );
						}
					}
				}
			}else{
				$message = __("An option requires components module, but it is not loaded","wbf");
				$wbf_notice_manager->add_notice("components_not_loaded",$message,"error","_flash_");
			}
		}
	}else{
		$wbf_notice_manager->clear_notices("theme_opt_component_deps");
	}
}

/**
 * Generate a new _theme-options-generated.less and recompile the styles
 *
 * @param $values
 * @param bool|false $release release the compiler after? Default to "false". If "false" the compiler release the lock itself if necessary.
 * @uses of_create_styles
 */
function of_recompile_styles($values,$release = false){
	//Todo: what happens when no compiler is set?
	$result = of_create_styles("less",$values);
	if($result){
		//Then, compile less
		if(isset($GLOBALS['wbf_styles_compiler']) && $GLOBALS['wbf_styles_compiler']){
			global $wbf_styles_compiler;
			$wbf_styles_compiler->compile();
			if($release) $wbf_styles_compiler->release_lock();
		}
	}
}

/**
 * Generate a new style file for the theme options
 *
 * @param array|null $values
 * @param string $type
 *
 * @use of_generate_less_file
 *
 * @return bool|string
 */
function of_create_styles($type = "css", $values = null){
	$input_file_path = apply_filters("wbf/theme_options/styles/input_path",of_styles_get_default_input_path());
	$output_file_path = apply_filters("wbf/theme_options/styles/output_path",of_styles_get_default_output_path());
	switch($type){
		case "css":
			//Todo: implement the creation of a simple css file
			break;
		case "less":
			return of_generate_less_file($values,$input_file_path,$output_file_path); //Create a theme-options-generated.less file
			break;
		case "sass":
			//Todo: implement the creation of a sass file
			break;
	}
	return false;
}

/**
 * Return the default path for _theme-options-generated.less.cmp
 *
 * @return string
 */
function of_styles_get_default_input_path(){
	$input_file_path = rtrim(get_stylesheet_directory(),"/")."/"."_theme-options-generated.less.cmp";
	$input_file_path = apply_filters("wbf/modules/options/theme_options_input_file_location/main",$input_file_path);
	return $input_file_path;
}

/**
 * Return the default path for _theme-options-generated.less.cmp in parent theme
 *
 * @return string
 */
function of_styles_get_parent_default_input_path(){
	$input_file_path = rtrim(get_template_directory(),"/")."/"."_theme-options-generated.less.cmp";
	$input_file_path = apply_filters("wbf/modules/options/theme_options_input_file_location/child",$input_file_path);
	return $input_file_path;
}

/**
 * Return the default path for theme-options-generated.less
 *
 * @return string
 */
function of_styles_get_default_output_path(){
	if(is_multisite()){
		$blogname = wbf_get_sanitized_blogname();
		if(!isset($output_file_path) || empty($output_file_path)){
			$output_file_path = WBF_OPTIONS_FRAMEWORK_THEME_ASSETS_DIR."/mu/{$blogname}-theme-options-generated.less";
		}
	}else{
		if(!isset($output_file_path) || empty($output_file_path)){
			$output_file_path = WBF_OPTIONS_FRAMEWORK_THEME_ASSETS_DIR."/theme-options-generated.less";
		}
	}
	return $output_file_path;
}

/**
 * Parse {@import 'theme-options-generated.less'} into tmp_ style file.
 *
 * @hooked 'wbf/compiler/parser/line/import'
 *
 * @param $line
 * @param $inputFile
 * @param $filepath
 *
 * @return string
 */
function of_parse_generated_file($parsed_line,$line,$matches,$filepath,$inputFile){
	/*
	 * PARSE theme-options-generated.less
	 */
	if(isset($matches[1]) && $matches[1] == "theme-options-generated.less"){
		if(is_multisite()){
			$blogname = wbf_get_sanitized_blogname();
			$fileToImport = new \SplFileInfo(WBF_OPTIONS_FRAMEWORK_THEME_ASSETS_DIR."/mu/".$blogname."-".$matches[1]);
		}else{
			$fileToImport = new \SplFileInfo(WBF_OPTIONS_FRAMEWORK_THEME_ASSETS_DIR."/".$matches[1]);
		}
		if($fileToImport->isFile() && $fileToImport->isReadable()){
			if($inputFile->getPath() == $fileToImport->getPath()){
				$parsed_line = "@import '{$fileToImport->getBasename()}';\n";
			}else{
				$parsed_line = "@import '{$fileToImport->getRealPath()}';\n";
			}
		}
	}
	return $parsed_line;
}

/**
 * Replace {of_get_option} and {of_get_font} tags in _theme-options-generated.less.cmp;
 * It is called during "update_option" via of_options_save() and during "wbf/compiler/pre_compile" via hook
 *
 * @param array $value values of the options
 * @param null $input_file_path
 * @param null $output_file_path
 * @param string $output_type can be "FILE" or "STRING"
 *
 * @return bool|string
 */
function of_generate_less_file($value = null,$input_file_path = null,$output_file_path = null,$output_type = "FILE"){
	if(!isset($value) || empty($value)) $value = Framework::get_options_values();

	if(!isset($input_file_path) || empty($input_file_path)){
		$input_file_path = of_styles_get_default_input_path();
	}
	if(!isset($output_file_path) || empty($output_file_path)){
		$output_file_path = of_styles_get_default_output_path();
	}

	if(!is_array($value)) return false;

	$output_string = "";

    $tmpFile = new \SplFileInfo($input_file_path);
    if( (!$tmpFile->isFile() || !$tmpFile->isWritable()) && is_child_theme() ){
	    $input_file_path = of_styles_get_parent_default_input_path(); //Search in parent
        $tmpFile = new \SplFileInfo($input_file_path);
    }
	$parsedFile = $output_file_path ? new \SplFileInfo($output_file_path) : null;
	if(!is_dir($parsedFile->getPath())){
		mkdir($parsedFile->getPath());
	}

    if($tmpFile->isFile() && $tmpFile->isWritable()) {
        $genericOptionfindRegExp = "~//{of_get_option\('([a-zA-Z0-9\-_]+)'\)}~";
        $fontOptionfindRegExp    = "~//{of_get_font\('([a-zA-Z0-9\-_]+)'\)}~";

        $tmpFileObj    = $tmpFile->openFile( "r" );
        $parsedFileObj = $output_type == "FILE" ? $parsedFile->openFile( "w" ) : null;
        $byte_written = $output_type == "FILE" ? 0 : null;

        while ( ! $tmpFileObj->eof() ) {
            $line = $tmpFileObj->fgets();
            //Replace a generic of option
            if ( preg_match( $genericOptionfindRegExp, $line, $matches ) ) {
                if ( array_key_exists( $matches[1], $value ) ) {
                    if ( $value[ $matches[1] ] != "" ) {
                        $line = preg_replace( $genericOptionfindRegExp, $value[ $matches[1] ], $line );
                    } else {
                        $line = "//{$matches[1]} is empty\n";
                    }
                } else {
                    $line = "//{$matches[1]} not found\n";
                }
            }
            //Replace a font option
            if ( preg_match( $fontOptionfindRegExp, $line, $matches ) ) {
                $line = "//{$matches[1]} is empty\n";
                if ( array_key_exists( $matches[1], $value ) ) {
                    if ( $value[ $matches[1] ] != "" ) {
                        $attr       = $value[ $matches[1] ];
	                    if(isset($attr['category']))
                            $fontString = "font-family: '" . $attr['family'] . "', " . $attr['category'] . ";";
	                    else
		                    $fontString = "font-family: '" . $attr['family'] . "';";
                        /*if(preg_match("/([0-9]+)([a-z]+)/",$attr['style'],$style_matches)){
                            if($style_matches[1] == 'regular') $style_matches[1] = "normal";
                            $fontString .= "font-weight: ".$style_matches[1].";";
                            $fontString .= "font-style: ".$style_matches[2].";";
                        }else{
                            if($attr['style'] == 'regular') $attr['style'] = "normal";
                            $fontString .= "font-weight: ".$attr['style'].";";
                        }*/
                        $fontString .= "color: " . $attr['color'] . ";";
                        $line = $fontString;
                    } else {
                        $line = "//{$matches[1]} is empty\n";
                    }
                } else {
                    $line = "//{$matches[1]} not found\n";
                }
            }
	        if($output_type == "FILE"){
	            $byte_written += $parsedFileObj->fwrite( $line );
	        }else{
		        $output_string .= $line."\n";
	        }
        }
	    //Here the file has been written!
	    if($output_type != "FILE"){
		    return $output_string;
	    }
	    return true;
    }
	return false;
}

/**
 * Returns an array with the dependencies of theme options
 * @param null $all_options
 * @return array
 */
function _of_get_theme_options_deps($all_options = null){
	//todo: a partire da quì, forse si genera qlc errore durante l'attivazione del plugin qnd non c'è nessun tema che lo supporta
    $deps_to_achieve = array();
    if(!isset($all_options)) $all_options = Framework::get_registered_options();
	if(is_array($all_options) && !empty($all_options)){
	    foreach($all_options as $k => $opt_data){
	        if(isset($opt_data['id'])){
	            $current_opt_name = $opt_data['id'];
	            $current_value = of_get_option($current_opt_name);
	            if(isset($opt_data['deps'])){
	                if(isset($opt_data['deps']['_global'])){
	                    if(isset($opt_data['deps']['_global']['components']))
	                        $deps_to_achieve['components'][] = $opt_data['deps']['_global']['components'];
	                }
	                unset($opt_data['deps']['_global']);
	                foreach($opt_data['deps'] as $v => $deps){
	                    if($current_value == $v){ //true the option has the value specified into deps array
	                        //Then set the deps to achieve
	                        if(isset($deps['components'])) $deps_to_achieve['components'] = $deps['components'];
	                    }
	                }
	            }
	        }
	    }
	}
    return $deps_to_achieve;
}

/**
 * Check if current admin page is the options framework page
 * @param $hook
 * @return bool
 */
function of_is_admin_framework_page($hook){
	return Admin::is_options_page();
}

/**
 * Takes an array of options and returns the values themselves and the default value
 * @usage
 *
 * A typical array should be like this:
 *
 * array(
 *       array(
 *           "name" => __("Full width. No sidebar.","waboot"),
 *           "value" => "full-width"
 *       ),
 *       array(
 *           "name" => __("Sidebar right","waboot"),
 *           "value" => "sidebar-right"
 *       ),
 *       array(
 *           "name" => __("Sidebar left","waboot"),
 *           "value" => "sidebar-left"
 *       ),
 *       '_default' => 'sidebar-right'
 * )
 *
 * OR (more general):
 *
 * array(
 *       'opt1'
 *       'opt2,
 *       'opt2,
 *       '_default' => 'opt1'
 * )
 *
 * IF '_default' is not set or does not exists in the array, the function returns the first value (ore the 'value' field of the first key)
 *
 * @param $values
 * @return array
 */
function of_add_default_key($values){
    $default = false;

    if(isset($values['_default'])){
        if(array_key_exists($values['_default'],$values)){
            $default = $values['_default'];
        }else{
            foreach($values as $v){
                if(is_array($v)){
                    if($v['value'] == $values['_default']){
                        $default = $values['_default'];
                    }
                }
            }
        }
    }
    if(!isset($values['_default']) || $default == false){
        reset($values);
        $default = key($values);
        if(is_array($values[$default])){
            $default = $values[$default]['value'];
        }
    }
    if(isset($values['_default'])) unset($values['_default']);

    return array(
      'values' => $values,
      'default' => $default
    );
}

/*
 * IMPORT \ EXPORT FUNCTIONS (not used - we use the i\e functions into \WBF\modules\options\Admin)
 */

/**
 * Replace the $old_prefix with $new_prefix in Theme Options id
 * @param $old_prefix
 * @param $new_prefix
 * @since 0.1.0
 */
function prefix_theme_options($old_prefix, $new_prefix) {
    $options_field = Framework::get_options_root_id();

    if (!$options_field || empty($options_field)) return;

    $options = get_option($options_field);
    $new_options = array();

    if (!empty($options) && $options != false) {
        foreach ($options as $k => $v) {
            $new_k = preg_replace("|^" . $old_prefix . "_|", $new_prefix . "_", $k);
            $new_options[$new_k] = $v;
        }
    } else {
        return;
    }

    update_option($options_field, $new_options);
}

/**
 * Transfer theme options from a theme to another
 * @param string $from_theme theme the name of the theme from which export
 * @param (optional) null string $to_theme the name of the theme into which import (current theme if null)
 * @totest
 * @since 0.1.0
 */
function transfer_theme_options($from_theme, $to_theme = null) {
    $from_theme_options = get_option($from_theme);
    if (!isset($to_theme))
        import_theme_options($from_theme_options);
    else
        update_option($to_theme, $from_theme_options);
}

/**
 * Copy a theme options array into current theme options option. Old theme options will be replaced.
 * @param array $exported_options
 * @totest
 * @since 0.1.0
 */
function import_theme_options($exported_options) {
    $options_field = Framework::get_options_root_id();
    update_option($options_field, $exported_options);
}

/**
 * Get an instance of Organizer
 *
 * @return Organizer
 */
function organizer(){
	return Organizer::getInstance();
}