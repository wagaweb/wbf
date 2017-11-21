<?php

namespace WBF\modules\components;

use WBF\components\mvc\HTMLView;
use WBF\components\utils\Utilities;
use WBF\modules\components\ComponentsManager;
use WBF\modules\options\Framework;
use WBF\modules\options\Organizer;

class GUI {

	public static $wp_menu_slug = "wbf_components";

	public static function scripts( $hook ) {
		global $plugin_page;
		if ( $plugin_page == GUI::$wp_menu_slug ) {
			wp_enqueue_media();
		}
	}

	public static function add_menu( $parent_slug ) {
		WBF()->add_submenu_page(
			__( "Waboot Components", "wbf" ),
			__( "Components", "wbf" ),
			"activate_plugins",
			self::$wp_menu_slug,
			'\WBF\modules\components\GUI::components_admin_page'
		);
	}

	/**
	 * Performs some operations in components options before saving
	 */
	private static function sanitize_components_options($options,$registered_components){
		$options = apply_filters("wbf/modules/components/options_sanitization_before_save",$options,$registered_components);
		return $options;
	}

	/**
	 * Display the component page
	 */
	public static function components_admin_page() {

		$registered_components = ComponentsManager::getAllComponents();

		$options_updated_flag = false;

		/*
		 * Save Component Options
		 */
		if ( isset( $_POST['submit-components-options'] ) ) {
			$of_config_id = Framework::get_options_root_id();
			if ( isset( $_POST[ $of_config_id ] ) ) {
				$component_options = ComponentsManager::get_components_options();

				$options_to_update = self::sanitize_components_options($_POST[ $of_config_id ],$registered_components);

				$options_to_update = Framework::update_theme_options( $options_to_update, true, $component_options );

				$theme = wp_get_theme();

				//Save components options to auxiliary array
				if ( isset( $options_to_update ) && $options_to_update ) {
					update_option( "wbf_" . $theme->get_stylesheet() . "_components_options", $options_to_update );
				}

				//Set the flag that tells that the components was saved at least once
				$components_already_saved = (array) get_option( "wbf_components_saved_once", array() );
				if ( ! in_array( $theme->get_stylesheet(), $components_already_saved ) ) {
					$components_already_saved[] = $theme->get_stylesheet();
					update_option( "wbf_components_saved_once", $components_already_saved );
				}

				$options_updated_flag = true;
				
				Utilities::add_admin_notice("options_updated",_x("Options updated successfully","Component Page","wbf"),"success",['manual_display' => true]);
			}
		}

		$components_options          = Organizer::getInstance()->get_group( "components" );
		$compiled_components_options = array();
		$current_element             = "";
		foreach ( $components_options as $key => $option ) {
			if ( $option['type'] == "heading" ) {
				$current_element                                 = preg_replace( "/ Component/", "", $option['name'] );
				$compiled_components_options[ $current_element ] = array();
				continue;
			}
			$compiled_components_options[ $current_element ][] = $components_options[ $key ];
		}

		//Let's categorize the components
		$categorized_registered_components = [];
		$component_categories_weights = [];
		foreach ($registered_components as $c){
			if(isset($c->category)){
				$categorized_registered_components[$c->category][$c->name] = $c;
				if(!array_key_exists($c->category,$component_categories_weights)){
					$component_categories_weights[$c->category] = 10;
				}
			}else{
				$categorized_registered_components["_uncategorized"][$c->name] = $c;
				if(!array_key_exists("_uncategorized",$component_categories_weights)){
					$component_categories_weights["_uncategorized"] = 10;
				}
			}
		}
		//...And sort them among categories
		foreach ($categorized_registered_components as $category => $components){
			uksort($categorized_registered_components[$category],function($a, $b){
				if($a == $b){
					return 0;
				}
				return $a < $b ? - 1 : 1;
			});
		}

		//Sort by categories
		$component_categories_weights = apply_filters("wbf/modules/components/categories_weights",$component_categories_weights);
		uksort($categorized_registered_components,function($a,$b) use($component_categories_weights){
			if($component_categories_weights[$a] < $component_categories_weights[$b]){
				return -1;
			}elseif($component_categories_weights[$a] > $component_categories_weights[$b]){
				return 1;
			}else{
				return 0;
			}
		});

		//Sort the un-categorized components array
		uksort($registered_components, function($a, $b){
			if($a == $b){
				return 0;
			}
			return $a < $b ? - 1 : 1;
		});
		
		//Checking for errors
		if( ( isset( $_GET['enable'] ) || isset( $_GET['disable'] ) ) && ! empty( ComponentsManager::$last_error ) ){
			Utilities::add_admin_notice("options_updated",ComponentsManager::$last_error,"error",['manual_display' => true]);
		}

		if ( isset( $_POST['submit-components-options'] ) ) {
			do_action('wbf/modules/components/after_components_options_saved',$registered_components,$categorized_registered_components,$compiled_components_options,$options_updated_flag);
		}

		$v = new HTMLView( "src/modules/components/views/components-page.php", "wbf");
		$v->clean()->display([
			'page_title'                        => _x("Components","Components page title","wbf"),
			'registered_components'             => $registered_components,
			'categorized_registered_components' => $categorized_registered_components,
			'compiled_components_options'       => $compiled_components_options,
			'last_error'                        => ( isset( $_GET['enable'] ) || isset( $_GET['disable'] ) ) && ! empty( ComponentsManager::$last_error ) ? ComponentsManager::$last_error : false,
			'options_updated_flag'              => $options_updated_flag
		]);
	}}