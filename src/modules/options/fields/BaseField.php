<?php

namespace WBF\modules\options\fields;

use WBF\modules\options\Framework;

class BaseField {
	var $related_option;
	var $value;
	var $options_db_key;

	public function __construct() {}

	public function init(){}

	public function setup($value,$related_option){
		$this->value = $value;
		$this->related_option = $related_option;
		$this->options_db_key = Framework::get_options_root_id();
	}

	protected function get_field_id(){
		return esc_attr($this->related_option['id']);
	}

	protected function get_field_name(){
		$id = $this->options_db_key;
		$name = $id."[".$this->related_option['id']."]";
		$name = esc_attr($name);
		return $name;
	}

	protected function get_description(){
		global $allowedtags;
		$current_option_description = '';
		if(isset($this->related_option['desc'])) {
			$current_option_description = $this->related_option['desc'];
			$current_option_description = wp_kses($current_option_description, $allowedtags);
		}
		return $current_option_description;
	}
}