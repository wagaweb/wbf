<?php
namespace WBF\modules\components;

use WBF\components\notices\Notice_Manager;
use WBF\components\mvc\HTMLView;
use WBF\components\utils\Utilities;
use WBF\modules\options\Admin;
use WBF\modules\options\Framework;
use WBF\modules\options\Organizer;
use WBF\modules\components\GUI;

class ComponentsManager {

    static $last_error = "";

	const STATE_ENABLED = "enabled";

	const STATE_DISABLED = "disabled";

    /**
     * Add hooks, detect components into components directory and updates relative options
     * 
     * @hooked 'wbf_after_setup_theme'
     */
    static function init(){
    	do_action("wbf/modules/components/before_init");

	    add_action("wbf/theme_options/register",'\WBF\modules\components\ComponentsManager::registeredActiveComponentOptions',999); //register component options
	    add_filter("wbf/modules/options/pre_save",'\WBF\modules\components\ComponentsManager::on_theme_options_saving',10,3);
	    //add_filter("wbf/modules/options/after_restore",'\WBF\modules\components\ComponentsManager::on_theme_options_restore',10,1); //@deprecated
	    add_filter("wbf/modules/options/after_reset",'\WBF\modules\components\ComponentsManager::on_theme_options_reset',10,1);

	    self::prune_components();
	    self::detect_components();

	    do_action("wbf/modules/components/after_init");
    }

	/**
	 * Prune invalid components and update the registered component WP option. Called by self::init()
	 */
	static function prune_components(){
		/*
		 * @from 1.1.4: do this action only in Components page to avoid unwanted components deactivation on deploy procedures.
		 */
		if(!is_admin()) return;
		if(!isset($_GET['page']) || $_GET['page'] != GUI::$wp_menu_slug) return;

		$prune_components = function($components,$directory){
			foreach ($components as $name => $data){
				//If the file does not exists or if the file is not in the expected directory, unset and remove the component
				if( !is_file($data['file']) || !preg_match('|'.$directory.'|', $data['file']) ){
					unset( $components[$name] );
					if(ComponentsManager::is_active($name)){
						ComponentsManager::remove($name);
					}
				}
			}
			return $components;
		};

		//Prune from parent
		$registered_components = $prune_components(self::get_registered_components(),get_root_components_directory());
		self::update_registered_components( $registered_components, false );

		//Prune from child
		if(is_child_theme()){
			$registered_components = $prune_components(self::get_registered_components(true),get_child_components_directory());
			self::update_registered_components( $registered_components, true );
		}

		//Prune states
		$states = self::get_components_state();
		foreach ($states as $component_name => $component_state){
			if(!self::is_present($component_name)){
				unset($states[$component_name]);
			}
		}
		ComponentsManager::update_components_state($states);
	}

	/**
	 * Detect the components in the their directory and update the registered component WP option. Called by self::init()
	 *
	 * @use self::detect_components_from_directories
	 *
	 * @param bool $force
	 *
	 * @return mixed|void
	 * @throws \Exception
	 */
    static function detect_components($force = false){
    	static $already_detected;
    	if($already_detected === true && $force === false) return;
	    /** Detect components in main theme **/
	    $parent_components = self::detect_components_from_directory(get_root_components_directory());
	    self::update_registered_components( $parent_components, false ); //update the WP Option of registered component
	    /** Detect components in child theme **/
	    if(is_child_theme()){
		    $child_components = self::detect_components_from_directory(get_child_components_directory(),true);
		    self::update_registered_components( $child_components, true ); //update the WP Option of registered component
	    }
	    self::update_global_components_vars(); //Update registered_components global
	    $already_detected = true;
    }

