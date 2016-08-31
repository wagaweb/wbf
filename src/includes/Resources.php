<?php

namespace WBF\includes;

use WBF\components\utils\Utilities;

class Resources{

	/**
	 * @var Resources
	 */
	private static $instance;
	/**
	 * @var bool|string
	 */
	private $wbf_path = false;
	/**
	 * @var bool|string
	 */
	private $wbf_url = false;
	/**
	 * The WBF working directory path
	 * @var bool|string
	 */
	private $wbf_wd = false;

	/**
	 * @return Resources
	 */
	public static function getInstance(){
		if (null === static::$instance) {
			static::$instance = new static();
		}
		return static::$instance;
	}
	
	protected function __construct(){
		$path = get_option("wbf_path");
		$url = $url = get_option("wbf_url");
		if(defined("WBF_DIRECTORY")){
			$path = rtrim(WBF_DIRECTORY,"/")."/";
			$this->wbf_path = $path;
		}elseif($path && is_string($path) && !empty($path)){
			$path = rtrim($path,"/")."/";
			$this->wbf_path = $path;
		}
		if(defined("WBF_URL")){
			$url = rtrim(WBF_URL,"/")."/";
			$this->wbf_url = $url;
		}elseif($url && is_string($url) && !empty($url)){
			$url = rtrim($url,"/")."/";
			$this->wbf_url = $url;
		}
	}

	/**
	 * Returns WBF url or FALSE
	 *
	 * @return bool|string
	 */
	public function get_url(){
		return $this->wbf_url;
	}

	/**
	 * Returns WBF path or FALSE
	 *
	 * @return bool|string
	 */
	public function get_path(){
		return $this->wbf_path;
	}

	/**
	 * Gets WBF admin assets uri
	 *
	 * @return bool|string
	 */
	public function get_admin_assets_uri(){
		return $this->get_assets_uri(true);
	}

	/**
	 * Gets WBF assets uri
	 * @param bool $admin_assets_flag
	 *
	 * @return bool|string
	 */
	public function get_assets_uri($admin_assets_flag = false){
		if($admin_assets_flag){
			return $this->prefix_url("admin");
		}else{
			return $this->prefix_url("public");
		}
	}

	/**
	 * Prefix $to with the WBF URL
	 * @param $to
	 *
	 * @return bool|string
	 */
	public function prefix_url($to){
		$url = trim($this->get_url());
		$to = trim($to);
		if($url){
			return rtrim($url,"/")."/".ltrim($to,"/");
		}else{
			return false;
		}
	}

	/**
	 * Prefix $to with the WBF PATH
	 * @param $to
	 *
	 * @return bool|string
	 */
	public function prefix_path($to){
		$path = trim($this->get_path());
		$to = trim($to);
		if($path){
			return rtrim($path,"/")."/".ltrim($to,"/");
		}else{
			return false;
		}
	}

	/**
	 * Returns WBF Theme dir
	 *
	 * @return bool|string
	 */
	/*public function get_theme_dir(){
		if(defined("WBF_THEME_DIRECTORY")){
			return rtrim(WBF_THEME_DIRECTORY,"/");
		}
		return false;
	}*/

	/**
	 * Tries to create the WBF working directory
	 */
	function maybe_add_work_directory(){
		$theme = wp_get_theme();
		if(defined("WBF_WORK_DIRECTORY_NAME")){
			$path = WBF_WORK_DIRECTORY."/".$theme->get_stylesheet();
			if(!is_dir(WBF_WORK_DIRECTORY)){ //We do not have the working directory
				Utilities::mkpath($path);
			}elseif(!is_dir($path)){ //We have the working directory, but not the theme directory in it
				@mkdir($path);
			}
			if(is_dir($path)){
				$this->wbf_wd = $path;
			}
		}
	}

	/**
	 * Returns WBF base working directory (without the theme)
	 *
	 * @return bool|string
	 */
	function get_base_working_directory(){
		if($this->wbf_wd){
			return rtrim(dirname($this->wbf_wd),"/");
		}
		return false;
	}

	/**
	 * Returns WBF working directory
	 *
	 * @return bool|string
	 */
	function get_working_directory(){
		if($this->wbf_wd){
			return rtrim($this->wbf_wd,"/");
		}
		return false;
	}

    function get_working_directory_uri(){
        return path_to_url($this->get_working_directory());
    }

	private function __clone(){}
	private function __wakeup(){}
}