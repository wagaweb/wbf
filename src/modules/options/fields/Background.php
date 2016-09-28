<?php
namespace WBF\modules\options\fields;

use WBF\modules\options\MediaUploader;

class Background extends BaseField implements Field{

	public function get_html() {
		$val = $this->value;
		$options_db_key = $this->options_db_key;
		$current_option = $this->related_option;

		$output = "";

		$background = $val;

		$media_uploader = new MediaUploader();
		$media_uploader->setup($background['image'],$current_option);

		// Background Color
		$default_color = '';
		if (isset($current_option['std']['color'])) {
			if ($val != $current_option['std']['color']) {
				$default_color = ' data-default-color="' . $current_option['std']['color'] . '" ';
			}
		}
		$output .= '<input name="' . esc_attr($options_db_key . '[' . $current_option['id'] . '][color]') . '" id="' . esc_attr($current_option['id'] . '_color') . '" class="of-color of-background-color"  type="text" value="' . esc_attr($background['color']) . '"' . $default_color . ' />';

		// Background Image
		if (!isset($background['image'])) {
			$background['image'] = '';
		}

		$output .= $media_uploader->get_html(esc_attr($options_db_key . '[' . $current_option['id'] . '][image]'));

		$class = 'of-background-properties';
		if ('' == $background['image']) {
			$class .= ' hide';
		}
		$output .= '<div class="' . esc_attr($class) . '">';

		// Background Repeat
		$output .= '<select class="of-background of-background-repeat" name="' . esc_attr($options_db_key . '[' . $current_option['id'] . '][repeat]') . '" id="' . esc_attr($current_option['id'] . '_repeat') . '">';
		$repeats = \WBF\modules\options\of_recognized_background_repeat();

		foreach ($repeats as $key => $repeat) {
			$output .= '<option value="' . esc_attr($key) . '" ' . selected($background['repeat'], $key, false) . '>' . esc_html($repeat) . '</option>';
		}
		$output .= '</select>';

		// Background Position
		$output .= '<select class="of-background of-background-position" name="' . esc_attr($options_db_key . '[' . $current_option['id'] . '][position]') . '" id="' . esc_attr($current_option['id'] . '_position') . '">';
		$positions = \WBF\modules\options\of_recognized_background_position();

		foreach ($positions as $key => $position) {
			$output .= '<option value="' . esc_attr($key) . '" ' . selected($background['position'], $key, false) . '>' . esc_html($position) . '</option>';
		}
		$output .= '</select>';

		// Background Attachment
		$output .= '<select class="of-background of-background-attachment" name="' . esc_attr($options_db_key . '[' . $current_option['id'] . '][attachment]') . '" id="' . esc_attr($current_option['id'] . '_attachment') . '">';
		$attachments = \WBF\modules\options\of_recognized_background_attachment();

		foreach ($attachments as $key => $attachment) {
			$output .= '<option value="' . esc_attr($key) . '" ' . selected($background['attachment'], $key, false) . '>' . esc_html($attachment) . '</option>';
		}
		$output .= '</select>';
		$output .= '</div>';

		return $output;
	}

	public function sanitize( $input, $option ) {
		global $wbf_options_framework;

		$output = wp_parse_args( $input, array(
			'color' => '',
			'image'  => '',
			'repeat'  => 'no-repeat',
			'position' => 'top center',
			'attachment' => 'scroll'
		) );

		//Validate color
		$output['color'] = call_user_func(function($hex){
			$hex = trim( $hex );
			/* Strip recognized prefixes. */
			if ( 0 === strpos( $hex, '#' ) ) {
				$hex = substr( $hex, 1 );
			}
			elseif ( 0 === strpos( $hex, '%23' ) ) {
				$hex = substr( $hex, 3 );
			}
			/* Regex match. */
			if ( 0 === preg_match( '/^[0-9a-fA-F]{6}$/', $hex ) ) {
				if(isset($option['std']['color'])){
					return $option['std']['color'];
				}
				return "";
			}
			else {
				return $hex;
			}
		},$input['color']);

		//Validate image
		$output['image'] = call_user_func(function($input){
			$output = '';
			$filetype = wp_check_filetype($input);
			if ( $filetype["ext"] ) {
				$output = $input;
			}
			return $output;
		},$input['image']);

		//Validate repeat
		$output['repeat'] = call_user_func(function($input){
			$recognized = array(
				'no-repeat' => __( 'No Repeat', 'wbf' ),
				'repeat-x'  => __( 'Repeat Horizontally', 'wbf' ),
				'repeat-y'  => __( 'Repeat Vertically', 'wbf' ),
				'repeat'    => __( 'Repeat All', 'wbf' ),
			);
			if ( array_key_exists( $input, $recognized ) ) {
				return $input;
			}
			return 'no-repeat';
		},$input['repeat']);

		//Validate position
		$output['position'] = call_user_func(function($input){
			$recognized = [
				'top left'      => __( 'Top Left', 'textdomain' ),
				'top center'    => __( 'Top Center', 'textdomain' ),
				'top right'     => __( 'Top Right', 'textdomain' ),
				'center left'   => __( 'Middle Left', 'textdomain' ),
				'center center' => __( 'Middle Center', 'textdomain' ),
				'center right'  => __( 'Middle Right', 'textdomain' ),
				'bottom left'   => __( 'Bottom Left', 'textdomain' ),
				'bottom center' => __( 'Bottom Center', 'textdomain' ),
				'bottom right'  => __( 'Bottom Right', 'textdomain')
			];
			if ( array_key_exists( $input, $recognized ) ) {
				return $input;
			}
			return 'top center';
		},$input['position']);

		//Validate attachment
		$output['attachment'] = call_user_func(function($input){
			$recognized = [
				'scroll' => __( 'Scroll Normally', 'textdomain' ),
				'fixed'  => __( 'Fixed in Place', 'textdomain')
			];
			if ( array_key_exists( $input, $recognized ) ) {
				return $input;
			}
			return 'scroll';
		},$input['attachment']);

		return $output;
	}
}