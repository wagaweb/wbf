<?php
namespace WBF\modules\options\fields;

class Images extends BaseField implements Field{

	public function get_html() {
		$output = "";
		$current_option = $this->related_option;
		$val = $this->value;
		$name = $this->get_field_name();

		if(!isset($this->related_option['options']) || !is_array($this->related_option['options'])) return $output;

		foreach ($current_option['options'] as $key => $option) {
			$selected = '';
			if ($val != '' && ($val == $key)) {
				$selected = ' of-radio-img-selected';
			}

			if(is_array($option)){
				$option_value = $option['value'];
			}else{
				$option_value = $option;
			}

			$output .= '<input type="radio" id="' . esc_attr($current_option['id'] . '_' . $key) . '" class="of-radio-img-radio" value="' . esc_attr($key) . '" name="' . esc_attr($name) . '" ' . checked($val, $key, false) . ' />';
			$output .= '<div class="of-radio-img-label">' . esc_html($key) . '</div>';
			$output .= '<div class="option-wrap">';
			if(is_array($option) && isset($option['label'])){
				$output .= '<span>'. esc_attr($option['label']) . '</span>';
			}
			$output .= '<img src="' . esc_url($option_value) . '" alt="' . $option_value . '" class="of-radio-img-img' . $selected . '" onclick="document.getElementById(\'' . esc_attr($current_option['id'] . '_' . $key) . '\').checked=true;" /></div>';
		}

		return $output;
	}
}