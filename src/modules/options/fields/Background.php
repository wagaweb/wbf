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
}