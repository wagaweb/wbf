<?php
namespace WBF\modules\options\fields;

class Multicheck extends BaseField implements Field{

	public function get_html() {
		$current_option = $this->related_option;
		$val = $this->value;
		$output = "";

		foreach ($current_option['options'] as $key => $option) {
			$checked = '';
			$label = $option;
			$option = preg_replace('/[^a-zA-Z0-9._\-]/', '', strtolower($key));

			$id = $this->options_db_key . '-' . $current_option['id'] . '-' . $option;
			$name = $this->options_db_key . '[' . $current_option['id'] . '][' . $option . ']';

			if (isset($val[$option])) {
				$checked = checked($val[$option], 1, false);
			}

			$output .= '<div class="check-wrapper"><input id="' . esc_attr($id) . '" class="checkbox of-input" type="checkbox" name="' . esc_attr($name) . '" ' . $checked . ' /><label for="' . esc_attr($id) . '">' . esc_html($label) . '</label></div>';
		}

		return $output;
	}
}