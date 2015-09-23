<?php

namespace WBF\includes;

use WBF\admin\License_Manager;

class Theme_Update_Checker extends \ThemeUpdateChecker{
	var $optionName = '';      //Where to store update info.
	/**
	 * Class constructor.
	 *
	 * @param string $theme Theme slug, e.g. "twentyten".
	 * @param string $metadataUrl The URL of the theme metadata file.
	 * @param boolean $enableAutomaticChecking Enable/disable automatic update checking. If set to FALSE, you'll need to explicitly call checkForUpdates() to, err, check for updates.
	 */
	public function __construct($theme, $metadataUrl, $enableAutomaticChecking = true){
		$this->metadataUrl = $metadataUrl;
		$this->enableAutomaticChecking = $enableAutomaticChecking;
		$this->theme = $theme;
		$this->optionName = 'external_theme_updates-'.$this->theme;

		if(!$this->automaticCheckDone)
			update_option("wbf_unable_to_update",false);

		$maybe_license = License_Manager::theme_has_license($theme);

		if($maybe_license && $maybe_license->is_valid()){
			$this->installHooks();
		}else{
			$state = $this->requestUpdate();
			if(!is_null($state) && !$this->automaticCheckDone){
				update_option("wbf_unable_to_update",true);
				add_action( 'admin_notices', array($this,'update_available_notice') );
				//Insert our fake update info into the update list maintained by WP.
				add_filter('site_transient_update_themes', array($this,'injectFakeUpdate'));
			}
			$this->update_state_option($state);
			$this->automaticCheckDone = true;
		}
	}

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

	public function update_available_notice(){
		$unable_to_update = get_option("wbf_unable_to_update",false);
		if($unable_to_update && \WBF::is_wbf_admin_page()) :
		?>
		<div class="waboot-upgrade-notice update-nag">
			<?php echo sprintf(__( 'A new version of Waboot is available! <a href="%s" title="Enter a valid license">Enter a valid license</a> to get latest updates.', 'wbf' ),"admin.php?page=waboot_license"); ?>
		</div>
		<?php endif;
	}

	public function update_state_option($new_state){
		$state = get_option($this->optionName);
		if ( empty($state) ){
			$state = new \StdClass;
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