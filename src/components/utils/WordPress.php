<?php
namespace WBF\components\utils;


use WBF\components\utils\woocommerce\WBF_Product_Variation;

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

	/**
	 * Get post type accordingly provided object
	 *
	 * @param $object
	 *
	 * @return false|string
	 */
	public static function get_object_post_type($object){
		if($object instanceof \WP_Term){
			return Terms::get_post_type_by_term($object);
		}elseif($object instanceof \WP_Taxonomy){
			return Terms::get_post_type_by_taxonomy($object);
		}elseif($object instanceof \WP_Post_Type){
			return $object->name;
		}elseif($object instanceof \WP_Post){
			return $object->post_type;
		}
		return false;
	}

	/**
	 * Alias of get_post_meta() that returns the meta unserialized and cache the results.
	 *
	 * @param int|object $post if object id provided, it checks
	 *
	 * @return array
	 */
	static function get_post_metas($post){
		$post_id = false;

		if(!is_numeric($post)){
			if($post instanceof \WP_Post){
				if(isset($post->id)){
					$post_id = $post->id;
				}else{
					$post_id = $post->ID;
				}
			}
		}else{
			$post_id = $post;
		}

		if(!$post_id) return [];

		static $cache;
		if(isset($cache[$post_id])) return $cache[$post_id];

		$metas = array_map(function($value){
			if(is_array($value) && isset($value[0])){
				return maybe_unserialize($value[0]);
			}else{
				return $value;
			}
		},get_post_meta($post_id));

		$cache[$post_id] = $metas;

		return $metas;
	}

	/**
	 * Alias of get_post_meta($post_id,$key,true) that cache the result. If called on a Variation
	 *
	 * @param int|object $post
	 * @param string $key
	 *
	 * @return mixed
	 */
	public static function get_post_meta($post,$key){
		$post_id = false;

		if(!is_numeric($post)){
			if($post instanceof \WP_Post){
				if(isset($post->id)){
					$post_id = $post->id;
				}else{
					$post_id = $post->ID;
				}
			}
		}else{
			$post_id = $post;
		}

		if(!$post_id) return [];

		static $cache = [];

		if(isset($cache[$post_id][$key])) return $cache[$post_id][$key];

		$meta = get_post_meta($post_id,$key,true);

		if($meta){
			$cache[$post_id][$key] = $meta;
		}

		return $meta;
	}
}