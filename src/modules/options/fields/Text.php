<?php

namespace WBF\modules\options\fields;

class Text extends BaseField implements Field{

	public function init(){
		add_filter("wbf/theme_options/text/get_value",[$this,"get_value"]);
	}

	/**
	 * Perform some operation before printing out the option value
	 *
	 * @hooked 'wbf/theme_options/text/get_value'
	 *
	 * @param $value
	 * @return string
	 */
	public function get_value($value){
		if(is_string($value)){
			$value = stripslashes($value);
		}
		return $value;
	}

	/**
	 * return string
	 */
	public function get_html(){
		$output = '<input id="' . $this->get_field_id() . '" class="of-input" name="' . $this->get_field_name() . '" type="text" value="' . esc_attr($this->value) . '" />';
		return $output;
	}

	public function sanitize($input, $option) {
		global $allowedposttags;

		$custom_allowedtags["a"] = array(
			"href"   => array(),
			"target" => array(),
			"id"     => array(),
			"class"  => array()
		);

		$custom_allowedtags = array_merge( $custom_allowedtags, $allowedposttags );
		$output = wp_kses( $input, $custom_allowedtags );

		return $output;
	}
}