	/**
	 * Detect the components in the specified directories and update the registered component WP option.
	 *
	 * @param $components_directory
	 * @param bool $child_theme_context are we trying to detect the components from a child theme?
	 *
	 * @use self::get_registered_components
	 *
	 * @return array
	 * @throws \Exception
	 */
    static function detect_components_from_directory( $components_directory, $child_theme_context = false ) {
	    $registered_components = self::get_registered_components($child_theme_context);

	    if(!is_dir($components_directory)){
	    	throw new \Exception('Invalid components directory: '.$components_directory);
	    }

	    $components_files = listFolderFiles( $components_directory );
	    foreach ( $components_files as $file ) {
		    //$component_data = get_plugin_data($file);
		    $component_data = ComponentFactory::get_component_data( $file );
		    if ( $component_data['Name'] != "" ) {
			    //The component is valid, now checks if is already in registered list
			    $component_name = basename( dirname( $file ) );
			    if ( $component_name == "components" ) { //this means that the component file is in root directory
				    $pinfo          = pathinfo( $file );
				    $component_name = $pinfo['filename'];
			    }
			    //Buildup component data:
			    $component_params = [
				    'nicename' => $component_name,
				    'class_name' => isset($component_data['Class Name']) && $component_data['Class Name'] != "" ? $component_data['Class Name'] : ComponentFactory::get_component_class_name($component_name),
				    'file' => $file,
				    'metadata' => [
				        'tags' => $component_data['Tags'],
				        'category' => $component_data['Category'],
					    'version' => $component_data['Version']
				    ],
				    'child_component' => $child_theme_context,
				    //'enabled' => array_key_exists( $component_name, $registered_components ) ? $registered_components[ $component_name ][ 'enabled' ] : false
			    ];
			    //if($component_params['enabled'] === null) $component_params['enabled'] = false;
			    if(isset($component_params['enabled'])) unset($component_params['enabled']);
			    if ( ! array_key_exists( $component_name, $registered_components ) || $registered_components[ $component_name ] != $component_data ) {
				    $registered_components[ $component_name ] = $component_params;
			    }
		    }
	    }

        return $registered_components;
    }

	/**
	 * Get the value of "{$template_name}_registered_components" option (default to empty array)
	 *
	 * @param bool|FALSE $get_child_components
	 *
	 * @return array
	 */
	static function get_registered_components($get_child_components = false){
		$theme = wp_get_theme();
		if(!$theme->errors()){
			if($get_child_components){
				$rc = get_option( $theme->get_stylesheet()."_registered_components", array());
			}else{
				$rc = get_option( $theme->get_template()."_registered_components", array());
			}
		}else{
			$rc = [];
		}
		return $rc;
	}

	/**
	 * Update the "{$template_name}_registered_components" option, where $template_name is the current active template.
	 *
	 * @param array $registered_components
	 * @param bool $update_child_theme
	 */
	static function update_registered_components( $registered_components, $update_child_theme){
		$theme = wp_get_theme();
		if(!$theme->errors()){
			if($update_child_theme){
				update_option( $theme->get_stylesheet()."_registered_components", $registered_components );
			}else{
				update_option( $theme->get_template()."_registered_components", $registered_components );
			}
		}
	}

	/**
	 * Updates the component states (enabled or disabled) option for current theme
	 *
	 * @param array $states
	 */
	static function update_components_state($states){
		$opt = get_option("wbf_".get_stylesheet()."_components_state", []);
		update_option("wbf_".get_stylesheet()."_components_state", $states);
	}

	/**
	 * Get the current component state (enabled or disabled) option for the current theme
	 *
	 * @param string|\WP_Theme|null $theme
	 *
	 * @return array
	 * @throws \Exception
	 */
	static function get_components_state($theme = null){
		if(!isset($theme)){
			$theme_name = get_stylesheet();
		}else{
			if(\is_string($theme)){
				$theme = wp_get_theme($theme);
			}
			if($theme instanceof \WP_Theme){
				$theme_name = $theme->get_stylesheet();
			}
		}
		if(!isset($theme_name)){
			throw new \Exception('Unable to get components state');
		}
		$opt = get_option("wbf_".$theme_name."_components_state", []);
		$opt = apply_filters("wbf/modules/components/states",$opt,$theme_name);
		return $opt;
	}

