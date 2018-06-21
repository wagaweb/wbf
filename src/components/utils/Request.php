<?php
namespace WBF\components\utils;

class Request{
	/**
	 * Retrieve a GET or POST parameter (GET has priority over parameters with the same name). Assign $default when not
	 * found.
	 *
	 * @param string $paramName
	 * @param mixed|null $default
	 * @param bool $sanitize
	 * @param \callable|null $sanitizeCallback
	 *
	 * @return null
	 */
	static function get($paramName, $default = null, $sanitize = true, $sanitizeCallback = null){
		$paramValue = null;
		if(isset($_GET[$paramName])){
			$paramValue = $_GET[$paramName];
		}elseif(isset($_POST[$paramName])){
			$paramValue = $_POST[$paramName];
		}else{
			return $default; //Return early if the value has not been found
		}

		if($sanitize && isset($paramValue)){
			if(isset($sanitizeCallback)){
				if(is_callable($sanitizeCallback)){
					$paramValue = $sanitizeCallback($paramValue);
				}
				trigger_error('Unable to call sanitize callback',E_USER_NOTICE);
			}else{
				$paramValue = sanitize_text_field($paramValue);
			}
		}

		return $paramValue;
	}
}