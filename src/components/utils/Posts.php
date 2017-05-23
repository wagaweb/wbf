<?php
namespace WBF\components\utils;


use WBF\components\mvc\HTMLView;

class Posts {
	/**
	 * Get a list of post types without the blacklisted ones
	 * @param array $blacklist
	 *
	 * @return array
	 */
	static function get_filtered_post_types($blacklist = array()){
		$post_types = get_post_types();
		$result = array();
		$blacklist = array_merge($blacklist,array('attachment','revision','nav_menu_item','ml-slider','custom_css','customize_changeset'));
		$blacklist = array_unique(apply_filters("wbf/utilities/get_filtered_post_types/blacklist",$blacklist));
		foreach($post_types as $pt){
			if(!in_array($pt,$blacklist)){
				$pt_obj = get_post_type_object($pt);
				$result[$pt_obj->name] = $pt_obj->label;
			}
		}

		$result = array_unique(apply_filters("wbf/utilities/get_filtered_post_types",$result));

		return $result;
	}

	/**
	 * Get posts while preserving memory
	 *
	 * @param callable $callback a function that will be called for each post. You can use it to additionally filter the posts. If it returns true, the post will be added to output array.
	 * @param array    $args normal arguments for WP_Query
	 * @param bool     $include_meta the post meta will be included in the post object (default to FALSE)
	 *
	 * @return array of posts
	 */
	static function recursive_get_posts(\closure $callback = null, $args = array(), $include_meta = false){
		$all_posts = [];
		$page = 1;
		$get_posts = function ( $args ) use ( &$page ) {
			$args = wp_parse_args( $args, array(
				'post_type' => 'post',
				'paged' => $page,
			) );
			$all_posts = new \WP_Query( $args );
			if ( count( $all_posts->posts ) > 0 ) {
				return $all_posts;
			} else {
				return false;
			}
		};
		while ( $paged_posts = $get_posts( $args ) ) {
			$i = 0;
			while ( $i <= count( $paged_posts->posts ) - 1 ) { //while($all_posts->have_posts()) WE CANNOT USE have_posts... too many issue
				//if($i == 1) $all_posts->next_post(); //The first next post does not change $all_posts->post for some reason... so we need to do it double...
				$p = $paged_posts->posts[ $i ];
				if($include_meta){
					$p->meta = get_post_meta($p->ID);
				}
				if(isset($callback)){
					$result = call_user_func( $callback, $p );
					if($result){
						$all_posts[$p->ID] = $p;
					}
				}else{
					$all_posts[$p->ID] = $p;
				}
				//if($i < count($all_posts->posts)) $all_posts->next_post();
				$i ++;
			}
			$page ++;
		}
		return $all_posts;
	}

	/**
	 * Get the src of the $post_id thumbnail
	 *
	 * @param $post_id
	 * @param null $size
	 * @return mixed
	 */
	static function get_post_thumbnail_src($post_id,$size=null){
		$post_thumbnail_id = get_post_thumbnail_id($post_id);
		$thumbnail = wp_get_attachment_image_src($post_thumbnail_id,$size);
		if(isset($thumbnail[0])){
			return $thumbnail[0];
		}
		return false;
	}

	/**
	 * Return an ID of an attachment by searching the database with the file URL.
	 *
	 * First checks to see if the $url is pointing to a file that exists in
	 * the wp-content directory. If so, then we search the database for a
	 * partial match consisting of the remaining path AFTER the wp-content
	 * directory. Finally, if a match is found the attachment ID will be
	 * returned.
	 *
	 * @see https://gist.github.com/fjarrett/5544469#file-gistfile1-php
	 *
	 * @param string $url The URL of the image (ex: http://mysite.com/wp-content/uploads/2013/05/test-image.jpg)
	 *
	 * @return int|null $attachment Returns an attachment ID, or null if no attachment is found
	 */
	static function get_attachment_id_by_url($url) {
		// Split the $url into two parts with the wp-content directory as the separator
		$parsed_url  = explode( parse_url( WP_CONTENT_URL, PHP_URL_PATH ), $url );

		// Get the host of the current site and the host of the $url, ignoring www
		$this_host = str_ireplace( 'www.', '', parse_url( home_url(), PHP_URL_HOST ) );
		$file_host = str_ireplace( 'www.', '', parse_url( $url, PHP_URL_HOST ) );
		// Return nothing if there aren't any $url parts or if the current host and $url host do not match
		if ( ! isset( $parsed_url[1] ) || empty( $parsed_url[1] ) || ( $this_host != $file_host ) ) {
			return null;
		}
		// Now we're going to quickly search the DB for any attachment GUID with a partial path match
		// Example: /uploads/2013/05/test-image.jpg
		global $wpdb;
		$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts WHERE guid RLIKE %s;", $parsed_url[1] ) );
		// Returns null if no attachment is found
		if ( ! empty( $attachment[0] ) ) {
			return $attachment[0];
		} else {
			return false;
		}
	}

