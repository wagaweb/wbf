<?php
namespace WBF\modules\options\fields;

class Editor extends BaseField implements Field{

	public function get_html() {
		$current_option_description = $this->get_description();
		$current_option = $this->related_option;
		$val = $this->value;

		$output = "";

		$output .= '<div class="explain">' . $current_option_description . '</div>' . "\n";
		echo $output;
		$textarea_name = $this->get_field_name();
		$default_editor_settings = array(
			'textarea_name' => $textarea_name,
			'media_buttons' => false,
			'tinymce' => array('plugins' => 'wordpress')
		);
		$editor_settings = array();
		if (isset($current_option['settings'])) {
			$editor_settings = $current_option['settings'];
		}
		$editor_settings = array_merge($default_editor_settings, $editor_settings);
		\wp_editor($val, $current_option['id'], $editor_settings);
		$output = '';
		return $output;
	}

	public function sanitize( $input, $option ) {
		if ( current_user_can( 'unfiltered_html' ) ) {
			$output = $input;
		}
		else {
			global $allowedtags;
			$output = wpautop(wp_kses( $input, $allowedtags));
		}
		return $output;
	}
}