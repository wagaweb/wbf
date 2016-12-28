<?php
namespace WBF\components\utils;

class Paths {
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
	 * @param array $templates an associative array that must contain at least "names" key. It can have the "sources" key, with a list of path to files.
	 * @param bool|false $load if TRUE it calls load_template()
	 * @param bool|true $require_once it $load is TRUE, it assigned as the second argument to load_template()
	 * @param array $additional_search_paths
	 *
	 * @return string
	 */
	static function locate_template($templates, $load = false, $require_once = true, $additional_search_paths = [] ) {
		$located = '';
		$template_names = $templates['names'];
		$template_sources = isset($templates['sources']) ? $templates['sources'] : [];
		if(empty($additional_search_paths) || !is_array($additional_search_paths)){
			$additional_search_paths = apply_filters("wbf/locate_template/search_paths", []);
		}

		//Search for templates
		foreach( (array) $template_names as $template_name){
			if(!$template_name){
				continue;
			}

			$child_directory = get_stylesheet_directory();
			$parent_directory = get_template_directory();

			$search_locations = [
				$child_directory . '/',
				$child_directory."/templates",
				$child_directory."/templates/parts/",
				$parent_directory . '/',
				$parent_directory . '/templates',
				$parent_directory . '/templates/parts/'
			];

			$search_locations = array_merge($search_locations,$additional_search_paths);

			$search_locations = array_unique($search_locations);

			foreach($search_locations as $loc){
				$found = false;
				$locs = [
					rtrim($loc,"/") . '/'.ltrim($template_name,"/"),
					rtrim($loc,"/") . '/'.ltrim(basename($template_name),"/")
				];
				foreach ($locs as $path){
					if(file_exists($path)){
						$located = $path;
						$found = true;
						break;
					}
				}
				if($found){
					break;
				}
			}

			if(!empty($located)){
				break;
			}
		}

		//Search for templates into sources (complete file paths)
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

		if ( $load && $located != '' ){
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
	 * WBF version of get_template_part. In addition to the WP own method, it uses WBF locate_template that looks into plugins dir.
	 * If want to use your plugin templates parts you have to use this function.
	 *
	 * @param $slug
	 * @param null $name
	 * @param array $vars
	 *
	 * @return string
	 */
	static function get_template_part($slug, $name = null, $vars=[]){
		do_action( "get_template_part_{$slug}", $slug, $name );

		$templates = apply_filters("wbf/get_template_part/path:{$slug}",array(),array($slug,$name)); //@deprecated from WBF ^0.11.0
		$name = (string) $name;
		if ( '' !== $name )
			$templates['names'][] = "{$slug}-{$name}.php";

		$templates['names'][] = "{$slug}.php";

		// take some vars and passes them in the $post object (e.g. shortcode vars can be used in parts)
		if(is_array($vars) && !empty($vars)){
			global $post;
			$post->wbf_template_vars = $vars;
		}
		return self::locate_template($templates, true, false);
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
}