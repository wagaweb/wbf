<?php

namespace WBF\includes;

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
	 * Returns WBF Theme dir
	 *
	 * @return bool|string
	 */
	public function get_theme_dir(){
		if(defined("WBF_THEME_DIRECTORY")){
			return rtrim(WBF_THEME_DIRECTORY,"/");
		}
		return false;
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
		$path = trim($this->get_url());
		$to = trim($to);
		if($path){
			return rtrim($path,"/")."/".ltrim($to,"/");
		}else{
			return false;
		}
	}

	private function __clone(){}
	private function __wakeup(){}
}