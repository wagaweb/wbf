<?php
/*
 * This file is part of WBF Framework: https://github.com/wagaweb/wbf
 *
 * @author WAGA Team <dev@waga.it>
 */

namespace WBF\components\utils;

use Mockery\CountValidator\Exception;
use WBF\components\notices\Notice_Manager;

/**
 * Class Utilities
 *
 * @note Yes, we know traits, but, you know, WordPress PHP compatibility...
 *
 * @package WBF\components\utils
 */
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
		return WordPress::get_sanitized_blogname();
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
		return Paths::get_template_part($slug,$name,$vars);
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
		return Paths::locate_file($file,$load,$require_once);
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
		return Paths::locate_template($templates,$load,$require_once,$additional_search_paths);
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
		return Paths::locate_template_uri($template_names);
	}

	/**
	 * Get the current page type. Can be "default_home" | "static_home" | "blog_page" | "common"
	 *
	 * @return string
	 */
	static function get_current_page_type(){
		return Query::get_current_page_type();
	}

	/**
	 * Return TRUE when the default home page in displayed
	 *
	 * @return bool
	 */
	static function is_default_home(){
		return Query::is_default_home();
	}

	/**
	 * Return TRUE when the static home page in displayed
	 *
	 * @return bool
	 */
	static function is_static_home(){
		return Query::is_static_home();
	}

	/**
	 * Return TRUE when the user defined blog page in displayed
	 *
	 * @return bool
	 */
	static function is_blog_page(){
		return Query::is_blog_page();
	}

	/**
	 * Return TRUE when a common page page in displayed
	 *
	 * @return bool
	 */
	static function is_common_page(){
		return Query::is_common_page();
	}

	/**
	 * Recursively create directories
	 *
	 * @param $path
	 *
	 * @return bool
	 */
	static function mkpath($path) {
		return Paths::mkpath($path);
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
	 * @uses WBF\components\notices\Notice_Manager
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
			"cond_args" => null,
			"manual_display" => false
		]);

		$wbf_notice_manager->add_notice($id,$message,$level,$args['category'],$args['condition'],$args['cond_args'],$args['manual_display']);
	}

	/**
	 * Get a list of post types without the blacklisted ones
	 * @param array $blacklist
	 *
	 * @return array
	 */
	static function get_filtered_post_types($blacklist = array()){
		return Posts::get_filtered_post_types($blacklist);
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
		return Posts::recursive_get_posts($callback,$args,$include_meta);
	}

	/**
	 * Assure existence of $table_name.
	 *
	 * @param string $table_name (eventual prefix will be stripped out)
	 *
	 * @return bool
	 */
	static function table_exists($table_name){
		return DB::table_exists($table_name);
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
		return Terms::wpTerm_to_stdClass($instance);
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
		return Terms::get_post_terms_hierarchical($post_id,$taxonomy,$args,$flatten,$convert_to_wp_term);
	}

	/**
	 * Recursive version of array_diff
	 *
	 * @link http://stackoverflow.com/questions/3876435/recursive-array-diff
	 *
	 * @param array $arr1
	 * @param array $arr2
	 *
	 * @return array
	 */
	static function recursive_array_diff($arr1, $arr2) {
		return Arrays::recursive_array_diff($arr1,$arr2);
	}

	/**
	 * Recursive version of array_diff_assoc
	 *
	 * @link https://www.drupal.org/files/1850798-base-array_recurse-drupal-68.patch
	 *
	 * @param array $array1
	 * @param array $array2
	 *
	 * @return array
	 */
	static function recursive_array_diff_assoc($array1, $array2) {
		return Arrays::recursive_array_diff_assoc($array1,$array2);
	}

	/**
	 * Guess what :)
	 *
	 * @param $needle
	 * @param $haystack
	 * @return bool|int|string
	 */
	static function recursive_array_search($needle,$haystack) {
		return Arrays::recursive_array_search($needle,$haystack);
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
		return Arrays::associative_array_search($array,$key,$value);
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
		return Arrays::associative_array_add_element_after($element,$key,$array);
	}

	/**
	 * Get the next and prev element in an array relative to the current
	 *
	 * @param array $arr of items
	 * @param string $key of current item
	 * @return array
	 */
	static function array_neighbor($arr, $key){
		return Arrays::array_neighbor($arr,$key);
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
		return Paths::get_current_url();
	}

	/**
	 * Get the current url using wp functions
	 *
	 * @return string
	 */
	static function wp_get_current_url(){
		return Paths::wp_get_current_url();
	}

	/**
	 * Get the src of the $post_id thumbnail
	 *
	 * @param $post_id
	 * @param null $size
	 * @return mixed
	 */
	static function get_post_thumbnail_src($post_id,$size=null){
		return Posts::get_post_thumbnail_src($post_id,$size);
	}


	/**
	 * Convert full URL paths to path relative to wp-content.
	 *
	 * Removes the http or https protocols the domain and wp-content.
	 *
	 * @param string $link Full URL path.
	 * @return string path.
	 */
	static function wb_make_link_relative_to_wpcontent( $link ) {
		return preg_replace( '|^(https?:)?\/\/[^/]+(\/?wp-content)(\/?.*)|i', '$3', $link );
	}

	/**
	 * Convert an url to the absolute path of that url in wordpress
	 *
	 * @param $url
	 * @return mixed
	 */
	static function url_to_path($url){
		return Paths::url_to_path($url);
	}

	/**
	 * Convert a path to the uri relative to wordpress installation
	 *
	 * @param $path
	 * @return mixed
	 */
	static function path_to_url($path){
		return Paths::path_to_url($path);
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
		Paths::deltree($dir);
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
		return Paths::listFolderFiles($dir,$extension);
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
		return Paths::mkdir($path,$chmod);
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

	/**
	 * Secure dump. var_dump only in presence of an admin or when $_GET['wbf_debug'] is active.
	 */
	static function sdump($var,$format = true){
		if(current_user_can("manage_options") || isset($_GET['wbf_debug'])){
			if($format){
				self::predump($var);
			}else{
				var_dump($var);
			}
		}
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
		WordPress::add_tinymce_plugin($id,$params);
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
		return Posts::get_attachment_id_by_url($url);
	}

	/**
	 * This is a decent way of grabbing the dimensions of SVG files.
	 * Depends on http://php.net/manual/en/function.simplexml-load-file.php
	 * I believe this to be a reasonable dependency and should be common enough to
	 * not cause problems.
	 *
	 * @see https://github.com/grok/wordpress-plugin-scalable-vector-graphics/blob/master/scalable-vector-graphics.php
	 *
	 * @param $svg
	 *
	 * @return object
	 */
	static function get_svg_dimensions( $svg ) {
		$svg = simplexml_load_file( $svg );
		$attributes = $svg->attributes();
		$width = (string) $attributes->width;
		$height = (string) $attributes->height;
		return (object) array( 'width' => $width, 'height' => $height );
	}

}