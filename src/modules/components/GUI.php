<?php

namespace WBF\modules\components;

use WBF\components\mvc\HTMLView;
use WBF\modules\components\ComponentsManager;
use WBF\modules\options\Framework;
use WBF\modules\options\Organizer;

class GUI {

	public static $wp_menu_slug = "wbf_components";

	public static function scripts( $hook ) {
		global $plugin_page;
		if ( $plugin_page == GUI::$wp_menu_slug ) {
			// Enqueue custom CSS
			$stylesheet = \WBF::prefix_url( 'assets/dist/css/componentsframework.css' );
			if ( $stylesheet != "" ) {
				wp_enqueue_style( 'waboot-theme-components-style', $stylesheet, array(), '1.0.0', 'all' ); //Custom Theme Options CSS
			}
			if ( defined( "OPTIONS_FRAMEWORK_URL" ) ) {
				// Enqueue custom option panel JS
				wp_enqueue_script( 'options-custom', OPTIONS_FRAMEWORK_URL . 'js/options-custom.js', array(
					'jquery',
					'wp-color-picker'
				) );
			}
			/*if(WBF_ENV == "dev"){
				wp_register_script('component-page-script',WBF_URL."/assets/src/js/admin/components-page.js",array('jquery'));
			}else{
				wp_register_script('component-page-script',WBF_URL."/admin/js/components-page.min.js",array('jquery'));
			}
			wp_enqueue_script('component-page-script');*/
		}
	}

	public static function add_menu( $parent_slug ) {
		add_submenu_page( $parent_slug, __( "Waboot Components", "wbf" ), __( "Components", "wbf" ), "activate_plugins", self::$wp_menu_slug, '\WBF\modules\components\GUI::components_admin_page', "", 66 );
	}

	/**
	 * Display the component page
	 */
	public static function components_admin_page() {

		$options_updated_flag = false;

		/*
		 * Restore defaults components
		 */
		if ( isset( $_POST['restore_defaults_components'] ) ) {
			ComponentsManager::restore_components_state();
			$options_updated_flag = true;
		}

		/*
		 * Reset components
		 */
		if ( isset( $_POST['reset_components'] ) ) {
			ComponentsManager::reset_components_state();
			$options_updated_flag = true;
		}

		$registered_components = ComponentsManager::getAllComponents();
		if ( isset( $_POST['reset_components'] ) ) {
			array_map( function ( $c ) { $c->active = false; }, $registered_components );
		}

		/*
		 * Save Component Options
		 */
		if ( isset( $_POST['submit-components-options'] ) ) {
			$of_config_id = Framework::get_options_root_id();
			if ( isset( $_POST[ $of_config_id ] ) ) {
				$component_options = call_user_func( function () {
					$cbs = Framework::get_registered_options();
					$cbs = array_filter( $cbs, function ( $el ) {
						if ( isset( $el['component'] ) && $el['component'] ) {
							return true;
						}

						return false;
					} );

					return $cbs;
				} ); //Gets the components options (not the actual values)

				$options_to_update = $_POST[ $of_config_id ];

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

		uksort( $registered_components, function ( $a, $b ) {
			if ( $a == $b ) {
				return 0;
			}

			return $a < $b ? - 1 : 1;
		} );

		( new HTMLView( "src/modules/components/views/components_page.php", "wbf" ) )->clean()->display( [
			'registered_components'       => $registered_components,
			'compiled_components_options' => $compiled_components_options,
			'last_error'                  => ( isset( $_GET['enable'] ) || isset( $_GET['disable'] ) ) && ! empty( ComponentsManager::$last_error ) ? ComponentsManager::$last_error : false,
			'options_updated_flag'        => $options_updated_flag
		] );
	}}