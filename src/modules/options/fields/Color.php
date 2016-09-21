<?php
namespace WBF\modules\options\fields;

class Color extends BaseField implements Field{

	public function get_html() {
		$default_color = '';
		$val = $this->value;


		$output = "";
		if (isset($current_option['std'])) {
			if ($val != $current_option['std']) {
				$default_color = ' data-default-color="' . $current_option['std'] . '" ';
			}
		}
		$output .= '<input name="' . $this->get_field_name() . '" id="' . $this->get_field_id() . '" class="of-color"  type="text" value="' . esc_attr($val) . '"' . $default_color . ' />';

		return $output;
	}
}