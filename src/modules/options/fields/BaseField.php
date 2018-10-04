<?php

namespace WBF\modules\options\fields;

use WBF\modules\options\Framework;

class BaseField {
	/**
	 * @var array the option as defined by the developer
	 */
	var $related_option;
	/**
	 * @var string|array field value
	 */
	var $value;
	/**
	 * The value of the wordpress option that tells the system under which wordpress option the current theme options are stored
	 *
	 * @var string
	 */
	var $options_db_key;
	/**
	 * @var bool if FALSE, this tells the system that this option cannot have values (eg: info, headings... )
	 */
	var $can_have_value = true;

	public function __construct() {}

	/**
	 * Empty function that is called during options module initialization for every field that implements it.
	 * Can be used to add hooks.
	 */
	public function init(){}

	/**
	 * Setup the field
	 *
	 * @param array|string $value
	 * @param array $related_option
	 */
	public function setup($value,$related_option){
		$this->value = $value;
		$this->related_option = $related_option;
		$this->options_db_key = Framework::get_options_root_id();
	}

	/**
	 * Get field id
	 *
	 * @return string
	 */
	public function get_field_id(){
		return esc_attr($this->related_option['id']);
	}

	/**
	 * Get field name
	 *
	 * @return string
	 */
	public function get_field_name(){
		$id = $this->options_db_key;
		$name = $id."[".$this->related_option['id']."]";
		$name = esc_attr($name);
		return $name;
	}

	/**
	 * Get field description
	 *
	 * @return string
	 */
	public function get_description(){
		global $allowedtags;
		$current_option_description = '';
		if(isset($this->related_option['desc'])) {
			$current_option_description = $this->related_option['desc'];
			$current_option_description = wp_kses($current_option_description, $allowedtags);
		}
		return $current_option_description;
	}

	/**
	 * Return the settings specified for the field when the options has been registered
	 *
	 * @return array
	 */
	public function get_relative_option_settings(){
		return $this->related_option;
	}

	/**
	 * Common sanitize function for options that have multiple values.
	 *
	 * @param string|array $input
	 * @param array $option
	 *
	 * @return string
	 */
	protected function sanitize_enum_field($input, $option){
		$output = '';
		if ( array_key_exists( $input, $option['options'] ) ) {
			$output = $input;
		}
		return $output;
	}

	/**
	 * Check if this option can have values.
	 *
	 * @return bool
	 */
	public function can_have_value(){
		return $this->can_have_value;
	}
}