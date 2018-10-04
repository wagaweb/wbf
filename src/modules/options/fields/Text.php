<?php

namespace WBF\modules\options\fields;

class Text extends BaseField implements Field{

	public function init(){
		add_filter("wbf/theme_options/text/get_value",[$this,"get_value"]);
	}

	/**
	 * Perform some operation before printing out the option value
	 *
	 * @hooked 'wbf/theme_options/text/get_value'
	 *
	 * @param $value
	 * @return string
	 */
	public function get_value($value){
		if(is_string($value)){
			$value = stripslashes($value);
		}
		return $value;
	}

	/**
	 * return string
	 */
	public function get_html(){
		$value = $this->is_raw() ? $this->value : esc_attr($this->value);
		$output = '<input id="' . $this->get_field_id() . '" class="of-input" name="' . $this->get_field_name() . '" type="text" value="' . $value . '" />';
		return $output;
	}

	public function sanitize($input, $option) {
		if($this->is_raw($option)){
			return $input;
		}

		global $allowedposttags;

		$custom_allowedtags["a"] = array(
			"href"   => array(),
			"target" => array(),
			"id"     => array(),
			"class"  => array()
		);

		if(\is_array($allowedposttags)){
			$custom_allowedtags = array_merge( $custom_allowedtags, $allowedposttags );
		}

		$custom_allowedtags = apply_filters('wbf/modules/options/fields/text/allowed_tags',$custom_allowedtags,$input,$option,$this);

		$output = wp_kses( $input, $custom_allowedtags );

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