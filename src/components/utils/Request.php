<?php
namespace WBF\components\utils;

class Request{
	/**
	 * Retrieve a GET or POST parameter (GET has priority over parameters with the same name)
	 *
	 * @param string $param_name
	 *
	 * @param bool $sanitize
	 *
	 * @return null
	 */
	static function get($param_name,$sanitize = true){
		$var = null;
		if(isset($_GET[$param_name])){
			$var = $_GET[$param_name];
		}elseif(isset($_POST[$param_name])){
			$var = $_POST[$param_name];
		}

		if($sanitize){
			$var = sanitize_text_field($var);
		}

		return $var;
	}
}