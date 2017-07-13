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

	/**
	 * Send a PDF to the browser
	 *
	 * @param $filename
	 *
	 * @throws \Exception
	 */
	static function send_pdf($filename){
		if(headers_sent()){
			throw new \Exception('Cannot send headers to display the PDF. Headers already sent');
		}

		if(!is_file($filename)){
			http_response_code(404);
			die();
		}

		header('Content-Type: application/pdf');
		header('Content-disposition: inline; filename="' . $filename . '"');
		header('Cache-Control: public, must-revalidate, max-age=0');
		header('Pragma: public');
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		readfile($filename);
	}
}