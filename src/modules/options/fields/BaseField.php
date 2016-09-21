<?php

namespace WBF\modules\options\fields;

class BaseField {
	var $related_option;
	var $value;

	public function __construct() {}

	public function build($value,$related_option){
		$this->value = $value;
		$this->related_option = $related_option;
	}

	public function init(){}

	protected function get_field_name(){
	}
}