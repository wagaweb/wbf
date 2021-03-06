<?php
namespace WBF\modules\options\fields;

class Info extends BaseField implements Field{

	var $can_have_value = false;

	public function get_html() {
		$current_option = $this->related_option;
		$id = '';
		$class = 'section';
		$output = '';
		if (isset($current_option['id'])) {
			$id = 'id="' . $this->get_field_id() . '" ';
		}
		if (isset($current_option['type'])) {
			$class .= ' section-' . $current_option['type'];
		}
		if (isset($current_option['class'])) {
			$class .= ' ' . $current_option['class'];
		}

		$output .= '<div ' . $id . 'class="' . esc_attr($class) . '">' . "\n";
		if (isset($current_option['name'])) {
			$output .= '<h4 class="heading">' . esc_html($current_option['name']) . '</h4>' . "\n";
		}
		if ($current_option['desc']) {

			$output .= "<p>" . $this->get_description() . "</p>\n";
		}
		$output .= '</div>' . "\n";

		return $output;
	}

	public function sanitize( $input, $option ) {
		return $input;
	}
}