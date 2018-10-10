<?php

namespace WBF\components\mvc;

class ViewNotFoundException extends \Exception{
	/**
	 * @var string
	 */
	private $viewFilePath;
	/**
	 * @var array
	 */
	private $searchPaths;

	public function __construct( $viewFilePath, $searchPaths ) {
		if(!\is_array($searchPaths)){
			$searchPaths = [];
		}
		$this->viewFilePath = $viewFilePath;
		$this->searchPaths = $searchPaths;
		$message = "File {$viewFilePath} does not exists in any of these locations: ".implode(",\n",$searchPaths);
		parent::__construct( $message, 'view_not_found');
	}

	/**
	 * @return string
	 */
	public function getViewFilePath(){
		return $this->viewFilePath;
	}

	/**
	 * @return array
	 */
	public function getSearchPaths(){
		return $this->searchPaths;
	}

	/**
	 * @param string $glue
	 *
	 * @return string
	 */
	public function getSearchPathsString($glue){
		return implode($glue,$this->searchPaths);
	}
}