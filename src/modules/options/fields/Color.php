<?php
namespace WBF\modules\options\fields;

class Color extends BaseField implements Field{

	public function get_html() {
		$default_color = '';
		$val = $this->value;
		$current_option = $this->related_option;


		$output = "";
		if (isset($current_option['std'])) {
			if ($val != $current_option['std']) {
				$default_color = ' data-default-color="' . $current_option['std'] . '" ';
			}
		}
		$output .= '<input name="' . $this->get_field_name() . '" id="' . $this->get_field_id() . '" class="of-color"  type="text" value="' . esc_attr($val) . '"' . $default_color . ' />';

		return $output;
	}

	public function sanitize( $input, $option ) {
		$validate_hex = function($hex){
			$hex = trim( $hex );
			// Recognized prefixes.
			if ( 0 === strpos( $hex, '%23' ) ) {
				$hex = substr( $hex, 3 );
			}
			// Check if it is a valid hex.
			if ( preg_match( '/^#[0-9a-fA-F]{6}$/', $hex ) === 0 && $hex != "" ) { //Is an invalid hex. Empty value are accepted @since 0.14.9
				return false;
			}
			else {
				return true;
			}
		};

		if ( $validate_hex( $input ) ) {
			return $input;
		}

		if(isset($option['std'])) return $option['std'];
		return "";
	}
}