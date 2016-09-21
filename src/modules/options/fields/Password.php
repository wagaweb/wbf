<?php

namespace WBF\modules\options\fields;

class Password extends BaseField implements Field{

	/**
	 * return string
	 */
	public function get_html($option,$value,$theme_options_root_id){
		return '<input id="' . esc_attr($option['id']) . '" class="of-input" name="' . esc_attr($theme_options_root_id . '[' . $option['id'] . ']') . '" type="password" value="' . esc_attr($value) . '" />';
	}

	public function sanitize() {
		// TODO: Implement sanitize() method.
	}
}