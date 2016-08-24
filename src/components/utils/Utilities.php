<?php

namespace WBF\components\utils;

use WBF\components\notices\Notice_Manager;

class Utilities{

	const PAGE_TYPE_DEFAULT_HOME = "default_home";
	const PAGE_TYPE_STATIC_HOME = "static_home";
	const PAGE_TYPE_BLOG_PAGE = "blog_page";
	const PAGE_TYPE_COMMON = "common";

	/**
	 * Return a sanitized version of blog name
	 *
	 * @return string
	 */
	static function get_sanitized_blogname(){
		return sanitize_title_with_dashes(get_bloginfo("name"));
	}

	/**
	 * WBF version of get_template_part. In addition to the WP own method, it uses WBF locate_template that looks into plugins dir.
	 * If want to use your plugin templates parts you have to use this function.
	 *
	 * @param $slug
	 * @param null $name
	 */
	static function get_template_part($slug, $name = null){
		do_action( "get_template_part_{$slug}", $slug, $name );

		$templates = apply_filters("wbf/get_template_part/path:{$slug}",array(),array($slug,$name)); //@deprecated from WBF ^0.11.0
		$name = (string) $name;
		if ( '' !== $name )
			$templates['names'][] = "{$slug}-{$name}.php";

		$templates['names'][] = "{$slug}.php";

		self::locate_template($templates, true, false);
	}

	/**
	 *
	 * Converts HSV to RGB values
	 *
	 * @param $iH
	 * @param $iS
	 * @param $iV
	 *
	 * @return string
	 */
	static function fGetRGB($iH, $iS, $iV) {
		if($iH < 0)   $iH = 0;   // Hue:
		if($iH > 360) $iH = 360; //   0-360
		if($iS < 0)   $iS = 0;   // Saturation:
		if($iS > 100) $iS = 100; //   0-100
		if($iV < 0)   $iV = 0;   // Lightness:
		if($iV > 100) $iV = 100; //   0-100
		$dS = $iS/100.0; // Saturation: 0.0-1.0
		$dV = $iV/100.0; // Lightness:  0.0-1.0
		$dC = $dV*$dS;   // Chroma:     0.0-1.0
		$dH = $iH/60.0;  // H-Prime:    0.0-6.0
		$dT = $dH;       // Temp variable
		while($dT >= 2.0) $dT -= 2.0; // php modulus does not work with float
		$dX = $dC*(1-abs($dT-1));     // as used in the Wikipedia link
		switch(floor($dH)) {
			case 0:
				$dR = $dC; $dG = $dX; $dB = 0.0; break;
			case 1:
				$dR = $dX; $dG = $dC; $dB = 0.0; break;
			case 2:
				$dR = 0.0; $dG = $dC; $dB = $dX; break;
			case 3:
				$dR = 0.0; $dG = $dX; $dB = $dC; break;
			case 4:
				$dR = $dX; $dG = 0.0; $dB = $dC; break;
			case 5:
				$dR = $dC; $dG = 0.0; $dB = $dX; break;
			default:
				$dR = 0.0; $dG = 0.0; $dB = 0.0; break;
		}
		$dM  = $dV - $dC;
		$dR += $dM; $dG += $dM; $dB += $dM;
		$dR *= 255; $dG *= 255; $dB *= 255;
		return round($dR).",".round($dG).",".round($dB);
	}

	/**
	 * Search for $file in WBF directory, plus template and stylesheet directories
	 *
	 * @param $file
	 * @param bool $load
	 * @param bool $require_once
	 * @return string
	 * @throws \Exception
	 */
	static function locate_file($file, $load = false, $require_once = true){
		$located = '';

		//Defining search paths
		$search_paths = [];
		if(defined("WBF_DIRECTORY")){
			$search_paths[] = WBF_DIRECTORY;
		}
		$wbf_path_opt = get_option("wbf_path");
		if($wbf_path_opt && !empty($wbf_path_opt) && $wbf_path_opt != WBF_DIRECTORY){
			$search_paths[] = $wbf_path_opt;
			unset($wbf_path_opt);
		}
		$search_paths[] = get_template_directory();
		$search_paths[] = get_stylesheet_directory();

		//Searching:

		foreach($search_paths as $p){
			$path = rtrim($p,"/") . '/'.ltrim($file,"/");
			if(file_exists($path)){
				$located = $path;
				break;
			}
		}

		if($located == ''){
			throw new \Exception(sprintf(__("File: %s non found in any of the followinf paths: %s","wbf"),$file,implode(";\n",$search_paths)));
		}

		if ( $load && '' != $located ){
			if($require_once){
				require_once $located;
			}else{
				require $located;
			}
		}

		return $located;
	}

