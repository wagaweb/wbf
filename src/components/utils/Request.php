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
	 * @param \callable|null $sanitizeCallback
	 *
	 * @return null
	 */
	static function get($param_name,$sanitize = true,$sanitizeCallback = null){
		$var = null;
		if(isset($_GET[$param_name])){
			$var = $_GET[$param_name];
		}elseif(isset($_POST[$param_name])){
			$var = $_POST[$param_name];
		}

		if($sanitize){
			if(isset($sanitizeCallback)){
				if(is_callable($sanitizeCallback)){
					$var = $sanitizeCallback($var);
				}
				trigger_error('Unable to call sanitize callback',E_USER_NOTICE);
			}else{
				$var = sanitize_text_field($var);
			}
		}

		return $var;
	}
}