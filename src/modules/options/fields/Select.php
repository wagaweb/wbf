<?php
namespace WBF\modules\options\fields;

class Select extends BaseField implements Field{

	public function get_html() {
		$output = '<select class="of-input" name="' . $this->get_field_name() . '" id="' . esc_attr($this->related_option['id']) . '">';

		foreach ($this->related_option['options'] as $key => $option) {
			$output .= '<option' . selected($this->value, $key, false) . ' value="' . esc_attr($key) . '">' . esc_html($option) . '</option>';
		}
		$output .= '</select>';

		return $output;
	}
}