<?php

namespace WBF\modules\options\fields;

class Text extends BaseField implements Field{

	/**
	 * return string
	 */
	public function get_html(){
		$output = '<input id="' . $this->get_field_id() . '" class="of-input" name="' . $this->get_field_name() . '" type="text" value="' . esc_attr($this->value) . '" />';
		return $output;
	}

	public function sanitize() {
		// TODO: Implement sanitize() method.
	}
}