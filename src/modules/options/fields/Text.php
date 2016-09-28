<?php

namespace WBF\modules\options\fields;

class Text extends BaseField implements Field{

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