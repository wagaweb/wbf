<?php
namespace wbf\components\customupdater;

class Theme_State {
	/**
	 * @var int timestamp
	 */
	var $lastCheck = 0;
	/**
	 * @var string
	 */
	var $checkedVersion = false;
	/**
	 * @var Theme_Update|boolean
	 */
	var $update = false;

	public function __construct() {}
}