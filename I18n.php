<?php

namespace WBF\components\pluginsframework;

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Waboot_Plugin
 */
class I18n {
	/**
	 * The domain specified for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $domain The domain identifier for this plugin.
	 */
	private $domain;

	/**
	 * The language directory (relative to WP_PLUGIN_DIR)
	 *
	 * @var string
	 */
	private $dir;

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			$this->domain,
			false,
			$this->dir
		);
	}

	/**
	 * Set the domain equal to that of the specified domain.
	 *
	 * @since    1.0.0
	 *
	 * @param    string $domain The domain that represents the locale of this plugin.
	 */
	public function set_domain( $domain ) {
		$this->domain = $domain;
	}

	/**
	 * Language dir setter
	 *
	 * @param $dir
	 */
	public function set_language_dir($dir){
		$this->dir = $dir;
	}

	/**
	 * Domain getter
	 *
	 * @return string
	 */
	public function get_domain(){
		return $this->domain;
	}

	/**
	 * Language dir getter
	 * 
	 * @return string
	 */
	public function get_language_dir(){
		return $this->dir;
	}
}