	/**
	 * Retrieve the template file from various set of sources.
	 * It is used mainly by TemplatePlugin to add sources for template parts. @see: TemplatePlugin->add_template_base_path()
	 *
	 * @param array $templates an associative array that must contain at least "names" key. It can have the "sources" key, with a list of paths to explore.
	 * @param bool|false $load if TRUE it calls load_template()
	 * @param bool|true $require_once it $load is TRUE, it assigned as the second argument to load_template()
	 *
	 * @return string
	 */
	static function locate_template($templates, $load = false, $require_once = true ) {
		$located = '';
		$template_names = $templates['names'];
		$template_sources = isset($templates['sources']) ? $templates['sources'] : array();
		$registered_base_paths = apply_filters("wbf/get_template_part/base_paths",array());

		//Search into template dir
		foreach( (array) $template_names as $template_name){
			if(!$template_name){
				continue;
			}

			$search_locations = [
				get_stylesheet_directory() . '/' . $template_name,
				get_stylesheet_directory()."/templates/parts/".$template_name,
				get_template_directory() . '/' . $template_name,
				get_template_directory() . '/templates/parts/' . $template_name
			];

			$search_locations = array_unique($search_locations);

			foreach($search_locations as $loc){
				if(file_exists($loc)){
					$located = $loc;
					break;
				}
			}

			if($located != ""){
				break;
			}

			if(!empty($registered_base_paths)){
				//Search into registered base dirs
				foreach($registered_base_paths as $path){
					$path = rtrim($path,"/") . '/'.ltrim($template_name,"/");
					if(file_exists( $path )){
						$located = $path;
						break;
					}
				}
				if($located){
					break;
				}
			}
		}

		//Search into plugins dir
		if(empty($located)) {
			foreach($template_sources as $template_name){
				if ( !$template_name )
					continue;
				if( file_exists($template_name)){
					$located = $template_name;
					break;
				}
			}
		}

		if ( $load && '' != $located ){
			load_template( $located, $require_once );
		}

		return $located;
	}

	/**
	 * Retrieve the URI of the highest priority template file that exists.
	 *
	 * Searches in the stylesheet directory before the template directory so themes
	 * which inherit from a parent theme can just override one file.
	 *
	 * @param string|array $template_names Template file(s) to search for, in order.
	 * @return string The URI of the file if one is located.
	 */
	static function locate_template_uri($template_names){
		$located = '';
		foreach ((array)$template_names as $template_name) {
			if (!$template_name)
				continue;

			if (file_exists(get_stylesheet_directory() . '/' . $template_name)) {
				$located = get_stylesheet_directory_uri() . '/' . $template_name;
				break;
			} else if (file_exists(get_template_directory() . '/' . $template_name)) {
				$located = get_template_directory_uri() . '/' . $template_name;
				break;
			}
		}

		return $located;
	}

	/**
	 * Get the current page type. Can be "default_home" | "static_home" | "blog_page" | "common"
	 *
	 * @return string
	 */
	static function get_current_page_type(){
		if ( is_front_page() && is_home() ) {
			// Default homepage
			return self::PAGE_TYPE_DEFAULT_HOME;
		} elseif ( is_front_page() ) {
			// static homepage
			return self::PAGE_TYPE_STATIC_HOME;
		} elseif ( is_home() ) {
			// blog page
			return self::PAGE_TYPE_BLOG_PAGE;
		} else {
			//everything else
			return self::PAGE_TYPE_COMMON;
		}
	}

	/**
	 * Recursively create directories
	 *
	 * @param $path
	 *
	 * @return bool
	 */
	static function mkpath($path) {
		if(@mkdir($path) or file_exists($path)) return true;
		return (self::mkpath(dirname($path)) and mkdir($path));
	}

	/**
	 * Get an instance of Notice Manager.
	 *
	 * @param bool|FALSE $provide_new
	 *
	 * @return Notice_Manager
	 */
	static function get_wbf_notice_manager($provide_new = false){
		if($provide_new){
			return new Notice_Manager();
		}
		
		global $wbf_notice_manager;
		if(!$wbf_notice_manager){
			$wbf_notice_manager = new Notice_Manager();
			$GLOBALS['wbf_notice_manager'] = $wbf_notice_manager;
		}
		return $wbf_notice_manager;
	}

