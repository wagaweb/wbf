<?php

require_once "class-utilities.php";

if(!function_exists("wbf_get_sanitized_blogname")):
	/**
	 * Return a sanitized version of blog name
	 *
	 * @return string
	 */
	function wbf_get_sanitized_blogname(){
		return \WBF\includes\Utilities::get_sanitized_blogname();
	}
endif;

if(!function_exists('wbf_get_template_part')):
	/**
	 * WBF version of get_template_part
	 *
	 * @param $slug
	 * @param null $name
	 */
	function wbf_get_template_part($slug, $name = null){
		\WBF\includes\Utilities::get_template_part($slug,$name);
	}
endif;

if(!function_exists("wbf_locate_file")):
	/**
	 * Search for $file in WBF directory, plus template and stylesheet directories
	 *
	 * @param $file
	 * @param bool $load
	 * @param bool $require_once
	 * @return string
	 * @throws \Exception
	 */
	function wbf_locate_file($file, $load = false, $require_once = true){
		return \WBF\includes\Utilities::locate_file($file,$load,$require_once);
	}
endif;

if(!function_exists('wbf_locate_template')):
	/**
	 * Retrieve the template file from various set of sources
	 *
	 * @param array $templates an associative array that must contain at least "names" key. It can have the "sources" key, with a list of paths to explore.
	 * @param bool|false $load if TRUE it calls load_template()
	 * @param bool|true $require_once it $load is TRUE, it assigned as the second argument to load_template()
	 * @return string
	 */
	function wbf_locate_template($templates, $load = false, $require_once = true ) {
		return \WBF\includes\Utilities::locate_template($templates,$load,$require_once);
	}
endif;

if (!function_exists( 'wbf_locate_template_uri' )):
    /**
     * Retrieve the URI of the highest priority template file that exists.
     *
     * Searches in the stylesheet directory before the template directory so themes
     * which inherit from a parent theme can just override one file.
     *
     * @param string|array $template_names Template file(s) to search for, in order.
     * @return string The URI of the file if one is located.
     */
    function wbf_locate_template_uri($template_names){
        return \WBF\includes\Utilities::locate_template_uri($template_names);
    }
endif;

if (!function_exists( "wbf_get_filtered_post_types" )):
	/**
	 * Get a list of post types without the blacklisted ones
	 * @param array $blacklist
	 *
	 * @return array
	 */
	function wbf_get_filtered_post_types($blacklist = array()){
		return \WBF\includes\Utilities::get_filtered_post_types($blacklist);
	}
endif;

if (!function_exists( "wbf_get_posts" )) :
	/**
	 * Get posts while preserving memory
	 *
	 * @param callable $callback a function that will be called for each post. You can use it to additionally filter the posts. If it returns true, the post will be added to output array.
	 * @param array    $args normal arguments for WP_Query
	 * @param bool     $include_meta the post meta will be included in the post object (default to FALSE)
	 *
	 * @return array of posts
	 */
	function wbf_get_posts(\closure $callback = null, $args = array(), $include_meta = false){
		return \WBF\includes\Utilities::recursive_get_posts($callback,$args,$include_meta);
	}
endif;

if (!function_exists( "wbf_admin_show_message" )) :
    function wbf_admin_show_message($m, $type) {
	    \WBF\includes\Utilities::admin_show_message($m,$type);
    }
endif;

if (!function_exists("wbf_add_admin_notice")) :
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
	function wbf_add_admin_notice($id,$message,$level,$args = []){
		\WBF\includes\Utilities::add_admin_notice($id,$message,$level,$args);
	}
endif;

/***************************************************************
 * MOBILE DETECT FUNCTIONS
 ***************************************************************/

if (!function_exists("wb_is_mobile")):
    function wb_is_mobile()
    {
        $md = WBF::getInstance()->get_mobile_detect();
        return ($md->isMobile());
    }
endif;

if (!function_exists("wb_is_tablet")):
    function wb_is_tablet()
    {
        $md = WBF::getInstance()->get_mobile_detect();
        return ($md->isTablet());
    }
endif;

if (!function_exists("wb_is_ios")):
    function wb_is_ios()
    {
        $md = WBF::getInstance()->get_mobile_detect();
        return ($md->isiOS());
    }
endif;

if (!function_exists("wb_is_android")):
    function wb_is_android()
    {
        $md = WBF::getInstance()->get_mobile_detect();
        return ($md->isAndroidOS());
    }
endif;

if (!function_exists("wb_is_windows_mobile")):
    function wb_is_windows_mobile()
    {
        $md = WBF::getInstance()->get_mobile_detect();
        return ($md->is('WindowsMobileOS') || $md->is('WindowsPhoneOS'));
    }
endif;

if (!function_exists("wb_is_iphone")):
    function wb_is_iphone()
    {
        $md = WBF::getInstance()->get_mobile_detect();
        return ($md->isIphone());
    }
endif;

if (!function_exists("wb_is_ipad")):
    function wb_is_ipad()
    {
        $md = WBF::getInstance()->get_mobile_detect();
        return ($md->isIpad());
    }
endif;

if (!function_exists("wb_is_samsung")):
    function wb_is_samsung()
    {
        $md = WBF::getInstance()->get_mobile_detect();
        return ($md->is('Samsung'));
    }
endif;

