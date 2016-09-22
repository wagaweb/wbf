<?php
namespace WBF\modules\options\fields;

class Radio extends BaseField implements Field{

	public function get_html() {
		$name = $this->get_field_name();
		$val = $this->value;
		$output = "";
		foreach ($this->related_option['options'] as $key => $option) {
			$id = $this->options_db_key . '-' . $this->related_option['id'] . '-' . $key;
			$output .= '<div class="radio-wrapper"><input class="of-input of-radio" type="radio" name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" value="' . esc_attr($key) . '" ' . checked($val, $key, false) . ' /><label for="' . esc_attr($id) . '">' . esc_html($option) . '</label></div>';
		}

		return $output;
	}

	public function sanitize( $input, $option ) {
		return $this->sanitize_enum_field($input,$option);
	}
}