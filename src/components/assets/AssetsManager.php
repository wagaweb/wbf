<?php
/*
 * This file is part of WBF Framework: https://github.com/wagaweb/wbf
 *
 * @author WAGA Team <dev@waga.it>
 */

namespace WBF\components\assets;
use WBF\components\utils\Utilities;

/**
 * Class AssetsManager
 *
 * A simple assets manager. This class is just a draft.
 *
 * @version 0.2.0
 *
 * @package WBF\includes
 */
class AssetsManager {
	/**
	 * @var array
	 */
	var $assets;

	/**
	 * AssetsManager constructor.
	 *
	 * @param array $assets
	 */
	public function __construct($assets = []){
		if(is_array($assets) && !empty($assets)){
			$this->add_assets($assets);
		}
	}

	/**
	 * Adds a single asset
	 *
	 * @param $name
	 * @param array $args
	 */
	public function add_asset($name,$args){
		$this->assets[$name] = $args;
	}

	/**
	 * Adds multiple assets
	 * @param array $assets
	 */
	public function add_assets($assets){
		foreach($assets as $name => $args){
			$this->add_asset($name,$args);
		}
	}

	/**
	 * Enqueue the registered assets
	 *
	 * @throws \Exception
	 */
	public function enqueue(){
		$to_enqueue = [];

		//Doing some checks
		foreach($this->assets as $name => $param){
			$param = wp_parse_args($param,[
				'uri' => '', //A valid uri
				'path' => '', //A valid path
				'version' => false, //If FALSE, the filemtime will be used (if path is set)
				'deps' => [], //Dependencies
				'i10n' => [], //the Localication array for wp_localize_script
				'type' => '', //js or css. Optional. Its autodetected if empty.
				'enqueue_callback' => false, //A valid callable that must be return true or false
				'in_footer' => false, //Used for scripts
				'enqueue' => true, //If FALSE the script\css will only be registered
				'media' => apply_filters('wbf/assets/styles/default_media','all') //The media for which this stylesheet has been defined. Accepts media types like 'all', 'print' and 'screen', or media queries like '(orientation: portrait)' and '(max-width: 640px)'.
			]);
			if($param['path'] != "" && !file_exists($param['path'])){
				if(is_admin()){
					Utilities::add_admin_notice("style_{$name}_not_found","Asset '$name' not found in '".$param['path']."'","error",['category'=>'_flash_']);
				}else{
					trigger_error("Asset '$name' not found in '".$param['path']."'");
				}
				continue;
			}
			if(isset($param['version']) && $param['version']){
				$version = $param['version'];
			}else{
				//Get version
				if($param['path'] != "" && file_exists($param['path'])){
					$version = filemtime($param['path']);	
				}else{
					$version = false;
				}
			}
			if($param['path'] != "" && file_exists($param['path']) && $param['type'] == ""){
				//Autodetect types
				$ext = pathinfo($param['path'], PATHINFO_EXTENSION);
				if(in_array($ext,['js','css'])){
					$param['type'] = $ext;
				}
			}
			if($param['type'] == "js"){
				wp_register_script($name,$param['uri'],$param['deps'],$version,$param['in_footer']);
			}elseif($param['type'] == "css"){
				wp_register_style($name,$param['uri'],$param['deps'],$version,$param['media']);
			}else{
				throw new \Exception("Unknow asset type for $name");
			}
			if($param['type'] == "js" && isset($param['i10n']) && is_array($param['i10n']) && !empty($param['i10n'])){
				if(is_array($param['i10n']) && array_key_exists("name",$param['i10n']) && array_key_exists("params",$param['i10n']) && is_array($param['i10n']['params'])){
					wp_localize_script($name,$param['i10n']['name'],$param['i10n']['params']);
				}
			}
			if($param['enqueue']){
				$to_enqueue[] = [
					"name" => $name,
					"type" => $param['type'],
					"callback" => isset($param['enqueue_callback']) && is_callable($param['enqueue_callback']) ? $param['enqueue_callback'] : false
				];
			}
		}

		//Actual enqueue
		if(!empty($to_enqueue)){
			foreach($to_enqueue as $s){
				if($s['callback']){
					$can_enqueue = call_user_func($s['callback']);
				}else{
					$can_enqueue = true;
				}
				if($can_enqueue){
					if($s['type'] == "js"){
						wp_enqueue_script($s['name']);
					}elseif($s['type'] == "css"){
						wp_enqueue_style($s['name']);
					}
				}
			}
		}
	}
}