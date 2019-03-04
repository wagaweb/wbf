<?php
/*
 * This file is part of WBF Framework: https://github.com/wagaweb/wbf
 *
 * @author WAGA Team <dev@waga.it>
 */

namespace WBF\components\customupdater;

use WBF\components\notices\Notice_Manager;
use WBF\components\utils\Utilities;

class Theme_Update_Checker{

	/**
	 * @var string
	 */
	var $optionName = ''; //Where to store update info.
	/**
	 * @var Notice_Manager
	 */
	var $notice_manager;
	/**
	 * @var string
	 */
	var $theme;
	/**
	 * @var string
	 */
	var $endpoint;
	/**
	 * @var boolean
	 */
	var $enableAutomaticChecking;
	/**
	 * @var boolean
	 */
	var $automaticCheckDone;
	/**
	 * @var Theme_State
	 */
	var $current_state;

	/**
	 * Class constructor.
	 *
	 * @param string $theme Theme slug, e.g. "twentyten".
	 * @param string $endpoint The URL of the theme metadata.
	 * @param boolean $enableAutomaticChecking Enable/disable automatic update checking. If set to FALSE, you'll need to explicitly call checkForUpdates() to, err, check for updates.
	 */
	public function __construct($theme, $endpoint, $enableAutomaticChecking = true){
		$this->theme = $theme;
		$this->endpoint = $endpoint;
		$this->enableAutomaticChecking = $enableAutomaticChecking;
		$this->theme = $theme;
		$this->optionName = 'wbf_external_theme_updates-'.$this->theme;

		//Get current state
		$this->current_state = get_option($this->optionName);

		//Load Notice Manager if needed
		$wbf_notice_manager = Notice_Manager::get_global_instance();
		$this->notice_manager = &$wbf_notice_manager;

		//Install required hooks
		$this->installHooks();
	}

	/**
	 * Install the hooks required to run periodic update checks and inject update info
	 * into WP data structures.
	 *
	 * @return void
	 */
	public function installHooks(){
		//Check for updates when WordPress does. We can detect when that happens by tracking
		//updates to the "update_themes" transient, which only happen in wp_update_themes().
		if ( $this->enableAutomaticChecking ){
			add_filter('pre_set_site_transient_update_themes', array($this, 'onTransientUpdate'));
		}

		//Insert our update info into the update list maintained by WP.
		add_filter('site_transient_update_themes', array($this,'injectUpdate'));

		//Delete our update info when WP deletes its own.
		//This usually happens when a theme is installed, removed or upgraded.
		add_action('delete_site_transient_update_themes', array($this, 'deleteStoredData'));
	}

	/**
	 * Run the automatic update check, but no more than once per page load.
	 * This is a callback for WP hooks. Do not call it directly.
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public function onTransientUpdate($value){
		if ( !$this->automaticCheckDone ){
			$this->update_current_state();
			$this->automaticCheckDone = true;
		}
		return $value;
	}

	/**
	 * Insert the latest update (if any) into the update list maintained by WP.
	 *
	 * @param mixed $updates Update list.
	 *
	 * @hooked 'site_transient_{update_themes}'
	 *
	 * @return mixed Modified update list.
	 */
	public function injectUpdate($updates){
		$state = $this->get_current_state();

		//Is there an update to insert?
		if ( $state instanceOf Theme_State && isset($state->update) && !empty($state->update) && $state->update instanceof Theme_Update){
			$can_update = apply_filters("wbf/custom_theme_updater/can_update", true, $this);
			if($can_update){
				$updates->response[$this->theme] = $state->update->export_to_wp_format();
			}else{
				//This will make update fails:
				$response = $state->update->export_to_wp_format();
				$response['package'] = "";
				$updates->response[$this->theme] = $response;
				//Update the state option
				$this->update_current_state($state);
			}

			do_action("wbf/custom_theme_updater/after_update_inject", $this, $can_update);
		}

		return $updates;
	}

	/**
	 * Delete any stored book-keeping data.
	 *
	 * @return void
	 */
	public function deleteStoredData(){
		delete_option($this->optionName);
	}

	/**
	 * Get the current state
	 *
	 * @return Theme_State|boolean
	 */
	public function get_current_state(){
		if($this->current_state instanceof Theme_State){
			return $this->current_state;
		}else{
			return false;
		}
	}

	/**
	 * Set the current state
	 *
	 * @param Theme_State $state
	 */
	public function set_current_state(Theme_State $state){
		$this->current_state = $state;
		update_option($this->optionName,$state);
	}

	/**
	 * Update the current state with latest updates available.
	 *
	 * @param Theme_State|null $state
	 */
	public function update_current_state($state = null){
		if(!isset($state)){
			$state = $this->get_current_state();
		}
		if ( !$state ){
			$state = new Theme_State();
		}
		$state->lastCheck = time();
		$state->checkedVersion = $this->getInstalledVersion();
		$update = $this->requestUpdate();
		$update = apply_filters("wbf/custom_theme_updater/theme/update_state",$update,$this); //Make possibile to block\alter the update state (eg: with Licenses)
		$state->update = $update;
		if($state->update instanceof Theme_Update){
			$state->update->theme = $this->theme;
		}
		$this->set_current_state($state);
	}

	/**
	 * Get the currently installed version of our theme.
	 *
	 * @return string Version number or FALSE if error.
	 */
	public function getInstalledVersion(){
		$theme = wp_get_theme($this->theme);
		return $theme->get('Version');
	}

	/**
	 * Retrieve update info from the configured endpoint.
	 *
	 * Returns either an instance of ThemeUpdate, or NULL if there is
	 * no newer version available or if there's an error.
	 *
	 * @uses wp_remote_get()
	 *
	 * @param array $queryArgs Additional query arguments to append to the request. Optional.
	 * @return Theme_Update|false
	 */
	public function requestUpdate($queryArgs = []){
		//Setup the remote get request
		$queryArgs['installed_version'] = $this->getInstalledVersion();
		$queryArgs = apply_filters('wbf/custom_theme_updater/endpoint/query_args', $queryArgs, $this);

		$remote_get_options = array(
			'timeout' => 10, //seconds
		);
		$remote_get_options = apply_filters('wbf/custom_theme_updater/endpoint/remote_get_options', $remote_get_options, $this);

		$url = $this->endpoint;
		if(!is_string($url)) return false;

		if(!empty($queryArgs)){
			$url = add_query_arg($queryArgs, $url);
		}

		//Send the request.
		$response = wp_remote_get($url, $remote_get_options);

		//Try to parse the response
		$themeUpdate = null;
		$code = wp_remote_retrieve_response_code($response);
		$body = wp_remote_retrieve_body($response);
		if ( ($code == 200) && !empty($body) ){
			$Update = Theme_Update::build_from_json($body);
			//The update should be newer than the currently installed version.
			if ( $Update instanceof Theme_Update && version_compare($Update->version, $this->getInstalledVersion(), '>') ){
				$themeUpdate = $Update;
			}
		}

		$themeUpdate = apply_filters('wbf/custom_theme_updater/endpoint/result', $themeUpdate, $this, $response);

		if($themeUpdate instanceof Theme_Update){
			return $themeUpdate;
		}
		return false;
	}
}