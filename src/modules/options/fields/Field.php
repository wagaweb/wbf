<?php

namespace WBF\modules\options\fields;

interface Field {
	public function get_html();
	public function sanitize($input, $option);
}