if (!function_exists("wb_is_samsung_tablet")):
    function wb_is_samsung_tablet()
    {
        $md = WBF::getInstance()->get_mobile_detect();
        return ($md->is('SamsungTablet'));
    }
endif;

if (!function_exists("wb_is_kindle")):
    function wb_is_kindle()
    {
        $md = WBF::getInstance()->get_mobile_detect();
        return ($md->is('Kindle'));
    }
endif;

if (!function_exists("wb_android_version")):
    function wb_android_version()
    {
        $md = WBF::getInstance()->get_mobile_detect();
        return $md->version('Android');
    }
endif;

if (!function_exists("wb_iphone_version")):
    function wb_iphone_version()
    {
        $md = WBF::getInstance()->get_mobile_detect();
        return $md->version('iPhone');
    }
endif;

if (!function_exists("wb_ipad_version")):
    function wb_ipad_version()
    {
        $md = WBF::getInstance()->get_mobile_detect();
        return $md->version('iPad');
    }
endif;

/**************************************************************
 * OTHERS
 **************************************************************/

if ( !function_exists("get_post_thumbnail_src") ) :
	/**
	 * Get the src of the $post_id thumbnail
	 *
	 * @param $post_id
	 * @param null $size
	 * @return mixed
	 */
	function get_post_thumbnail_src($post_id,$size=null){
		return \WBF\includes\Utilities::get_post_thumbnail_src($post_id,$size);
	}
endif;

if ( !function_exists("get_current_url") ) :
	/**
	 * Get the current url via vanilla function
	 *
	 * @return string
	 */
	function get_current_url() {
		return \WBF\includes\Utilities::get_current_url();
	}
endif;

if ( !function_exists("get_wp_current_url") ) :
	/**
	 * Get the current url using wp functions
	 *
	 * @return string
	 */
	function wp_get_current_url(){
		return \WBF\includes\Utilities::wp_get_current_url();
	}
endif;

if ( !function_exists("array_neighbor") ) :
	/**
	 * Get the next and prev element in an array relative to the current
	 * @param array $arr of items
	 * @param string $key of current item
	 * @return array
	 */
	function array_neighbor($arr, $key){
		return \WBF\includes\Utilities::array_neighbor($arr,$key);
	}
endif;

if ( !function_exists("recursive_array_search") ) :
	/**
	 * Guess what :)
	 *
	 * @param $needle
	 * @param $haystack
	 * @return bool|int|string
	 */
	function recursive_array_search($needle,$haystack) {
		return \WBF\includes\Utilities::recursive_array_search($needle,$haystack);
	}
endif;

if ( !function_exists("remote_file_size") ) :
	/**
	 * Get Remote File Size
	 *
	 * @param string $url as remote file URL
	 * @return int as file size in byte
	 */
	function remote_file_size($url){
		return \WBF\includes\Utilities::remote_file_size($url);
	}
endif;

if ( !function_exists("formatBytes") ) :
	/**
	 * Converts bytes into human readable file size.
	 *
	 * @param string $bytes
	 * @param int $precision
	 * @return string human readable file size (2,87 МB)
	 */
	function formatBytes($bytes, $precision = 2) {
		return \WBF\includes\Utilities::formatBytes($bytes,$precision);
	}
endif;

if ( !function_exists("listFolderFiles") ) :
	/**
	 * List all files in a folder
	 *
	 * @param $dir
	 * @param string $extension
	 * @return array
	 */
	function listFolderFiles($dir,$extension = "php"){
		return \WBF\includes\Utilities::listFolderFiles($dir,$extension);
	}
endif;

if ( !function_exists("createdir") ) :
	/**
	 * Create a directory
	 *
	 * @param $path
	 * @param int $chmod
	 * @return bool
	 * @throws Exception
	 */
	function createdir($path,$chmod = 0777){
		return \WBF\includes\Utilities::mkdir($path,$chmod);
	}
endif;

if ( !function_exists("deltree") ) :
	/**
	 * Completely erase a directory
	 * @param string $dir the directory path
	 */
	function deltree($dir){
		\WBF\includes\Utilities::deltree($dir);
	}
endif;

if ( !function_exists("url_to_path") ) :
	/**
	 * Convert an url to the absolute path of that url in wordpress
	 *
	 * @param $url
	 * @return mixed
	 */
	function url_to_path($url){
		return \WBF\includes\Utilities::url_to_path($url);
	}
endif;

if ( !function_exists("path_to_url") ) :
	/**
	 * Convert a path to the uri relative to wordpress installation
	 *
	 * @param $path
	 * @return mixed
	 */
	function path_to_url($path){
		return \WBF\includes\Utilities::path_to_url($path);
	}
endif;

if ( !function_exists("count_digit") ) :
	/**
	 * Count a digit of an int
	 *
	 * @param $number
	 * @return int
	 */
	function count_digit($number){
		return \WBF\includes\Utilities::count_digit($number);
	}
endif;

if ( !function_exists("get_timezone_offset") ) :
	/**
	 * Returns the offset from the origin timezone to the remote timezone, in seconds.
	 * @param $remote_tz;
	 * @param $origin_tz; If null the servers current timezone is used as the origin.
	 * @return int;
	 */
	function get_timezone_offset($remote_tz, $origin_tz = null) {
		return \WBF\includes\Utilities::get_timezone_offset($remote_tz,$origin_tz);
	}
endif;