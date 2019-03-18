<?php
namespace WBF\modules\options\fields;

use WBF\components\assets\AssetsManager;
use WBF\components\utils\Utilities;
use WBF\includes\Resources;
use WBF\modules\options\fields\BaseField;
use WBF\modules\options\fields\Field;

/**
 * Class Advanced_Color
 * @package WBF\modules\options
 */
class Advanced_Color extends BaseField implements Field{

	/**
	 * Init editor actions. Called by Framework->init()
	 */
	public function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
		// Add the required scripts and styles
		add_filter( 'wbf/js/admin/deps', function($deps){
			$deps[] = "spectrum-js";
			return $deps;
		});
	}


	/**
	 *
	 * @return string
	 */
	public function get_html() {
		$default_color = '';
		if (isset($this->related_option['std'])) {
			if ($this->value != $this->related_option['std']) {
				$default_color = ' data-default-color="' . $this->related_option['std'] . '" ';
			}
		}
		$output = '<input name="' . $this->get_field_name() . '" id="' . $this->get_field_id() . '" class="advanced-color"  type="text" value="' . esc_attr($this->value) . '"' . $default_color . ' />';

		return $output;
	}

	public function scripts() {
		$res = [
			'spectrum-js' => [
				'uri' => WBF()->prefix_url('assets/dist/js/includes/spectrum.min.js'),
				'path' => WBF()->prefix_path('assets/dist/js/includes/spectrum.min.js'),
				'type' => 'js'
			],
			'spectrum-css' => [
				'uri' => WBF()->prefix_url('assets/vendor/spectrum.css'),
				'path' => WBF()->prefix_path('assets/vendor/spectrum.css'),
				'type' => 'css'
			]
		];

		$am = new AssetsManager($res);
		$am->enqueue();
	}

	public function sanitize( $input, $option ) {
		if (strstr($input, 'hsva') !== false) {
			$val = str_replace('hsva(', '', $input);
			$val = str_replace(')', '', $val);
			$values = explode(', ', $val);

			$rgb = Utilities::fGetRGB($values[0], $values[1], $values[2]);
			if (is_null($values[3])) {
				return $rgb;
			}
			$rgba = 'rgba( ' . $rgb . ',' . $values[3] . ')';
			return $rgba;
		}
		return $input;
	}
}