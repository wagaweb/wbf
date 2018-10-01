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
	 * Alias of get_post_meta($post_id,$key,true) that cache the result.
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

	/**
	 * Toggle maintenance mode for the site.
	 *
	 * Creates/deletes the maintenance file to enable/disable maintenance mode.
	 *
	 * @param bool $enable True to enable maintenance mode, false to disable.
	 *
	 * @extracted from 'class-wp-upgrader.php'
	 */
	public static function maintenance_mode( $enable = false ) {
		$file = ABSPATH . '.maintenance';
		if ( $enable ) {
			// Create maintenance file to signal that we are upgrading
			$maintenance_string = '<?php $upgrading = ' . time() . '; ?>';
			if(is_file($file)){
				unlink($file);
			}
			file_put_contents($file, $maintenance_string);
		} elseif ( ! $enable && is_file($file) ) {
			unlink($file);
		}
	}

	/**
	 * Wrapper for 'wp_ajax_' 'wp_ajax_nopriv_' actions. It automatically tests for DOING_AJAX
	 *
	 * @param string $name
	 * @param callable $callback
	 * @param int $priority
	 * @param int $accepted_args
	 */
	public static function add_ajax_endpoint($name,$callback,$priority = 10,$accepted_args = 1){
		if(!is_callable($callback)){
			trigger_error('Invalid callback for ajax endpoint',E_USER_WARNING);
			return;
		}
		$wrapperCallback = function() use($callback){
			if(!defined('DOING_AJAX') || !DOING_AJAX) return;
			$callback();
		};
		add_action('wp_ajax_'.$name,$wrapperCallback,$priority,$accepted_args);
		add_action('wp_ajax_nopriv_'.$name,$wrapperCallback,$priority,$accepted_args);
	}

	/**
	 * Save a file as WP attachment
	 *
	 * @param string $fromPath the path to the file to save as attachment
	 * @param string|null $time Time formatted in 'yyyy/mm'.
	 * @param string|null $targetCustomFilename custom target filename with extension.
	 * @param array $attachmentData additional metadata for the attachment
	 * @param int $parentId the post to which link the attachment (can be 0)
	 * @param bool $unlinkOriginalFile whether delete the original file
	 * @param string $wpAdminIncludesPath the path to the wp-admin/includes directory (the function needs image.php file from this directory to generate the attachment metadata)
	 *
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 *
	 * @uses \wp_upload_dir()
	 * @uses \wp_unique_filename()
	 * @uses \wp_check_filetype_and_ext()
	 * @uses \wp_insert_attachment()
	 * @uses \wp_update_attachment_metadata()
	 * @uses \wp_generate_attachment_metadata()
	 *
	 * @see \_wp_handle_upload()
	 * @see \media_handle_upload()
	 *
	 * @return int
	 */
	public static function save_file_as_attachment($fromPath, $targetCustomFilename = null, $time = null,array  $attachmentData = [], $parentId = 0, $unlinkOriginalFile = false, $wpAdminIncludesPath = null){
		if(!\file_exists($fromPath)){
			throw new \InvalidArgumentException("Unable to save $fromPath as attachment: file not found");
		}
		if($time === null){
			$time = (new \DateTime())->format('Y/m');
		}
		$uploads = \wp_upload_dir($time);
		if(!\is_array($uploads) || $uploads['error'] !== false){
			if(\is_array($uploads)){
				throw new \RuntimeException("Unable to save $fromPath as attachment: ".$uploads['error']);
			}
			throw new \RuntimeException("Unable to save $fromPath as attachment");
		}

		$fromPathInfo = \pathinfo($fromPath);
		if($targetCustomFilename === null){
			$targetFilename = \wp_unique_filename($uploads['path'], $fromPathInfo['basename']);
		}else{
			$targetFilename = \wp_unique_filename($uploads['path'], $targetCustomFilename);
		}
		$targetPath = $uploads['path'] . "/$targetFilename"; //Destination path.

		//Compute the mimetype
		$wpFileType = \wp_check_filetype_and_ext($fromPath, $fromPathInfo['basename']);

		//Move the file
		if(!\copy($fromPath,$targetPath)){
			throw new \RuntimeException("Unable to save $fromPath as attachment: unable to move the file to ".$targetPath);
		}
		if($unlinkOriginalFile){
			\unlink($fromPath);
		}

		//Set correct file permissions (borrowed from _wp_handle_upload())
		$stat = \stat(\dirname($targetPath));
		$perms = $stat['mode'] & 0000666;
		@\chmod($targetPath, $perms);

		//Compute the URL.
		$url = $uploads['url'] . "/$targetFilename";

		//Construct the attachment array
		$attachment = \wp_parse_args($attachmentData,[
			'post_mime_type' => $wpFileType['type'],
			'guid' => $url,
			'post_parent' => $parentId,
			'post_title' => $fromPathInfo['filename'],
			'post_content' => '',
			'post_excerpt' => '',
		]);

		//This should never be set as it would then overwrite an existing attachment.
		unset($attachment['ID']);

		//Save the data
		if(!\function_exists('wp_generate_attachment_metadata')){
			if($wpAdminIncludesPath === null){
				$wpAdminIncludesPath = ABSPATH.'/wp-admin/includes/image.php';
			}
			require_once $wpAdminIncludesPath;
		}
		$id = \wp_insert_attachment( $attachment, $targetPath, $parentId, true );
		if(!is_wp_error($id)){
			\wp_update_attachment_metadata($id, \wp_generate_attachment_metadata($id, $targetPath));
		}else{
			throw new \RuntimeException("Unable to save $fromPath as attachment: ".$id->get_error_message());
		}

		return $id;
	}

	/**
	 * Sign-in an user by $login and $password
	 *
	 * @param string $login
	 * @param string $password
	 * @param bool $remember
	 * @param bool $secure_cookie
	 *
	 * @return \WP_Error|\WP_User
	 */
	public static function signin_by_credentials($login,$password,$remember = true,$secure_cookie = true){
		$r = wp_signon([
			'user_login' => $login,
			'user_password' => $password,
			'remember' => $remember
		],$secure_cookie);
		if($r instanceof \WP_User){
			wp_set_current_user($r->ID);
		}
		return $r;
	}

	/**
	 * Sign-in an user by the provided $fieldKey and $fieldValue
	 *
	 * @param string $fieldKey (can be: 'id' || 'login' || 'email' || 'slug')
	 * @param string $fieldValue
	 * @param bool $remember
	 * @param bool $secure_cookie
	 *
	 * @return \WP_Error|\WP_User
	 */
	public static function signin_by($fieldKey,$fieldValue,$remember = true,$secure_cookie = true){
		$user = false;
		switch($fieldKey){
			case 'id':
				$user = get_user_by('id',$fieldValue);
				break;
			case 'login':
				$user = get_user_by('login',$fieldValue);
				break;
			case 'email':
				$user = get_user_by('email',$fieldValue);
				break;
			case 'slug':
				$user = get_user_by('slug',$fieldValue);
				break;
		}
		if($user instanceof \WP_User){
			wp_set_auth_cookie($user->ID,$remember,$secure_cookie);
			do_action('wp_login', $user->user_login, $user);
			wp_set_current_user($user->ID);
			return $user;
		}
		return new \WP_Error('wbf_invalid_login','Invalid login');
	}
}