	/**
	 * Display the content navigation.
	 *
	 * This require a valid WBF View. Into this view some variables will be passed:
	 * - {bool} $can_display_pagination
	 * - {bool} $show_pagination
	 * - {string} $pagination
	 * - {int} $max_num_pages ($query->max_num_pages)
	 *
	 * @throws \Exception
	 *
	 * @param string|array $tpl_file a pointer to a file to render the template into. If array, must contain the file path at [0] and the plugin name at [1].
	 * @param bool $show_pagination
	 * @param bool $query
	 * @param bool $current_page
	 * @param string $paged_var_name You can supply different paged var name for multiple pagination. The name must be previously registered with add_rewrite_tag()
	 */
	static function the_post_navigation($tpl_file, $show_pagination = false, $query = false, $current_page = false, $paged_var_name = "paged"){
		if($show_pagination){
			if(!$query){
				global $wp_query;
				$query = $wp_query;
			}
			$big = 999999999; // need an unlikely integer
			if($paged_var_name != "paged"){
				$base =  add_query_arg([
					$paged_var_name => "%#%"
				]);
				$base = home_url().$base;
			}else{
				$base =  str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) );
			}
			if(!$current_page){
				if(!$query){
					$current_page = max( 1, intval(get_query_var($paged_var_name)) );
				}else{
					$current_page = max(1, intval($query->get($paged_var_name)) );
				}
			}
			$current_page = intval($current_page);
			$paginate = paginate_links([
				'base' => $base,
				'format' => '?'.$paged_var_name.'=%#%',
				'current' => $$current_page,
				'total' => $query->max_num_pages
			]);
			$paginate_array = explode("\n",$paginate);
			foreach($paginate_array as $k => $link){
				$paginate_array[$k] = "<li>".$link."</li>";
			}
			$pagination = implode("\n",$paginate_array);
		}else{
			$pagination = "";
		}

		if(is_array($tpl_file)){
			if(count($tpl_file) != 2){
				throw new \Exception('Invalid number of indexes for $tpl_file');
			}
			$v = new HTMLView($tpl_file[0],$tpl_file[1]);
		}else{
			$v = new HTMLView($tpl_file);
		}

		$v->display([
			'can_display_pagination' => true, //todo: do some condition?
			'show_pagination' => $show_pagination,
			'pagination' => $pagination,
			'max_num_pages' => $query->max_num_pages
		]);
	}

	/**
	 * Adds a new custom column
	 *
	 * @param $post_type
	 * @param $slug
	 * @param $label
	 * @param callable $display_callback
	 * @param bool $sortable
	 * @param bool|callable $sortable_callback
	 *
	 * @throws \Exception
	 */
	static function add_custom_column($post_type,$slug,$label, Callable $display_callback, $sortable = false, $sortable_callback = false){
		add_filter("manage_".$post_type."_posts_columns", function($columns) use($slug,$label){
			$columns[$slug] = $label;
			return $columns;
		}, 11, 2);
		if($sortable){
			add_filter('manage_edit-'.$post_type.'_sortable_columns', function($columns) use($slug){
				$columns[$slug] = $slug;
				return $columns;
			});
		}
		add_action("manage_".$post_type."_posts_custom_column", function($column_name,$post_id) use($slug,$display_callback){
			if($column_name == $slug){
				$display_callback($post_id);
			}
		}, 11, 2);
		if($sortable){
			if(!$sortable_callback){
				throw new \Exception("Sortable callback was not defined");
			}
			add_action( 'pre_get_posts', function($query) use($post_type, $sortable_callback){
				if(!is_admin()) return;
				if(!function_exists("get_current_screen")) return;
				$screen = get_current_screen();
				if(!$screen instanceof \WP_Screen) return;
				if($screen->id != "edit-".$post_type) return;
				$sortable_callback($query);
			} );
		}
	}
}