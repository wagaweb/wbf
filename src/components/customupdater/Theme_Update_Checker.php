<?php

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
		parent::__construct($theme,$metadataUrl,$enableAutomaticChecking);
		if(!$this->automaticCheckDone){
			update_option("wbf_unable_to_update",false);
		}
	}

	/**
	 * Install the hooks required to run periodic update checks and inject update info
	 * into WP data structures.
	 *
	 * @return void
	 */
	public function installHooks() {
		if($this->license && (!$this->license instanceof License || !$this->license->is_valid())){
			$state = $this->requestUpdate();
			if(!is_null($state) && !$this->automaticCheckDone){
				update_option("wbf_unable_to_update",true);
				$this->update_available_notice();
				//Insert our fake update info into the update list maintained by WP.
				add_filter('site_transient_update_themes', array($this,'injectFakeUpdate'));
			}
			$this->update_state_option($state);
			$this->automaticCheckDone = true;
		}else{
			parent::installHooks();
		}
	}

	/**
	 * Inject a fake update, so for themes without a valid license, the plugin update sequence will fail
	 *
	 * @param $updates
	 *
	 * @return mixed
	 */
	public function injectFakeUpdate($updates){
		$state = get_option($this->optionName);

		//Is there an update to insert?
		if ( !empty($state) && isset($state->update) && !empty($state->update) ){
			$response = $state->update->toWpFormat();
			$response['package'] = "";
			$updates->response[$this->theme] = $response;
		}

		return $updates;
	}

	/**
	 * Show the update notice
	 */
	public function update_available_notice(){
		$unable_to_update = get_option("wbf_unable_to_update",false);
		if($unable_to_update && \WBF::is_wbf_admin_page()){
			$message = sprintf(__( 'A new version of %s is available! <a href="%s" title="Enter a valid license">Enter a valid license</a> to get latest updates.', 'wbf' ),$this->theme,"admin.php?page=wbf_licenses");
			$this->notice_manager->add_notice($this->theme."-update",$message,"nag","_flash_");
		}
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