	/**
	 * Updates global $registered_components
	 *
	 * @use self::getAllDetectedComponents()
	 * @use self::instance_component()
	 *
	 * @param bool|false $registered_components
	 *
	 * @return array
	 * @throws \Exception
	 */
	static function update_global_components_vars($registered_components = false){
		if(!$registered_components){
			global $registered_components;
		}
		$components = self::getAllDetectedComponents();
		foreach($components as $c){
			try{
				$oComponent = ComponentFactory::create($c);
				if($oComponent instanceof Component){
					$registered_components[$c['nicename']] = $oComponent;
				}
			}catch(\Exception $e){
				if(function_exists("WBF")){
					WBF()->services()->get_notice_manager()->add_notice($c['nicename']."_error",$e->getMessage(),"error","_flash_");
				}
			}
		}
		return $registered_components;
	}

	/**
	 * Gets currently registered components
	 *
	 * @return array of Component instances
	 */
	static function getAllComponents() {
		global $registered_components;
		if ( ! empty( $registered_components ) ) {
			return $registered_components;
		} else {
			$components = self::update_global_components_vars();
			return $components;
		}
	}

	/**
	 * Get all active components
	 * @return array
	 */
	static function getActiveComponents(){
		$components = self::getAllComponents();
		$activeComponents = array_filter($components,function($component){
			return self::is_active($component);
		});
		if(!\is_array($activeComponents)){
			return [];
		}
		return $activeComponents;
	}

	/**
	 * Retrieve current detected components (an array of components data)
	 *
	 * @uses self::get_registered_components()
	 *
	 * @return array
	 */
	static function getAllDetectedComponents(){
		$core_components  = self::get_registered_components();
		if ( is_child_theme() ) {
			$child_components = self::get_registered_components(true);
			foreach ( $core_components as $name => $comp ) {
				if ( array_key_exists( $name, $child_components ) ) {
					$child_components[ $name ]['override'] = true;
					//unset($child_components[$name]); //todo: per ora, non permettere la sovrascrizione
				}
			}
			$components = array_merge( $core_components, $child_components ); //this will override core_components with child_components with same name
		} else {
			/*foreach($core_components as $name => $comp){
				if(in_array($name,$child_components)){
					unset($child_components[$name]);
				}
			}*/
			$components = $core_components;
		}

		return $components;
	}

	/**
	 * Returns the currently loaded components
	 *
	 * @return array
	 */
	static function getLoadedComponents(){
		global $loaded_components;
		return $loaded_components;
	}

	/**
	 * Return a component from the loaded ones
	 *
	 * @param $nicename
	 *
	 * @return bool|Component
	 */
	static function getLoadedComponent($nicename){
		$loaded_components = self::getLoadedComponents();
		if(array_key_exists($nicename,$loaded_components)){
			return $loaded_components[$nicename];
		}else{
			return false;
		}
	}

	/**
	 * Inject a new component into global $loaded_components
	 *
	 * @param Component $c
	 */
	static function addLoadedComponent( Component $c ) {
		global $loaded_components;
		if ( ! in_array( $c->name, $loaded_components ) ) {
			$loaded_components[ $c->name] = $c;
		}
	}

	/**
     * Exec detectFilters() method on active components.
     */
    static function setupComponentsFilters(){
        $components = self::getAllComponents();
        foreach ( $components as $oComponent ) {
            if ( self::is_active( $oComponent ) ) {
                if(method_exists($oComponent,"detectFilters")){
                    $oComponent->detectFilters();
                }
            }
        }
    }

    /**
     * Exec the setup() method on active components (called during "init" action)
     */
    static function setupRegisteredComponents() {
        $components = self::getAllComponents();
        foreach ( $components as $oComponent ) {
            if ( self::is_active( $oComponent ) ) {
                if(method_exists($oComponent,"setup")) {
	                $oComponent->setup();
                }
            }
        }
    }

