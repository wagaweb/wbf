<?php

namespace WBF\components\license;

class License_Manager{

	static function get($license_slug,$type){
		$licenses = self::get_all();
		if(array_key_exists($type,$licenses) && array_key_exists($license_slug,$licenses[$type])){
			return $licenses[$type][$license_slug];
		}else{
			return false;
		}
	}

	/**
	 * Returns all theme licences, or false
	 * @return array|bool
	 */
	static function get_theme_licenses(){
		$licenses = self::get_all();
		if(isset($licenses['theme'])){
			return $licenses['theme'];
		}else{
			return false;
		}
	}

	/**
	 * Checks if there are at least one registered theme license
	 * @return bool
	 */
	static function has_theme_licenses(){
		return is_array(self::get_theme_licenses());
	}

	/**
	 * Checks if there are at least one registered plugin license
	 * @return bool
	 */
	static function has_plugin_licenses(){
		return is_array(self::get_plugin_licenses());
	}

	/**
	 * Returns all plugin licenses or false
	 * @return array|bool
	 */
	static function get_plugin_licenses(){
		$licenses = self::get_all();
		if(isset($licenses['plugin'])){
			return $licenses['plugin'];
		}else{
			return false;
		}
	}

	/**
	 * Update a specific license
	 *
	 * @param $license_slug
	 * @param $type
	 * @param $value
	 *
	 * @throws License_Exception
	 */
	static function update($license_slug,$type,$value){
		$l = self::get($license_slug,$type);
		$value = $l::sanitize_license($value);
		if($value && is_string($value)){
			$l->update($value);
			do_action("wbf/license_updated",$l);
		}else{
			throw new License_Exception(__("License sanitization has gone wrong","wbf"));
		}
	}

	/**
	 * Delete a specific license
	 * @param $license_slug
	 * @param $type
	 */
	static function delete($license_slug,$type){
		$l = self::get($license_slug,$type);
		$l->remove();
		do_action("wbf/license_removed",$l);
	}

	/**
	 * Returns registered licenses
	 * @return mixed|void
	 */
	static function get_all(){
		$licenses = apply_filters("wbf/admin/licences/registered",[]);
		return $licenses;
	}

	/**
	 * Register a new license
	 * @param \WBF\components\license\License $license
	 * @return License
	 */
	static function register(License $license, $type){
		add_filter("wbf/admin/licences/registered",function($licenses) use($license, $type){
			$licenses[$type][$license->slug] = $license;
			return $licenses;
		});
		return $license;
	}

	/**
	 * Register a License class for a theme
	 * @param License $license
	 *
	 * @return License
	 */
	static function register_theme_license(License $license){
		return self::register($license,"theme");
	}

	/**
	 * Register a License class for a plugin. The call to Plugin_Update_Checker is done by Plugin Framework
	 * @param License $license
	 *
	 * @return License
	 */
	static function register_plugin_license(License $license){
		return self::register($license,"plugin");
	}

	/**
	 * Checks if a specified theme has a license registered
	 * @param $theme_slug
	 *
	 * @return \WBF\components\license\License|bool
	 */
	static function theme_has_license($theme_slug){
		return self::get($theme_slug,"theme");
	}

	/**
	 * Hides the first characters of a license code
	 * @param     $code
	 * @param int $cut_point
	 *
	 * @return string
	 */
	static function crypt_license_visual($code,$cut_point = 4){
		$first_chars = substr($code,0,strlen($code)-$cut_point);
		$first_chars = preg_replace("|[\\w]|","*",$first_chars);
		$last_chars = substr($code,strlen($code)-$cut_point);
		return $first_chars.$last_chars;
	}
}