	/**
	 * Show a flash message in the dashboard
	 *
	 * @param $m
	 * @param $type
	 */
	static function admin_show_message($m, $type) {
		self::add_admin_notice("adm_notice_".rand(1,50),$m,$type,['category'=>'_flash_']);
	}

	/**
	 * Add an admin notice
	 *
	 * @uses WBF\admin\Notice_Manager
	 *
	 * @param String $id
	 * @param String $message
	 * @param String $level (can be: "updated","error","nag")
	 * @param array $args (category[default:base], condition[default:null], cond_args[default:null])
	 */
	static function add_admin_notice($id,$message,$level,$args = []){
		$wbf_notice_manager = self::get_wbf_notice_manager();
		
		$args = wp_parse_args($args,[
			"category" => '_flash_',
			"condition" => null,
			"cond_args" => null
		]);

		$wbf_notice_manager->add_notice($id,$message,$level,$args['category'],$args['condition'],$args['cond_args']);
	}

	/**
	 * Get a list of post types without the blacklisted ones
	 * @param array $blacklist
	 *
	 * @return array
	 */
	static function get_filtered_post_types($blacklist = array()){
		$post_types = get_post_types();
		$result = array();
		$blacklist = array_merge($blacklist,array('attachment','revision','nav_menu_item','ml-slider'));
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
	 * Assure existence of $table_name.
	 *
	 * @param string $table_name (eventual prefix will be stripped out)
	 *
	 * @return bool
	 */
	static function table_exists($table_name){
		global $wpdb;
		$wpdb->suppress_errors();
		static $cache;

		//If prefix already exists, strip it
		if(preg_match("/{$wpdb->prefix}/",$table_name)){
			$table_name = ltrim($table_name,$wpdb->prefix);
		}

		if(isset($cache[$table_name])) return $cache[$table_name];

		$search = $wpdb->query("SHOW TABLES LIKE '".$wpdb->prefix.$table_name."'");
		if($search){
			$cache[$table_name] = true;
			return true;
		}
		$cache[$table_name] = false;
		return false;
	}

	/**
	 * Give the percentage of a number
	 *
	 * @param $number
	 * @param $percentage
	 *
	 * @return float|int
	 */
	static function get_percentage($number,$percentage){
		if(!is_string($number) && !is_float($number) && !is_int($number)){
			throw new \InvalidArgumentException("Number must be string, int or float");
		}
		if(is_string($number)){
			$number = floatval($number);
		}
		if($number == 0) return 0;
		if($number < 0) $number = $number * -1;

		$new_value = ($percentage / 100) * $number;

		return $new_value;
	}

	/**
	 * Get IP Info
	 * @from http://stackoverflow.com/questions/12553160/getting-visitors-country-from-their-ip
	 *
	 * @param null $ip
	 * @param string $purpose
	 * @param bool|true $deep_detect
	 *
	 * @return array|null|string
	 */
	static function ip_info($ip = NULL, $purpose = "location", $deep_detect = TRUE) {
		$output = NULL;
		if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
			$ip = $_SERVER["REMOTE_ADDR"];
			if ($deep_detect) {
				if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
					$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
				if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
					$ip = $_SERVER['HTTP_CLIENT_IP'];
			}
		}
		$purpose    = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));
		$support    = array("country", "countrycode", "state", "region", "city", "location", "address");
		$continents = array(
			"AF" => "Africa",
			"AN" => "Antarctica",
			"AS" => "Asia",
			"EU" => "Europe",
			"OC" => "Australia (Oceania)",
			"NA" => "North America",
			"SA" => "South America"
		);
		if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
			$ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
			if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
				switch ($purpose) {
					case "location":
						$output = array(
							"city"           => @$ipdat->geoplugin_city,
							"state"          => @$ipdat->geoplugin_regionName,
							"country"        => @$ipdat->geoplugin_countryName,
							"country_code"   => @$ipdat->geoplugin_countryCode,
							"continent"      => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
							"continent_code" => @$ipdat->geoplugin_continentCode
						);
						break;
					case "address":
						$address = array($ipdat->geoplugin_countryName);
						if (@strlen($ipdat->geoplugin_regionName) >= 1)
							$address[] = $ipdat->geoplugin_regionName;
						if (@strlen($ipdat->geoplugin_city) >= 1)
							$address[] = $ipdat->geoplugin_city;
						$output = implode(", ", array_reverse($address));
						break;
					case "city":
						$output = @$ipdat->geoplugin_city;
						break;
					case "state":
						$output = @$ipdat->geoplugin_regionName;
						break;
					case "region":
						$output = @$ipdat->geoplugin_regionName;
						break;
					case "country":
						$output = @$ipdat->geoplugin_countryName;
						break;
					case "countrycode":
						$output = @$ipdat->geoplugin_countryCode;
						break;
				}
			}
		}
		return $output;
	}

	/**
	 * Convert WP_Term to old-fashion stdClass
	 *
	 * @param $instance
	 *
	 * @return \stdClass
	 */
	static function wpTerm_to_stdClass(\WP_Term $instance){
		$std = new \stdClass();
		$std->term_id = $instance->term_id;
		$std->name = $instance->name;
		$std->slug = $instance->slug;
		$std->taxonomy = $instance->taxonomy;
		$std->term_group = $instance->term_group;
		$std->term_taxonomy_id = $instance->term_taxonomy_id;
		$std->description = $instance->description;
		$std->parent = $instance->parent;
		$std->count = $instance->count;
		$std->filter = $instance->filter;
		return $std;
	}

	/**
	 * Get a list of term in hierarchical order, with parents before their children.
	 * The functions automatically completes the list with che missing parents (they will be labeled with "not_assigned = true" property)..
	 *
	 * @param int $post_id the $post_id param for wp_get_post_terms()
	 * @param string $taxonomy the $taxonomy param for wp_get_post_terms()
	 * @param array $args the $args param for wp_get_post_terms()
	 * @param boolean $flatten TRUE to flatten the hierarchical array down to one level. Children will be inserted after their parents;
	 *                          FALSE to retrieve a multidimensional array in which the first level is composed by top-level parents. Children will be appended into "children" property of each parent term.
	 *
	 * @param bool|false $convert_to_wp_term is true, the resulting list flatted list will be converted into WP_Term list
	 *
	 * @return array
	 */
	static function get_post_terms_hierarchical($post_id, $taxonomy, $args = [], $flatten = true, $convert_to_wp_term = false){
		static $cache;

		if(isset($cache[$taxonomy][$post_id]) && is_array($cache[$taxonomy][$post_id])) return $cache[$taxonomy][$post_id];

		$args = wp_parse_args($args,[
			'orderby' => 'parent'
		]);
		$args['orderby'] = 'parent'; //we need to force this
		$terms = wp_get_post_terms( $post_id, $taxonomy, $args);

		/**
		 * Convert WP_Term to old-fashion stdClass
		 *
		 * @param $instance
		 *
		 * @return \stdClass
		 */
		$WPTermToStdClass = function($instance) {
			$std = new \stdClass();
			$std->term_id = $instance->term_id;
			$std->name = $instance->name;
			$std->slug = $instance->slug;
			$std->term_group = $instance->term_group;
			$std->term_taxonomy_id = $instance->term_taxonomy_id;
			$std->description = $instance->description;
			$std->parent = $instance->parent;
			$std->count = $instance->count;
			$std->filter = $instance->filter;
			return $std;
		};

		/**
		 * Insert a mixed at specified position into input $array
		 *
		 * @param array $input
		 * @param $position
		 * @param $insertion
		 *
		 * @return array
		 */
		$array_insert = function(Array $input,$position,$insertion){
			$insertion = array($insertion);
			$first_array = array_splice ($input, 0, $position);
			$output = array_merge ($first_array, $insertion, $input);
			return $output;
		};

		/**
		 * Insert $insertion after the element with $term->id == $insert_at_term_id of array $input
		 * @param array $input
		 * @param int   $insert_at_term_id
		 * @param array $insertion
		 *
		 * @return array|bool
		 */
		$children_insert = function(Array $input,$insert_at_term_id,$insertion) use(&$children_insert,$WPTermToStdClass){
			$output = $input;

			foreach($output as $k => $t){
				if($t instanceof \WP_Term){
					$output[$k] = $WPTermToStdClass($t);
				}
			}

			foreach($input as $k => $v){
				if($v->term_id == $insert_at_term_id){ //We found the parent
					if(!isset($output[$k]->childeren) || !is_integer(array_search($insertion,$output[$k]->children))){
						$output[$k]->children[] = $insertion;
						return $output;
					}
				}elseif(isset($v->children) && count($v->children) >= 1){ //Search in parent children
					$new_children = $children_insert($v->children,$insert_at_term_id,$insertion);
					if(is_array($new_children)){
						$output[$k]->children = $new_children;
						return $output;
					}
				}
			}
			return false; //We haven't found any point of insertion
		};

		/**
		 * Complete the terms list with missing parents. Missing parents will be labeled with "not_assigned = true"
		 *
		 * @param $terms
		 *
		 * @return mixed
		 * @internal param $p
		 * @internal param $t
		 *
		 */
		$complete_missing_terms = function($terms) use($taxonomy){
			/**
			 * Add the parent pf $child into the $terms_list (if not present)
			 * @param $child
			 * @param $terms_list
			 *
			 * @return array
			 */
			$add_parent = function($child,$terms_list) use(&$add_parent,$taxonomy){
				$parent = get_term($child->parent,$taxonomy);
				$terms_list_as_array = json_decode(json_encode($terms_list),true);
				$found = self::associative_array_search($terms_list_as_array,"term_id",$parent->term_id);
				if(empty($found)){
					$parent->not_assigned = true; //Set a flag to tell that this parent is added programmatically and not by the user
					$terms_list[] = $parent;
				}
				if($parent->parent != 0){
					return $add_parent($parent,$terms_list);
				}else{
					return $terms_list;
				}
			};
			$new_term_list = $terms;
			foreach($terms as $t){
				if($t->parent != 0){
					$new_term_list = $add_parent($t,$new_term_list);
				}
			}
			return $new_term_list;
		};

		/**
		 * Build term hierarchy
		 * @param array $cats the terms to reorder
		 *
		 * @return array
		 */
		$build_hierarchy = function(Array $cats) use ($array_insert, $children_insert){
			$cats_count = count($cats); //meow! How many terms have we?
			$result = [];

			if($cats_count < 1){
				return $result;
			}
			elseif($cats_count == 1){
				return $cats;
			}

			//Populate all the parent
			foreach ($cats as $i => $cat) {
				if($cat->parent == 0){
					$result[] = $cat;
					unset($cats[$i]); //remove the parent from the list
				}
			}

			$inserted_cats = count($result); //Count the items inserted at this point
			$cats = array_values($cats); //resort the array

			if($inserted_cats == 0){
				return []; //Here we return if no parents are present within the terms
			}

			//Populate with children
			while(count($cats) > 0){ //Go on until we reached have some terms to order
				foreach ($cats as $i => $cat) {
					$parent_term_id = $cat->parent;
					$r = $children_insert($result,$parent_term_id,$cat);
					if(is_array($r)){ //We found a valid parent, and $r is the new array with $cat appended into parent
						$result = $r;
						unset($cats[$i]);
						$cats = array_values($cats); //resort the array
						break; //and break!
					}
				}
			}

			return $result;
		};

		$flatten_terms_hierarchy = function($term_hierarchy) use($convert_to_wp_term){
			$output_terms = [];
			$flat = function($term_hierarchy) use (&$output_terms,&$flat,$convert_to_wp_term){
				foreach($term_hierarchy as $k => $t){
					$output_terms[] = $convert_to_wp_term ? \WP_Term::get_instance($t->term_id,$t->taxonomy) : $t;
					if(isset($t->children) && $t->children >= 1){
						$flat($t->children);
					}
				}
			};
			$flat($term_hierarchy);

			foreach($output_terms as $k=>$v){
				if(isset($v->children)){
					unset($output_terms[$k]->children);
				}
			}

			return $output_terms;
		};

		if(!is_array($terms) || empty($terms)) return [];

		foreach($terms as $k => $t){
			if($t instanceof \WP_Term){
				$terms[$k] = $WPTermToStdClass($t);
			}
		}

		$terms = $complete_missing_terms($terms);
		$h = $build_hierarchy($terms);

		$sortedTerms = $flatten ? $flatten_terms_hierarchy($h) : $h; //Extract the children

		$cache[$taxonomy][$post_id] = $sortedTerms;

		return $sortedTerms;
	}

	/**
	 * Guess what :)
	 *
	 * @param $needle
	 * @param $haystack
	 * @return bool|int|string
	 */
	static function recursive_array_search($needle,$haystack) {
		foreach($haystack as $key=>$value) {
			$current_key=$key;
			if($needle===$value OR (is_array($value) && recursive_array_search($needle,$value) !== false)) {
				return $current_key;
			}
		}
		return false;
	}

	/**
	 * Search $array for the $key=>$value pair.
	 *
	 * @param array $array the target array
	 * @param mixed $key the key to find
	 * @param mixed $value the value to find into the $key
	 *
	 * @return array with the found pairs, or empty.
	 */
	static function associative_array_search($array,$key,$value){
		$search_r = function($array, $key, $value, &$results, $subarray_key = null) use(&$search_r){
			if (!is_array($array)) {
				return;
			}

			if (isset($array[$key]) && $array[$key] == $value) {
				if(isset($subarray_key))
					$results[$subarray_key] = $array;
				else
					$results[] = $array;
			}

			foreach ($array as $k => $subarray) {
				$search_r($subarray, $key, $value, $results, $k);
			}
		};
		$results = array();
		$search_r($array, $key, $value, $results);
		return $results;
	}

	/**
	 * Insert an $element after $key in $array (associative)
	 *
	 * @param array $element
	 * @param string $key
	 * @param array $array
	 *
	 * @return array
	 */
	static function associative_array_add_element_after(array $element,$key,array $array){
		$i = 1;
		foreach($array as $k => $v){
			if($k == $key){
				break;
			}
			$i++;
		}
		$head = array_slice($array,0,$i,true);
		$tail = array_slice($array,$i);
		$result = array_merge($head,$element);
		$result = array_merge($result,$tail);
		return $result;
	}

	/**
	 * Get the next and prev element in an array relative to the current
	 *
	 * @param array $arr of items
	 * @param string $key of current item
	 * @return array
	 */
	static function array_neighbor($arr, $key){
		$keys = array_keys($arr);
		$keyIndexes = array_flip($keys);

		$return = array();
		if (isset($keys[$keyIndexes[$key]-1])) {
			$return[] = $keys[$keyIndexes[$key]-1];
		}
		else {
			$return[] = $keys[sizeof($keys)-1];
		}

		if (isset($keys[$keyIndexes[$key]+1])) {
			$return[] = $keys[$keyIndexes[$key]+1];
		}
		else {
			$return[] = $keys[0];
		}

		return $return;
	}

	/**
	 * A simple replacer for ciryllic characters. Provided by Infinita.
	 *
	 * @param $string
	 *
	 * @return mixed|string
	 */
	static function replace_cyrillic($string){
		//translitteration of cyrylic
		$cyrylicFrom = array('А', 'Б', 'В', 'Г', 'Д', 'Е' , 'Ё' , 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х' , 'Ц', 'Ч'  , 'Ш' , 'Щ'    , 'Ъ', 'Ы', 'Ь', 'Э', 'Ю' , 'Я' , 'а', 'б', 'в', 'г', 'д', 'е' , 'ё' , 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х' , 'ц', 'ч'  , 'ш' , 'щ'    , 'ъ', 'ы', 'ь', 'э', 'ю' , 'я');
		$cyrylicTo   = array('A', 'B', 'W', 'G', 'D', 'Ie', 'Io', 'Z', 'Z', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'Ch', 'C', 'Tch', 'Sh', 'Shtch', '' , 'Y', '' , 'E', 'Iu', 'Ia', 'a', 'b', 'w', 'g', 'd', 'ie', 'io', 'z', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'ch', 'c', 'tch', 'sh', 'shtch', '' , 'y', '' , 'e', 'iu', 'ia');

		//transcription of characters with accent and other signs
		$from = array("Á", "À", "Â", "Ä", "Ă", "Ā", "Ã", "Å", "Ą", "Æ" , "Ć", "Ċ", "Ĉ", "Č", "Ç", "Ď", "Đ", "Ð", "É", "È", "Ė", "Ê", "Ë", "Ě", "Ē", "Ę", "Ə", "Ġ", "Ĝ", "Ğ", "Ģ", "á", "à", "â", "ä", "ă", "ā", "ã", "å", "ą", "æ" , "ć", "ċ", "ĉ", "č", "ç", "ď", "đ", "ð", "é", "è", "ė", "ê", "ë", "ě", "ē", "ę", "ə", "ġ", "ĝ", "ğ", "ģ", "Ĥ", "Ħ", "I", "Í", "Ì", "İ", "Î", "Ï", "Ī", "Į", "Ĳ" , "Ĵ", "Ķ", "Ļ", "Ł", "Ń", "Ň", "Ñ", "Ņ", "Ó", "Ò", "Ô", "Ö", "Õ", "Ő", "Ø", "Ơ", "Œ" , "ĥ", "ħ", "ı", "í", "ì", "i", "î", "ï", "ī", "į", "ĳ" , "ĵ", "ķ", "ļ", "ł", "ń", "ň", "ñ", "ņ", "ó", "ò", "ô", "ö", "õ", "ő", "ø", "ơ", "œ", "Ŕ", "Ř", "Ś", "Ŝ", "Š", "Ş", "Ť", "Ţ", "Þ", "Ú", "Ù", "Û", "Ü", "Ŭ", "Ū", "Ů", "Ų", "Ű", "Ư", "Ŵ", "Ý", "Ŷ", "Ÿ", "Ź", "Ż", "Ž", "ŕ", "ř", "ś", "ŝ", "š", "ş", "ß", "ť", "ţ", "þ", "ú", "ù", "û", "ü", "ŭ", "ū", "ů", "ų", "ű", "ư", "ŵ", "ý", "ŷ", "ÿ", "ź", "ż", "ž");
		$to   = array("A", "A", "A", "A", "A", "A", "A", "A", "A", "AE", "C", "C", "C", "C", "C", "D", "D", "D", "E", "E", "E", "E", "E", "E", "E", "E", "G", "G", "G", "G", "G", "a", "a", "a", "a", "a", "a", "a", "a", "a", "ae", "c", "c", "c", "c", "c", "d", "d", "d", "e", "e", "e", "e", "e", "e", "e", "e", "g", "g", "g", "g", "g", "H", "H", "I", "I", "I", "I", "I", "I", "I", "I", "IJ", "J", "K", "L", "L", "N", "N", "N", "N", "O", "O", "O", "O", "O", "O", "O", "O", "CE", "h", "h", "i", "i", "i", "i", "i", "i", "i", "i", "ij", "j", "k", "l", "l", "n", "n", "n", "n", "o", "o", "o", "o", "o", "o", "o", "o", "o", "R", "R", "S", "S", "S", "S", "T", "T", "T", "U", "U", "U", "U", "U", "U", "U", "U", "U", "U", "W", "Y", "Y", "Y", "Z", "Z", "Z", "r", "r", "s", "s", "s", "s", "B", "t", "t", "b", "u", "u", "u", "u", "u", "u", "u", "u", "u", "u", "w", "y", "y", "y", "z", "z", "z");

		$from = array_merge($from, $cyrylicFrom);
		$to   = array_merge($to  , $cyrylicTo);

		//execute replace
		$string = str_replace($from, $to, $string);

		//convert remaining characters to lower case
		$string = strtolower($string);

		//force-clean every other character, replacing with an hyphen
		$string = preg_replace("/[^a-z0-9]/i", '-', $string);

		//replaces repeated hyphens with one hyphen only
		$string = preg_replace("/[-]{2,}/", '-', $string);

		//final trim
		$string = trim($string, '-');

		return $string;
	}

	/**
	 * Get the current url via vanilla function
	 *
	 * @return string
	 */
	static function get_current_url() {
		$pageURL = 'http';
		if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}

	/**
	 * Get the current url using wp functions
	 *
	 * @return string
	 */
	static function wp_get_current_url(){
		global $wp;
		$current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
		return $current_url;
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
	 * Convert an url to the absolute path of that url in wordpress
	 *
	 * @param $url
	 * @return mixed
	 */
	static function url_to_path($url){
		$blogurl = get_bloginfo("url");
		$blogurl = preg_replace("(https?://)", "", $blogurl );
		//$result = preg_match("/^https?:\/\/$blogurl\/([[:space:]a-zA-Z0-9\/_.-]+)/", $url, $matches);
		$result = preg_replace("|^https?://$blogurl|", ABSPATH, $url);
		//$blogpath = ABSPATH;

		//$filepath = $blogpath."/".$matches[1];
		//return $filepath;
		return $result;
	}

	/**
	 * Convert a path to the uri relative to wordpress installation
	 *
	 * @param $path
	 * @return mixed
	 */
	static function path_to_url($path){
		$blogurl = trailingslashit(get_bloginfo("url"));
		$blogpath = ABSPATH;
		$result = preg_replace("|^$blogpath|", $blogurl, $path);
		return $result;
	}

	/**
	 * Check if a string is a JSON array
	 *
	 * @param $string
	 *
	 * @return bool
	 */
	static function isJson($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}

	/**
	 * Returns TRUE if $url is https
	 *
	 * @param $url
	 *
	 * @return bool
	 */
	static function is_ssl($url){
		return substr( $url, 0, 5 ) === 'https';
	}

	/**
	 * Completely erase a directory
	 * @param string $dir the directory path
	 */
	static function deltree($dir){
		if(!preg_match("|[A-Za-z0-9]+/$|",$dir)) $dir .= "/"; // ensure $dir ends with a slash

		$files = glob( $dir . '*', GLOB_MARK );
		foreach($files as $file){
			if( substr( $file, -1 ) == '/' )
				deltree( $file );
			else
				unlink( $file );
		}
		if(is_dir($dir)){
			rmdir( $dir );
		}
	}

	/**
	 * Get Remote File Size
	 *
	 * @param string $url as remote file URL
	 * @return int as file size in byte
	 */
	static function remote_file_size($url){
		# Get all header information
		$data = get_headers($url, true);
		# Look up validity
		if (isset($data['Content-Length'])){
			# Return file size
			return (int) $data['Content-Length'];
		}
		return false;
	}

	/**
	 * Converts bytes into human readable file size.
	 *
	 * @param string $bytes
	 * @param int $precision
	 * @return string human readable file size (2,87 МB)
	 */
	static function formatBytes($bytes, $precision = 2) {
		$units = array('B', 'KB', 'MB', 'GB', 'TB');

		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);

		// Uncomment one of the following alternatives
		$bytes /= pow(1024, $pow);
		// $bytes /= (1 << (10 * $pow));

		return round($bytes, $precision) . ' ' . $units[$pow];
	}

	/**
	 * List all files in a folder
	 *
	 * @param $dir
	 * @param string $extension
	 * @return array
	 */
	static function listFolderFiles($dir,$extension = "php"){
		$files_in_root = glob($dir."/*.{$extension}");
		$files = glob($dir."/*/*.{$extension}");

		if(!$files_in_root) $files_in_root = array();
		if(!$files) $files = array();

		return array_merge($files_in_root,$files);
	}

	/**
	 * Create a directory
	 *
	 * @param $path
	 * @param int $chmod
	 * @return bool
	 * @throws \Exception
	 */
	static function mkdir($path,$chmod = 0777){
		if(!is_dir($path)){
			if(!mkdir($path,$chmod)){
				throw new \Exception(_("Unable to create folder {$path}"));
			}else{
				return true;
			}
		}
		return false;
	}

	/**
	 * Count a digit of an int
	 *
	 * @param $number
	 * @return int
	 */
	static function count_digit($number){
		$digit = 0;
		do
		{
			$number /= 10;      //$number = $number / 10;
			$number = intval($number);
			$digit++;
		}while($number!=0);
		return $digit;
	}

	/**
	 * Returns the offset from the origin timezone to the remote timezone, in seconds.
	 * @param $remote_tz;
	 * @param $origin_tz; If null the servers current timezone is used as the origin.
	 * @return int;
	 */
	static function get_timezone_offset($remote_tz, $origin_tz = null) {
		if($origin_tz === null) {
			if(!is_string($origin_tz = date_default_timezone_get())) {
				return false; // A UTC timestamp was returned -- bail out!
			}
		}
		$origin_dtz = new \DateTimeZone($origin_tz);
		$remote_dtz = new \DateTimeZone($remote_tz);
		$origin_dt = new \DateTime("now", $origin_dtz);
		$remote_dt = new \DateTime("now", $remote_dtz);
		$offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
		return $offset;
	}

	/**
	 * Ensure that the string does not have the trailing slash
	 *
	 * @param $string
	 *
	 * @return string
	 */
	static function maybe_strip_trailing_slash($string){
		return rtrim($string,"/");
	}

	/**
	 * Simply var_dump enclosed in <pre> :)
	 *
	 * @param $var
	 */
	static function predump($var){
		echo "<pre>";
		var_dump($var);
		echo "</pre>";
	}
}