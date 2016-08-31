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

/**
 * Class Advanced_Color
 * @package WBF\modules\options
 */
class Advanced_Color {

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
	 * @param $current_option
	 * @param $val
	 * @param $options_db_key
	 *
	 * @return string
	 */
	static function display( $current_option, $val, $options_db_key ) {
		$default_color = '';
		if (isset($current_option['std'])) {
			if ($val != $current_option['std']) {
				$default_color = ' data-default-color="' . $current_option['std'] . '" ';
			}
		}
		$output = '<input name="' . esc_attr($options_db_key . '[' . $current_option['id'] . ']') . '" id="' . esc_attr($current_option['id']) . '" class="advanced-color"  type="text" value="' . esc_attr($val) . '"' . $default_color . ' />';

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