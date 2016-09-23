<?php
namespace WBF\modules\options\fields;

class Heading extends BaseField implements Field{

	var $can_have_value = false;

	public function get_html() {
		$current_option = $this->related_option;

		$output = "";

		$output .= '<h3>' . esc_html($current_option['name']) . '</h3>' . "\n";

		return $output;
	}

	public function sanitize( $input, $option ) {
		return $input;
	}
}