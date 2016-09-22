<?php
namespace WBF\modules\options\fields;

class Heading extends BaseField implements Field{

	public function get_html() {
		$args = func_get_args();
		$counter = $args[0];

		$current_option = $this->related_option;

		$output = "";

		$class = '';
		$class = !empty($current_option['id']) ? $current_option['id'] : $current_option['name'];
		$class = preg_replace('/[^a-zA-Z0-9._\-]/', '', strtolower($class));
		$section_id = isset($current_option['section_id']) ? $current_option['section_id'] : "";
		if($section_id !== "") $class = $class." ".$section_id;
		$output .= '<div id="options-group-' . $counter . '" class="group ' . $class . '">';
		$output .= '<h3>' . esc_html($current_option['name']) . '</h3>' . "\n";

		return $output;
	}

	public function sanitize( $input, $option ) {
		return $input;
	}
}