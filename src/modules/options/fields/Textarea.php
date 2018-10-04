<?php
namespace WBF\modules\options\fields;

class Textarea extends BaseField implements Field{

	public function init(){
		add_filter("wbf/theme_options/textarea/get_value",[$this,"get_value"]);
	}

	/**
	 * Perform some operation before printing out the option value
	 *
	 * @hooked 'wbf/theme_options/textarea/get_value'
	 *
	 * @param $value
	 * @return string
	 */
	public function get_value($value){
		if(\is_string($value)){
			$value = stripslashes($value);
		}
		return $value;
	}

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
		$val = $this->is_raw() ? $val : esc_textarea($val);
		$output .= '<textarea id="' . esc_attr($current_option['id']) . '" class="of-input" name="' . $this->get_field_name() . '" rows="' . $rows . '">' . $val . '</textarea>';

		return $output;
	}

	public function sanitize( $input, $option ) {
		if($this->is_raw($option)){
			return $input;
		}
		global $allowedposttags;

		if(\is_array($allowedposttags)){
			$custom_allowedtags = $allowedposttags;
		}else{
			$custom_allowedtags = [];
		}

		$custom_allowedtags = apply_filters('wbf/modules/options/fields/textarea/allowed_tags',$custom_allowedtags,$input,$option,$this);
		$output = wp_kses( $input, $custom_allowedtags);

		return $output;
	}

	/**
	 * @param null $settings
	 *
	 * @return bool
	 */
	public function is_raw($settings = null){
		if($settings === null){
			$settings = $this->get_relative_option_settings();
		}
		return isset($settings['raw']) && $settings['raw'] === true;
	}
}