<?php
namespace WBF\modules\options\fields;

class Checkbox extends BaseField implements Field{

	public function get_html() {
		$current_option_description = $this->get_description();
		$val = $this->value;

		$output = "";
		$output .= '<div class="wb-onoffswitch">';
		$output .= '<div class="check_wrapper"><input id="' . $this->get_field_id() . '" class="checkbox of-input wb-onoffswitch-checkbox" type="checkbox" name="' . $this->get_field_name() . '" ' . checked($val, 1, false) . ' />';
		$output .= '<label class="wb-onoffswitch-label" for="' . $this->get_field_id() . '"><span class="wb-onoffswitch-inner"></span><span class="wb-onoffswitch-switch"></span></label></div>';
		$output .= '</div>';

		return $output;
	}

	public function sanitize( $input, $option ) {
		if ( $input ) {
			$output = '1';
		} else {
			$output = false;
		}
		return $output;
	}
}