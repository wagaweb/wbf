<?php

namespace WBF\includes;

use WBF\components\utils\Utilities;

class Resources{

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
	 * Resources constructor.
	 *
	 * @param null $path
	 * @param null $url
	 *
	 * @throws \Exception
	 */
	public function __construct($path = null,$url = null){
		$update_wbf_path_flag = false;
		$update_wbf_url_flag = false;

		if(!$path){
			$path = get_option("wbf_path");
		}else{
			$update_wbf_path_flag = true;
		}

		if(!$url){
			$url = $url = get_option("wbf_url");
		}else{
			$update_wbf_url_flag = true;
		}

		if($path && is_string($path) && !empty($path)){
			$path = rtrim($path,"/")."/";
		}else{
			throw new \Exception('Invalid path provided');
		}

		if($url && is_string($url) && !empty($url)){
			$url = rtrim($url,"/")."/";
		}else{
			throw new \Exception('Invalid url provided');
		}

		if( $update_wbf_path_flag && ( get_option('wbf_path','') !== $path) ){
			update_option('wbf_path',$path);
		}
		if( $update_wbf_url_flag && ( get_option('wbf_url','') !== $url) ){
			update_option('wbf_path',$url);
		}

		$this->wbf_path = $path;
		$this->wbf_url = $url;
	}

	/**
	 * Returns WBF url or FALSE
	 *
	 * @return bool|string
	 */
	public function get_url(){
		if(defined('WBF_URL')){
			return WBF_URL;
		}
		return $this->wbf_url;
	}

	/**
	 * Returns WBF path or FALSE
	 *
	 * @return bool|string
	 */
	public function get_path(){
		if(defined('WBF_PATH')){
			return WBF_PATH;
		}
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
	public function maybe_add_work_directory(){
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
	public function get_base_working_directory(){
		if($this->wbf_wd){
			return rtrim(dirname($this->wbf_wd),"/");
		}
		return false;
	}

	/**
	 * Returns WBF working directory
	 *
	 * @param bool $base (return dirname() of working directory)
	 *
	 * @return bool|string
	 */
	public function get_working_directory($base = false){
		if($this->wbf_wd){
			if($base){
				return dirname(rtrim($this->wbf_wd,"/"));
			}
			return rtrim($this->wbf_wd,"/");
		}
		return false;
	}

	/**
	 * Returns WBF working directory URI
	 *
	 * @param bool $base
	 *
	 * @return mixed
	 */
    public function get_working_directory_uri($base = false){
        return path_to_url($this->get_working_directory($base));
    }
}