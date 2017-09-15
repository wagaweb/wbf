<?php

namespace WBF\components\utils;

class Response{
	/**
	 * Send a file to the browser
	 *
	 * @from: https://stackoverflow.com/questions/1946479/php-downloading-all-file-types
	 * @from: https://github.com/apfelbox/PHP-File-Download/blob/master/src/FileDownload.php
	 *
	 * @param string $filename
	 *
	 * @throws \Exception
	 */
	static function send_file($filename){
		if(headers_sent()){
			throw new \Exception('Cannot send headers to display the PDF. Headers already sent');
		}

		if(!is_file($filename)){
			http_response_code(404);
			die();
		}

		$getMime = function($filename){
			$mime = mime_content_type($filename);
			return $mime === 'inode/x-empty' || !$mime ? "application/force-download" : $mime;
		};

		$getSize = function($filename){
			$size = filesize($filename);
			return $size;
		};

		$fileInfo = new \SplFileInfo($filename);
		$mimeType = $getMime($filename);
		$size = $getSize($filename);

		if($fileInfo->getExtension() === 'pdf'){
			self::send_pdf($filename);
		}else{
			$pointer = fopen($filename,"rb");

			header('Content-Type: '.$mimeType);
			header('Content-Disposition: attachment; filename="'. basename($filename) . '"');

			header('Content-Transfer-Encoding: binary');
			header("Content-Length: {$size}");

			@ob_clean();
			rewind($pointer);
			fpassthru($pointer);
		}
	}

	/**
	 * Send a PDF to the browser
	 *
	 * @param string $filename
	 * @param bool $skip_checks
	 *
	 * @throws \Exception
	 */
	static function send_pdf($filename, $skip_checks = false){

		if(!$skip_checks){
			if(headers_sent()){
				throw new \Exception('Cannot send headers to display the PDF. Headers already sent');
			}

			if(!is_file($filename)){
				http_response_code(404);
				die();
			}
		}

		header('Content-Type: application/pdf');
		header('Content-disposition: inline; filename="' . $filename . '"');

		header('Cache-Control: public, must-revalidate, max-age=0');
		header('Pragma: public');
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

		@readfile($filename);
	}
}