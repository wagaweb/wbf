<?php
/*
 * This file is part of WBF Framework: https://github.com/wagaweb/wbf
 *
 * @author WAGA Team <dev@waga.it>
 */

namespace WBF\components\customupdater;

use WBF\components\license\License;
use WBF\components\license\License_Manager;
use WBF\components\notices\Notice_Manager;
use WBF\components\utils\Utilities;

require_once( dirname(__FILE__).'/vendor/theme-update-checker.php');

class Theme_Update_Checker extends \ThemeUpdateChecker{

	/**
	 * @var string
	 */
	var $optionName = ''; //Where to store update info.
	/**
	 * @var License
	 */
	var $license;
	/**
	 * @var Notice_Manager
	 */
	var $notice_manager;
	/**
	 * @var string
	 */
	var $theme;

	/**
	 * Class constructor.
	 *
	 * @param string $theme Theme slug, e.g. "twentyten".
	 * @param string $metadataUrl The URL of the theme metadata file.
	 * @param boolean $enableAutomaticChecking Enable/disable automatic update checking. If set to FALSE, you'll need to explicitly call checkForUpdates() to, err, check for updates.
	 */
	public function __construct($theme, $metadataUrl, $enableAutomaticChecking = true){
		$this->theme = $theme;
		$this->license = License_Manager::theme_has_license($theme);
		//Load Notice Manager if needed
		$wbf_notice_manager = Utilities::get_wbf_notice_manager();
		$this->notice_manager = &$wbf_notice_manager;
		//Then let's get back to parent:
		parent::__construct($theme,$metadataUrl,$enableAutomaticChecking);
	}

	/**
	 * Insert the latest update (if any) into the update list maintained by WP.
	 *
	 * @param \stdClass $updates Update list.
	 *
	 * @hooked 'site_transient_update_themes'
	 *
	 * @return array Modified update list.
	 */
	public function injectUpdate($updates){
		$state = get_option($this->optionName);

		//Is there an update to insert?
		if ( !empty($state) && isset($state->update) && !empty($state->update) ){
			if($state->update instanceof \stdClass){
				$state = $state->update; //todo: this is an ugly hotfix!
			}
			if($this->license && (!$this->license instanceof License || !$this->license->is_valid())){
				//This will make update fails:
				$response = $state->update->toWpFormat();
				$response['package'] = "";
				$updates->response[$this->theme] = $response;
				//Update flag and notices
				$this->set_can_update_flag(false);
				$this->update_available_notice();
				//Update the state option
				$this->update_state_option($state);
			}else{
				$this->set_can_update_flag(true);
				$updates->response[$this->theme] = $state->update->toWpFormat();
			}
		}

		return $updates;
	}

	/**
	 * Show the update notice
	 */
	public function update_available_notice(){
		$message = sprintf(__( 'A new version of %s is available! <a href="%s" title="Enter a valid license">Enter a valid license</a> to get latest updates.', 'wbf' ),$this->theme,"admin.php?page=wbf_licenses");
		$message = apply_filters("wbf/custom_theme_updater/admin_message", $message);
		$this->notice_manager->add_notice($this->theme."-update",$message,"nag","base",__NAMESPACE__."\\Can_Update","theme_".$this->theme);
	}

	/**
	 * Set the update flag for Notice
	 *
	 * @param $can_update
	 */
	public function set_can_update_flag($can_update){
		$opt = get_option("wbf_invalid_licenses",[]);
		if(!$can_update){
			$opt["theme_".$this->theme] = true;
		}else{
			if(array_key_exists("theme_".$this->theme,$opt)){
				unset($opt["theme_".$this->theme]);
			}
		}
		update_option("wbf_invalid_licenses",$opt);
	}

	/**
	 * Update the theme state option
	 *
	 * @param string $new_state
	 */
	public function update_state_option($new_state){
		$state = get_option($this->optionName);
		if ( empty($state) ){
			$state = new \stdClass();
			$state->lastCheck = 0;
			$state->checkedVersion = '';
			$state->update = null;
		}
		$state->lastCheck = time();
		$state->checkedVersion = $this->getInstalledVersion();
		$state->update = $new_state;
		update_option($this->optionName, $state);
	}
}