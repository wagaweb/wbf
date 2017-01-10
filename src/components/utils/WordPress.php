<?php
namespace WBF\components\utils;


class WordPress {
	/**
	 * Return a sanitized version of blog name
	 *
	 * @return string
	 */
	static function get_sanitized_blogname(){
		return sanitize_title_with_dashes(get_bloginfo("name"));
	}

	/**
	 * Adds a new TinyMCE plugin
	 *
	 * @param string $id plugin identifier (can be any [a-z_]+ string.
	 * @param array $params [
	 *  'plugin_path' => path/to/plugin/js
	 *  'create_button' => false|true
	 * ]
	 *
	 * @throws \Exception
	 */
	static function add_tinymce_plugin($id,$params){

		$params = wp_parse_args($params,[
			'plugin_path' => '',
			'create_button' => false
		]);

		// init process for registering our button
		add_action('init', function() use($id, $params){
			//Abort early if the user will never see TinyMCE
			if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') && get_user_option('rich_editing') == 'true') return;

			//Add a callback to regiser our tinymce plugin
			add_filter("mce_external_plugins", function($plugin_array) use($id, $params) {
				$plugin_array[$id] = $params['plugin_path'];
				return $plugin_array;
			});

			if($params['create_button']){
				// Add a callback to add our button to the TinyMCE toolbar
				add_filter('mce_buttons', function($buttons) use($id, $params) {
					//Add the button ID to the $button array
					$buttons[] = $id;
					return $buttons;
				});
			}
		});
	}

	/**
	 * Checks if we are in WP CLI
	 *
	 * @return bool
	 */
	static function is_wp_cli(){
		return defined("WP_CLI") && WP_CLI;
	}
}