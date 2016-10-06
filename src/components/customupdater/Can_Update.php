<?php
namespace WBF\components\customupdater;

use WBF\components\notices\conditions\Condition;

class Can_Update implements Condition {

	var $slug;

	function __construct($slug) {
		$this->slug = $slug;
	}

	function verify() {
		$opt = get_option("wbf_invalid_licenses",[]);
		if(isset($opt['slug'])){
			return false;
		}
		return true;
	}
}