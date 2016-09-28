<?php
namespace WBF\modules\options\fields;

class Textarea extends BaseField implements Field{
	/**
	 * return string
	 */
	public function get_html(){
		$output = "";
		$current_option = $this->related_option;
		$options_db_key = $this->options_db_key;
		$val = $this->value;

		$rows = '8';

		if (isset($current_option['settings']['rows'])) {
			$custom_rows = $current_option['settings']['rows'];
			if (is_numeric($custom_rows)) {
				$rows = $custom_rows;
			}
		}

		$val = stripslashes($val);
		$output .= '<textarea id="' . esc_attr($current_option['id']) . '" class="of-input" name="' . $this->get_field_name() . '" rows="' . $rows . '">' . esc_textarea($val) . '</textarea>';

		return $output;
	}

	public function sanitize( $input, $option ) {
		global $allowedposttags;
		$output = wp_kses( $input, $allowedposttags);
		return $output;
	}
}