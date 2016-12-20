<?php
namespace WBF\components\customupdater;


class Theme_Update {
	/**
	 * @var string version number
	 */
	public $version;
	/**
	 * @var string The URL where the user can learn more about this version.
	 */
	public $details_url;
	/**
	 * @var string The download URL for this version of the theme. Optional.
	 */
	public $download_url;
	/**
	 * @var string theme slug
	 */
	public $theme;

	/**
	 * Theme_Update constructor.
	 */
	public function __construct() {

	}

	/**
	 * Create a new instance of ThemeUpdate from its JSON-encoded representation.
	 *
	 * @param string $json Valid JSON string representing a theme information object.
	 * @return Theme_Update New instance of ThemeUpdate, or NULL on error.
	 */
	public static function build_from_json($json){
		$apiResponse = json_decode($json);
		if ( empty($apiResponse) || !is_object($apiResponse) ){
			return null;
		}

		//Very, very basic validation.
		$valid = isset($apiResponse->version) && !empty($apiResponse->version) && isset($apiResponse->details_url) && !empty($apiResponse->details_url);
		if ( !$valid ){
			return null;
		}

		$update = new self();
		foreach(get_object_vars($apiResponse) as $key => $value){
			$update->$key = $value;
		}

		return $update;
	}

	/**
	 * Transform the update into the format expected by the WordPress core.
	 *
	 * @return array
	 */
	public function export_to_wp_format(){
		$update = array(
			'theme' => isset($this->slug) ? $this->slug : $this->theme,
			'new_version' => $this->version,
			'url' => $this->details_url,
		);

		if ( !empty($this->download_url) ){
			$update['package'] = $this->download_url;
		}

		return $update;
	}
}