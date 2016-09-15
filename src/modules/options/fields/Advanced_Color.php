<?php
/**
 * @package   Options Module
 * @author    Riccardo D'Angelo <riccardo@waga.it>, WAGA <dev@waga.it>
 * @license   GPL-2.0+
 * @link      http://www.waboot.com
 * @copyright WAGA.it
 *
 * Based on Devin Price' Options_Framework
 */

namespace WBF\modules\options;

use WBF\components\assets\AssetsManager;
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
		$options_db_key = Framework::get_options_root_id();

		$default_color = '';
		if (isset($this->related_option['std'])) {
			if ($this->value != $this->related_option['std']) {
				$default_color = ' data-default-color="' . $this->related_option['std'] . '" ';
			}
		}
		$output = '<input name="' . esc_attr($options_db_key . '[' . $this->related_option['id'] . ']') . '" id="' . esc_attr($this->related_option['id']) . '" class="advanced-color"  type="text" value="' . esc_attr($this->value) . '"' . $default_color . ' />';

		return $output;
	}

	public function scripts() {
		$res = [
			'spectrum-js' => [
				'uri' => Resources::getInstance()->prefix_url('assets/dist/js/includes/spectrum.min.js'),
				'path' => Resources::getInstance()->prefix_path('assets/dist/js/includes/spectrum.min.js'),
				'type' => 'js'
			],
			'spectrum-css' => [
				'uri' => Resources::getInstance()->prefix_url('vendor/spectrum/spectrum.css'),
				'path' => Resources::getInstance()->prefix_path('vendor/spectrum/spectrum.css'),
				'type' => 'css'
			]
		];

		$am = new AssetsManager($res);
		$am->enqueue();
	}
}