    /**
     * Exec widgets() method on active components (during widgets_init action)
     */
    static function registerComponentsWidgets(){
        $components = self::getAllComponents();
        foreach ( $components as $oComponent ) {
            if ( self::is_active( $oComponent ) ) {
                if(method_exists($oComponent,"widgets")){
                    $oComponent->widgets();
                }
            }
        }
    }

    /**
     * Exec onInit()\run(), scripts(), styles() methods on active components; ONLY into the pages that support them.
     * See components-hooks.php.
     */
    static function enqueueRegisteredComponent( $action ) {
        $components = self::getActiveComponents();
        foreach ( $components as $oComponent ) {
            if ( self::is_enable_for_current_page( $oComponent ) ) {
                self::addLoadedComponent( $oComponent );
                switch ( $action ) {
                    case "wp":
                        if(method_exists($oComponent,"run")){
                            $oComponent->run();
                        }else{
                            if(method_exists($oComponent,"onInit")){
                                $oComponent->onInit();
                            }
                        }
                        break;
                    case "wp_enqueue_scripts":
                        if(method_exists($oComponent,"scripts"))
                            $oComponent->scripts();
                        if(method_exists($oComponent,"styles"))
                            $oComponent->styles();
                        break;
                }
            }
        }
    }

    /**
     * Exec register_options method on active components (executed during "wbf/theme_options/register" action)
     * @todo: BUG: When all components being deactivated, theme options checkboxes are reset
     */
    static function registeredActiveComponentOptions(){
	    $activeComponents = self::getActiveComponents();
        if(count($activeComponents) > 0){
	        foreach ( $activeComponents as $oComponent ) {
		        add_filter( "wbf/modules/components/component/{$oComponent->name}_component/register_custom_options", [ $oComponent, "theme_options" ] );
		        $oComponent->register_options();
	        }
        }
    }

	/**
	 * Performs actions before theme options are saved.
	 *
	 * @hooked 'wbf/modules/options/pre_save'
	 *
	 * @param $value
	 * @param $option
	 * @param $old_value
	 *
	 * @use self::override_theme_options()
	 *
	 * @return string|array
	 */
	static function on_theme_options_saving($value,$option,$old_value){
		$value = self::override_theme_options($value);
		return $value;
	}

	/**
	 * Restore components options after a theme options reset.
	 *
	 * @param $values
	 *
	 * @hooked 'wbf/modules/options/after_reset'
	 *
	 * @return array
	 */
	static function on_theme_options_reset($values){
		$theme = wp_get_theme();
		$component_options = get_option("wbf_".$theme->get_stylesheet()."_components_options",true);
		if(empty($component_options)) return $values;

		$values = $component_options;
		return $values;
	}

	/**
	 * Restore components options after a theme options restore to defaults. This might not be necessary: "on_theme_options_saving" already do that.
	 *
	 * @param $values
	 *
	 * @hooked 'wbf/modules/options/after_restore'
	 *
	 * @use self::override_theme_options()
	 *
	 * @return array
	 */
	static function on_theme_options_restore($values){
		$values = self::override_theme_options($values);
		return $values;
	}

	/**
	 * Override $theme_options values with values stored in components auxiliary array
	 */
	private static function override_theme_options($theme_options){
		$theme = wp_get_theme();
		$component_options = get_option("wbf_".$theme->get_stylesheet()."_components_options",[]);
		if(empty($component_options)) return $theme_options;

		//When theme options are saved, $value contains some wrong values for components options. We need to use the auxiliary array to restore those values:
		foreach($component_options as $k => $v){
			if(!self::is_component_option($k)) continue;
			if(!isset($theme_options[$k]) || (isset($theme_options[$k]) && $theme_options[$k] != $v)){
				if($v == "on") $v = "1"; //the checkboxes are saved as "1" or FALSE, but here we can have "on" as value. This is a legacy issue with vendor options framework.
				$theme_options[$k] = $v;
			}
		}
		
		return $theme_options;
	}

