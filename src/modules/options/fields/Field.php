<?php

namespace WBF\modules\options\fields;

interface Field {
	/**
	 * Return the field html
	 *
	 * @return string
	 */
	public function get_html();

	/**
	 * Sanitize the field. This function is hooked to 'of_sanitize_{field_type}' during module initialization.
	 *
	 * @param string|array $input
	 * @param array $option the option as defined by the developer
	 *
	 * @return mixed
	 */
	public function sanitize($input, $option);
}