    /**
     * Checks if the component called $name is loaded
     * @param $name
     * @return bool
     */
    static function is_loaded_by_name($name){
        global $loaded_components;
        if(array_key_exists($name,$loaded_components)) {
            return true;
        }
        return false;
    }

    /**
     * Check if the registered component is active (the component must exists)
     * @param string|array $component (l'array può essere ottenuto da get_option("waboot_registered_components"))
     * @return bool
     */
    static function is_active( $component ) {
        if(is_array($component)){
	        $states = self::get_components_state();
	        if(isset($component['nicename'])){
		        $name = $component['nicename'];
		        if(isset($states[$name]) && $states[$name] == 1){
			        return true;
		        }
	        }
        }elseif($component instanceof Component) {
	        return $component->is_active();
        }else{
            $states = self::get_components_state();
            if(isset($states[$component]) && $states[$component] == 1){
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if the main file of the $component is present
     * @param $component
     * @return bool
     */
    static function is_present( $component ){
        if(is_array($component)){
            if(is_file($component['file'])) return true;
        }else{
            $registered_components = self::getAllComponents();
            if(isset($registered_components[$component]) && is_file($registered_components[$component]->file)) return true;
        }
        return false;
    }

    /**
     * Checks if the component is allowed for the page\post being displayed
     * @param Component $c
     * @return bool
     */
    static function is_enable_for_current_page( Component $c ) {
        global $post;

	    $maybe_enabled = false;

        if ( is_admin() )
	        $maybe_enabled = false;

        if ( empty( $c->filters ) ) {
	        $maybe_enabled = false;
        }

        //Normalize data
	    //@todo: find out why some times 'post_type' and 'node_id' are empty arrays
	    if(\is_array($c->filters['post_type']) && count($c->filters['post_type']) === 0){
		    $c->filters['post_type'] = '*';
	    }
        if(\is_array($c->filters['node_id']) && count($c->filters['node_id']) === 0){
        	$c->filters['node_id'] = '*';
        }

        if ( $c->filters['node_id'] == "*" ) {
	        $maybe_enabled = true;
        } else {
	        if(!isset($post->ID)){
		        //We are in archives or 404
		        if(is_404() && in_array("page",$c->filters['post_type'])) $maybe_enabled = true;
				if(is_archive() && in_array("blog", $c->filters['post_type'])) $maybe_enabled = true;
	        }

	        $current_post_type = isset($post) ? get_post_type( $post->ID ) : "null";
            if ( is_home() ) {
                $current_post_id = get_option( "page_for_posts" );
            } else {
                $current_post_id = isset($post) ? $post->ID : 0;
            }
            if ( in_array( $current_post_id, $c->filters['node_id'] ) || in_array( $current_post_type, $c->filters['post_type'] ) ) {
	            $maybe_enabled = true;
            } else {
	            $maybe_enabled = false;
            }
        }

	    $maybe_enabled = apply_filters("wbf/modules/components/is_enabled_for_current_page",$maybe_enabled,$c);
	    $maybe_enabled = apply_filters("wbf/modules/components/{$c->name}/is_enabled_for_current_page",$maybe_enabled);
	    return $maybe_enabled;
    }

    /**
     * Enable or disable components if necessary
     */
    static function toggle_components(){
        global $plugin_page;
	    if(!is_admin()) return;
	    if(!isset($_GET['page']) || $_GET['page'] != GUI::$wp_menu_slug) return;

	    $components_state_changed = false;

        if ( isset( $_GET['enable'] ) ) {
            $component_name = $_GET['enable'];
            try {
                self::enable( $component_name, ComponentsManager::is_child_component( $component_name ) );
	            $components_state_changed = true;
            } catch ( \Exception $e ) {
                self::$last_error = $e->getMessage();
            }
        } elseif ( isset( $_GET['disable'] ) ) {
            $component_name = $_GET['disable'];
            try {
                self::disable( $component_name, ComponentsManager::is_child_component( $component_name ) );
	            $components_state_changed = true;
            } catch ( \Exception $e ) {
                self::$last_error = $e->getMessage();
            }
        } elseif( isset( $_POST['submit-components-options']) ){
            $registered_components = self::getAllComponents();
            $registered_components_status = isset($_POST['components_status']) ? $_POST['components_status'] : array();
            foreach($registered_components as $component_name => $component_data){
                if(!array_key_exists($component_name,$registered_components_status)){
                    $registered_components_status[$component_name] = "off";
                }
            }
            foreach($registered_components_status as $component_name => $component_status){
                if($component_status == "on" ){
                    if(!self::is_active($registered_components[$component_name])){
                        try{
	                        self::enable( $component_name, ComponentsManager::is_child_component( $component_name ) );
	                        $components_state_changed = true;
                        }catch(\Exception $e){
	                        self::$last_error = $e->getMessage();
	                        Utilities::admin_show_message(self::$last_error,"error");
                        }
                    }
                }else{
                    if(self::is_active($registered_components[$component_name])){
                        try{
                            self::disable( $component_name, ComponentsManager::is_child_component( $component_name ) );
	                        $components_state_changed = true;
                        }catch(\Exception $e){
                            self::$last_error = $e->getMessage();
                            Utilities::admin_show_message(self::$last_error,"error");
                        }
                    }
                }
            }
        }

        /*
		 * Restore defaults components
		 */
        if ( isset( $_POST['restore_defaults_components'] ) ) {
	        self::restore_components_state();
	        $components_state_changed = true;
	        Utilities::admin_show_message(__("Component status restored to defaults","wbf"),"success");
        }

        /*
		 * Reset components
		 */
        if ( isset( $_POST['reset_components'] ) ) {
	        self::reset_components_state();
	        $registered_components = self::getAllComponents();
	        array_map( function ( $c ) {
		        $c->active = false;
	        }, $registered_components );
	        $components_state_changed = true;
	        Utilities::admin_show_message(__("Component status reset","wbf"),"success");
        }

	    self::setupComponentsFilters();

        if($components_state_changed){
        	do_action('wbf/modules/components/after_state_changed');
        }
    }

    /**
     * Force enable a component
     *
     * @param $component_name
     * @throws \Exception
     */
    static function ensure_enabled( $component_name ){
        if(self::is_present($component_name)){
            if(!self::is_active($component_name)){
                self::enable($component_name, ComponentsManager::is_child_component( $component_name ));
            }
        }
    }

    /**
     * Enable a component
     *
     * @param $component_name
     * @param bool $child_component
     *
     * @use self::switch_component_state()
     *
     * @throws \Exception
     */
    static function enable( $component_name, $child_component = false ) {
	    self::switch_component_state($component_name, self::STATE_ENABLED, $child_component);
    }

    /**
     * Disable a component
     *
     * @param $component_name
     * @param bool $child_component
     *
     * @use self::switch_component_state()
     *
     * @throws \Exception
     */
    static function disable( $component_name, $child_component = false ) {
	    self::switch_component_state($component_name, self::STATE_DISABLED, $child_component);
    }

	/**
	 * Remove a component from the DB (without calling the disable functions)
	 *
	 * @param $component_name
	 *
	 * @throws \Exception
	 */
    static function remove ( $component_name ) {
	    $states = ComponentsManager::get_components_state();
	    if(isset($states[$component_name])){
	    	unset($states[$component_name]);
	    }
	    ComponentsManager::update_components_state($states);
    }

	/**
	 * Enable or disable a component
	 *
	 * @param string $component_name
	 * @param string $state
	 * @param bool|false $child_component
	 *
	 * @throws \Exception
	 */
	private static function switch_component_state($component_name, $state, $child_component = false){
		$registered_components = self::get_registered_components($child_component);
		if(array_key_exists( $component_name, $registered_components)){
			try{
				$component_params = $registered_components[ $component_name ];
				$oComponent = ComponentFactory::create($component_params);
				if($oComponent instanceof Component){
					//enable or disable
					if($state == self::STATE_ENABLED){
						$oComponent->onActivate();
						$oComponent->active = true;
						//$registered_components[ $component_name ]['enabled'] = true;
					}else{
						$oComponent->onDeactivate();
						$oComponent->active = false;
						//$registered_components[ $component_name ]['enabled'] = false;
					}
					//update the WP Option of registered component
					//self::update_registered_components($registered_components, $child_component);
					self::update_global_components_vars();
				}
			}catch(\Exception $e){
				throw new \Exception($e->getMessage());
			}
		} else {
			if($state === self::STATE_ENABLED){
				throw new \Exception( sprintf(__( "Component not found among registered components. Unable to enable the component: %s.","wbf"), $component_name));
			}else{
				throw new \Exception( sprintf(__( "Component not found among registered components. Unable to deactivate the component: %s","wbf"), $component_name));
			}
		}
	}

	/**
	 * Checks if $registered_component is a child component.
	 *
	 * @param array $registered_component (a component in array form)
	 *
	 * @return bool
	 */
    static function is_child_component($registered_component){
        if(is_array($registered_component)){
            if ( $registered_component['child_component'] == true ){
                return true;
            }
        }elseif($registered_component instanceof Component){
	        if ( $registered_component->is_child_component){
		        return true;
	        }
        }else{
            $components = ComponentsManager::getAllComponents();
            foreach($components as $name => $c){
                if($name == $registered_component){
                    if($c->is_child_component == true){
                        return true;
                    }
                }
            }
        }
        return false;
    }

	/**
	 * Gets all components options
	 *
	 * @return array
	 */
    static function get_components_options(){
	    $component_options = \call_user_func( function () {
		    $cbs = Framework::get_registered_options();
		    $cbs = array_filter( $cbs, function ( $el ) {
			    if ( isset( $el['component'] ) && $el['component'] ) {
				    return true;
			    }

			    return false;
		    } );

		    return $cbs;
	    } ); //Gets the components options (not the actual values)

	    return $component_options;
    }

	/**
	 * @param $option_name
	 *
	 * @return bool
	 */
    static function is_component_option($option_name){
	    $opt = Framework::get_option_object($option_name);
	    return isset( $opt['component'] ) && $opt['component'];
    }

	/**
	 * Reset components state
	 *
	 * @throws \Exception
	 */
    static function restore_components_state(){
        $default_components = apply_filters("wbf_default_components",array()); //todo @deprecated
        $default_components = apply_filters("wbf/modules/components/defaults",$default_components);
        $registered_components = self::getAllComponents();
	    if(is_array($registered_components) && !empty($registered_components)){
		    //Disable all components
		    foreach($registered_components as $c_name => $c_data){
			    if(!isset($c_data->is_child_component)){
				    $c_data->is_child_component = false;
			    }
			    self::disable($c_name, $c_data->is_child_component);
		    }
		    //Remove all components options //todo: this dont work
		    /*
		    $orgz = Organizer::getInstance();
		    $saved_options = Framework::get_saved_options();
		    $registered_components_names = array_keys($registered_components);
		    $registered_components_names_for_regex = implode("|",$registered_components_names);
		    foreach($saved_options as $k => $v){
			    if(preg_match("/^($registered_components_names_for_regex)/",$k)){
				    unset($saved_options[$k]);
			    }
		    }
		    Framework::update_theme_options($saved_options);
		    */
		    //Re-enable only the needed components
		    foreach($default_components as $c_name){
			    self::ensure_enabled($c_name);
		    }
	    }
	    update_option("wbf_components_saved_once", []); //Reset the saved_once state
    }

    /**
     * Delete the options which stores the registered components
     */
    static function reset_components_state(){
	    $theme = wp_get_theme();
	    if(!$theme->errors()){
		    delete_option( $theme->get_stylesheet()."_registered_components");
		    delete_option( $theme->get_template()."_registered_components");
	    }
    }
}